<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBackup;
use App\Models\SettingEntry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class BackupService
{
    /**
     * Get backup setting with fallback.
     */
    public function getSetting(string $key, $default = null)
    {
        $entry = SettingEntry::where('key', 'backup_' . $key)->first();
        return $entry ? $entry->value : $default;
    }

    /**
     * Set backup setting.
     */
    public function setSetting(string $key, $value): void
    {
        SettingEntry::updateOrCreate(
            ['key' => 'backup_' . $key],
            ['value' => $value]
        );
    }

    /**
     * Generate backup for user.
     */
    public function generateBackup(int $userId, string $backupType = 'manual', array $options = []): UserBackup
    {
        $user = User::findOrFail($userId);
        $lockKey = "backup_lock_{$userId}";

        // Enforce lock check
        if (Cache::has($lockKey)) {
            throw new \Exception("An operation is already in progress for this user.");
        }

        // Set lock for 5 minutes
        Cache::put($lockKey, true, 300);

        // 1. Create UserBackup model in pending state
        $backup = UserBackup::create([
            'user_id' => $userId,
            'backup_type' => $backupType,
            'storage_type' => $this->getSetting('storage_provider', 'local'),
            'backup_version' => '1.0',
            'file_name' => 'temp_' . uniqid() . '.zip',
            'file_size' => 0,
            'checksum_sha256' => '',
            'is_encrypted' => !empty($options['password']),
            'status' => 'pending',
            'device_type' => $options['device_type'] ?? null,
            'platform' => $options['platform'] ?? null,
            'app_version' => $options['app_version'] ?? null,
            'is_processing' => true,
            'expires_at' => $backupType === 'auto' 
                ? Carbon::now()->addDays((int) $this->getSetting('retention_days', 30)) 
                : null,
        ]);

        try {
            // 2. Serialize user data
            $payload = $this->serializeUserData($user, $options);

            $jsonContent = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // 3. Encrypt if password is set
            if (!empty($options['password'])) {
                $jsonContent = $this->encrypt($jsonContent, $options['password']);
            }

            // 4. Create ZIP archive
            $tempZipFile = tempnam(sys_get_temp_dir(), 'quran_backup_') . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($tempZipFile, \ZipArchive::CREATE) !== true) {
                throw new \Exception("Cannot create temporary zip file.");
            }
            $zip->addFromString('backup.json', $jsonContent);
            $zip->close();

            // 5. Hash SHA-256
            $checksum = hash_file('sha256', $tempZipFile);
            $fileSize = filesize($tempZipFile);

            // 6. Save to configured storage disk
            $disk = $this->getSetting('storage_provider', 'local');
            $fileName = "backups/user_{$userId}/backup_" . date('Ymd_His') . '_' . uniqid() . '.zip';

            Storage::disk($disk)->put($fileName, fopen($tempZipFile, 'r'));
            unlink($tempZipFile);

            // 7. Update UserBackup with file info and success status
            $backup->update([
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'checksum_sha256' => $checksum,
                'status' => 'success',
                'is_processing' => false,
            ]);

            // 8. Prune oldest auto backups if they exceed the limit
            if ($backupType === 'auto') {
                $this->pruneOldAutoBackups($userId);
            }

            Cache::forget($lockKey);
            return $backup;

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'is_processing' => false,
            ]);
            Cache::forget($lockKey);
            throw $e;
        }
    }

    /**
     * Upload an existing backup file to storage and register it.
     */
    public function uploadBackup(int $userId, $file, array $options = []): UserBackup
    {
        $lockKey = "backup_lock_{$userId}";
        if (Cache::has($lockKey)) {
            throw new \Exception("An operation is already in progress for this user.");
        }

        Cache::put($lockKey, true, 300);

        try {
            $tempPath = $file->getRealPath();
            $checksum = hash_file('sha256', $tempPath);
            $fileSize = $file->getSize();

            // Validate ZIP has backup.json
            $zip = new \ZipArchive();
            if ($zip->open($tempPath) !== true) {
                throw new \Exception("Invalid zip archive file.");
            }
            if ($zip->locateName('backup.json') === false) {
                $zip->close();
                throw new \Exception("Backup archive is missing backup.json.");
            }
            $zip->close();

            $disk = $this->getSetting('storage_provider', 'local');
            $fileName = "backups/user_{$userId}/backup_uploaded_" . date('Ymd_His') . '_' . uniqid() . '.zip';

            Storage::disk($disk)->putFileAs("backups/user_{$userId}", $file, basename($fileName));

            $backup = UserBackup::create([
                'user_id' => $userId,
                'backup_type' => 'manual',
                'storage_type' => $disk,
                'backup_version' => $options['backup_version'] ?? '1.0',
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'checksum_sha256' => $checksum,
                'is_encrypted' => $options['is_encrypted'] ?? false,
                'status' => 'success',
                'device_type' => $options['device_type'] ?? null,
                'platform' => $options['platform'] ?? null,
                'app_version' => $options['app_version'] ?? null,
                'is_processing' => false,
                'expires_at' => null,
            ]);

            Cache::forget($lockKey);
            return $backup;

        } catch (\Exception $e) {
            Cache::forget($lockKey);
            throw $e;
        }
    }

    /**
     * Delete backup from database and disk.
     */
    public function deleteBackup(int $backupId): void
    {
        $backup = UserBackup::findOrFail($backupId);

        if (Storage::disk($backup->storage_type)->exists($backup->file_name)) {
            Storage::disk($backup->storage_type)->delete($backup->file_name);
        }

        $backup->delete();
    }

    /**
     * Prune auto backups exceeding count limits.
     */
    private function pruneOldAutoBackups(int $userId): void
    {
        $maxCount = (int) $this->getSetting('max_count', 10);

        $autoBackups = UserBackup::where('user_id', $userId)
            ->where('backup_type', 'auto')
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($autoBackups->count() > $maxCount) {
            $toDelete = $autoBackups->slice($maxCount);
            foreach ($toDelete as $oldBackup) {
                $this->deleteBackup($oldBackup->id);
            }
        }
    }

    /**
     * Run daily pruning scheduler for expired backups.
     */
    public function pruneExpiredBackups(): int
    {
        $expired = UserBackup::where('status', 'success')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expired as $oldBackup) {
            $this->deleteBackup($oldBackup->id);
            $count++;
        }
        return $count;
    }

    /**
     * Serialize all user data to export.
     */
    private function serializeUserData(User $user, array $options = []): array
    {
        $userUuid = Uuid::uuid5(Uuid::NAMESPACE_DNS, $user->email)->toString();

        return [
            'backup_version' => '1.0',
            'app_version' => $options['app_version'] ?? '1.0.0',
            'device_type' => $options['device_type'] ?? 'Unknown',
            'platform' => $options['platform'] ?? 'Unknown',
            'created_at' => Carbon::now()->toIso8601String(),
            'user_uuid' => $userUuid,

            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'preferred_locale' => $user->preferred_locale,
                'points_total' => $user->points_total,
                'streak_days' => $user->streak_days,
                'longest_streak' => $user->longest_streak,
                'last_read_date' => $user->last_read_date ? $user->last_read_date->toDateString() : null,
            ],

            'tasbih' => [
                'streaks' => $user->tasbihStreak ? [
                    'current_streak' => $user->tasbihStreak->current_streak,
                    'longest_streak' => $user->tasbihStreak->longest_streak,
                    'last_activity_date' => $user->tasbihStreak->last_activity_date,
                ] : null,
            ],

            'goals' => [
                'goals' => $user->dailyGoals->map(function ($goal) {
                    return [
                        'goal_date' => $goal->goal_date,
                        'goal_value' => $goal->goal_value,
                        'today_progress' => $goal->today_progress,
                        'is_completed' => $goal->is_completed,
                    ];
                })->toArray(),
                'progress' => $user->goalProgress->map(function ($prog) {
                    return [
                        'goal_id' => $prog->goal_id,
                        'current_value' => $prog->current_value,
                        'is_completed' => $prog->is_completed,
                        'last_updated_at' => $prog->last_updated_at,
                    ];
                })->toArray(),
                'progress_events' => $user->goalProgressEvents->map(function ($ev) {
                    return [
                        'goal_id' => $ev->daily_goal_id ?? $ev->goal_id,
                        'event_uuid' => $ev->event_uuid,
                        'value_increment' => $ev->value_increment,
                        'occurred_at' => $ev->occurred_at,
                    ];
                })->toArray(),
            ],

            'achievements' => [
                'achievements' => $user->userAchievements->map(function ($ach) {
                    return [
                        'achievement_id' => $ach->achievement_id,
                        'current_progress' => $ach->current_progress,
                        'is_unlocked' => $ach->is_unlocked,
                        'unlocked_at' => $ach->unlocked_at,
                    ];
                })->toArray(),
                'events' => $user->achievementEvents->map(function ($ev) {
                    return [
                        'achievement_id' => $ev->achievement_id,
                        'event_uuid' => $ev->event_uuid ?? uniqid(),
                        'occurred_at' => $ev->occurred_at,
                    ];
                })->toArray(),
            ],

            'sessions' => [
                'sessions' => $user->tasbihSessions->map(function ($sess) {
                    return [
                        'dhikr_id' => $sess->dhikr_id,
                        'custom_dhikr_name' => $sess->custom_dhikr_name,
                        'start_time' => $sess->start_time,
                        'end_time' => $sess->end_time,
                        'duration_seconds' => $sess->duration_seconds,
                        'total_count' => $sess->total_count,
                        'avg_per_minute' => $sess->avg_per_minute,
                        'session_date' => $sess->session_date,
                        'status' => $sess->status,
                    ];
                })->toArray(),
                'aggregates' => $user->tasbihSessionAggregates->map(function ($agg) {
                    return [
                        'total_sessions' => $agg->total_sessions,
                        'total_dhikr_count' => $agg->total_dhikr_count,
                        'avg_duration_seconds' => $agg->avg_duration_seconds,
                        'activity_date' => $agg->activity_date,
                    ];
                })->toArray(),
            ],

            'reminders' => [
                'settings' => $user->reminders->map(function ($rem) {
                    return [
                        'reminder_type' => $rem->reminder_type,
                        'frequency' => $rem->frequency,
                        'reminder_time' => $rem->reminder_time,
                        'is_enabled' => $rem->is_enabled,
                        'schedule' => $rem->schedule,
                    ];
                })->toArray(),
            ],

            'leaderboard' => [
                'settings' => $user->leaderboardSettings ? [
                    'is_anonymous' => $user->leaderboardSettings->is_anonymous,
                    'display_name' => $user->leaderboardSettings->display_name,
                ] : null,
                'scores' => $user->leaderboardScores->map(function ($score) {
                    return [
                        'score_type' => $score->score_type,
                        'score' => $score->score,
                        'calculated_at' => $score->calculated_at,
                    ];
                })->toArray(),
            ],

            'bookmarks' => $user->bookmarks->map(function ($bm) {
                return [
                    'surah_id' => $bm->surah_id,
                    'ayah_id' => $bm->ayah_id,
                    'note' => $bm->note,
                ];
            })->toArray(),

            'favorites' => $user->favorites->map(function ($fav) {
                return [
                    'surah_id' => $fav->surah_id,
                    'ayah_id' => $fav->ayah_id,
                ];
            })->toArray(),
        ];
    }

    /**
     * Encrypt helper.
     */
    public function encrypt(string $data, string $password): string
    {
        $salt = openssl_random_pseudo_bytes(16);
        $key = hash_pbkdf2('sha256', $password, $salt, 10000, 32, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $ciphertext = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($salt . $iv . $ciphertext);
    }
}
