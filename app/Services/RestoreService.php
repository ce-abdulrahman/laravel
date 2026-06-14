<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBackup;
use App\Models\BackupRestoreLog;
use App\Models\TasbihSession;
use App\Models\TasbihSessionAggregate;
use App\Models\UserTasbihStreak;
use App\Models\UserDailyGoal;
use App\Models\UserGoalProgress;
use App\Models\UserGoalProgressEvent;
use App\Models\UserAchievement;
use App\Models\AchievementEvent;
use App\Models\UserReminder;
use App\Models\LeaderboardScore;
use App\Models\UserLeaderboardSetting;
use App\Models\Bookmark;
use App\Models\Favorite;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RestoreService
{
    /**
     * Decrypt helper.
     */
    public function decrypt(string $payload, string $password): ?string
    {
        try {
            $data = base64_decode($payload);
            if (strlen($data) < 32) return null;

            $salt = substr($data, 0, 16);
            $iv = substr($data, 16, 16);
            $ciphertext = substr($data, 32);

            $key = hash_pbkdf2('sha256', $password, $salt, 10000, 32, true);
            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            return $decrypted !== false ? $decrypted : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Read and extract JSON payload from backup ZIP.
     */
    public function extractBackupJson(string $filePath, ?string $password = null): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Cannot open ZIP archive.");
        }

        $jsonStr = $zip->getFromName('backup.json');
        $zip->close();

        if ($jsonStr === false) {
            throw new \Exception("Backup file is missing backup.json.");
        }

        // Decrypt if necessary
        if ($password !== null) {
            $decrypted = $this->decrypt($jsonStr, $password);
            if ($decrypted === null) {
                throw new \Exception("Decryption failed. Invalid password or corrupted backup.");
            }
            $jsonStr = $decrypted;
        }

        $payload = json_decode($jsonStr, true);
        if (!is_array($payload)) {
            throw new \Exception("Corrupted JSON structure inside backup.");
        }

        return $payload;
    }

    /**
     * Generate dry-run preview report of the restore.
     */
    public function generatePreviewReport(int $userId, string $filePath, ?string $password = null): array
    {
        $payload = $this->extractBackupJson($filePath, $password);
        $user = User::findOrFail($userId);

        $counts = [
            'sessions' => count($payload['sessions']['sessions'] ?? []),
            'goals' => count($payload['goals']['goals'] ?? []),
            'achievements' => count($payload['achievements']['achievements'] ?? []),
            'reminders' => count($payload['reminders']['settings'] ?? []),
            'bookmarks' => count($payload['bookmarks'] ?? []),
            'favorites' => count($payload['favorites'] ?? []),
        ];

        // Conflict check calculations
        $conflicts = [
            'sessions' => 0,
            'goals' => 0,
        ];

        if (!empty($payload['sessions']['sessions'])) {
            $importedStartTimes = collect($payload['sessions']['sessions'])->pluck('start_time')->toArray();
            $conflicts['sessions'] = TasbihSession::where('user_id', $userId)
                ->whereIn('start_time', $importedStartTimes)
                ->count();
        }

        if (!empty($payload['goals']['goals'])) {
            $importedDates = collect($payload['goals']['goals'])->pluck('goal_date')->toArray();
            $conflicts['goals'] = UserDailyGoal::where('user_id', $userId)
                ->whereIn('goal_date', $importedDates)
                ->count();
        }

        return [
            'backup_version' => $payload['backup_version'] ?? '1.0',
            'app_version' => $payload['app_version'] ?? 'Unknown',
            'device_type' => $payload['device_type'] ?? 'Unknown',
            'platform' => $payload['platform'] ?? 'Unknown',
            'created_at' => $payload['created_at'] ?? null,
            'user_uuid' => $payload['user_uuid'] ?? null,
            'counts' => $counts,
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Execute restore process.
     */
    public function restoreBackup(int $userId, string $filePath, string $conflictResolution = 'replace', array $modules = [], ?string $password = null, ?int $backupId = null): BackupRestoreLog
    {
        $lockKey = "backup_lock_{$userId}";
        if (Cache::has($lockKey)) {
            throw new \Exception("An operation is already in progress for this user.");
        }
        Cache::put($lockKey, true, 300);

        $log = BackupRestoreLog::create([
            'user_id' => $userId,
            'backup_id' => $backupId,
            'restore_type' => $backupId ? 'cloud' : 'local_file',
            'status' => 'pending',
            'started_at' => Carbon::now(),
        ]);

        try {
            $payload = $this->extractBackupJson($filePath, $password);

            // Set default modules if empty (restore all)
            if (empty($modules)) {
                $modules = ['user', 'tasbih', 'goals', 'achievements', 'sessions', 'reminders', 'leaderboard', 'bookmarks', 'favorites'];
            }

            DB::transaction(function () use ($userId, $payload, $conflictResolution, $modules) {
                // User Profile/Stats restoration
                if (in_array('user', $modules) && !empty($payload['user'])) {
                    $uData = $payload['user'];
                    $user = User::findOrFail($userId);
                    if ($conflictResolution === 'replace') {
                        $user->update([
                            'preferred_locale' => $uData['preferred_locale'] ?? $user->preferred_locale,
                            'points_total' => $uData['points_total'] ?? 0,
                            'streak_days' => $uData['streak_days'] ?? 0,
                            'longest_streak' => $uData['longest_streak'] ?? 0,
                            'last_read_date' => $uData['last_read_date'] ?? null,
                        ]);
                    } else {
                        // Merge: keep higher point/streak counts
                        $user->update([
                            'preferred_locale' => $user->preferred_locale ?? $uData['preferred_locale'],
                            'points_total' => max($user->points_total, $uData['points_total'] ?? 0),
                            'streak_days' => max($user->streak_days, $uData['streak_days'] ?? 0),
                            'longest_streak' => max($user->longest_streak, $uData['longest_streak'] ?? 0),
                        ]);
                    }
                }

                // 1. Tasbih Streaks
                if (in_array('tasbih', $modules) && !empty($payload['tasbih']['streaks'])) {
                    $sData = $payload['tasbih']['streaks'];
                    if ($conflictResolution === 'replace') {
                        UserTasbihStreak::updateOrCreate(
                            ['user_id' => $userId],
                            [
                                'current_streak' => $sData['current_streak'],
                                'longest_streak' => $sData['longest_streak'],
                                'last_activity_date' => $sData['last_activity_date'],
                            ]
                        );
                    } else {
                        $current = UserTasbihStreak::where('user_id', $userId)->first();
                        UserTasbihStreak::updateOrCreate(
                            ['user_id' => $userId],
                            [
                                'current_streak' => $current ? max($current->current_streak, $sData['current_streak']) : $sData['current_streak'],
                                'longest_streak' => $current ? max($current->longest_streak, $sData['longest_streak']) : $sData['longest_streak'],
                                'last_activity_date' => $sData['last_activity_date'] ?: ($current ? $current->last_activity_date : null),
                            ]
                        );
                    }
                }

                // 2. Daily Goals & Goal Progress
                if (in_array('goals', $modules) && !empty($payload['goals'])) {
                    $gData = $payload['goals'];
                    
                    if ($conflictResolution === 'replace') {
                        UserDailyGoal::where('user_id', $userId)->delete();
                        UserGoalProgress::where('user_id', $userId)->delete();
                        UserGoalProgressEvent::where('user_id', $userId)->delete();
                    }

                    // Restore Daily Goals
                    foreach ($gData['goals'] ?? [] as $goal) {
                        UserDailyGoal::updateOrCreate(
                            ['user_id' => $userId, 'goal_date' => $goal['goal_date']],
                            [
                                'goal_value' => $goal['goal_value'],
                                'today_progress' => $goal['today_progress'],
                                'is_completed' => $goal['is_completed'],
                            ]
                        );
                    }

                    // Restore Goal Progress
                    foreach ($gData['progress'] ?? [] as $prog) {
                        UserGoalProgress::updateOrCreate(
                            ['user_id' => $userId, 'goal_id' => $prog['goal_id']],
                            [
                                'current_value' => $prog['current_value'],
                                'is_completed' => $prog['is_completed'],
                                'last_updated_at' => $prog['last_updated_at'],
                            ]
                        );
                    }

                    // Restore Goal Progress Events
                    foreach ($gData['progress_events'] ?? [] as $ev) {
                        $exists = UserGoalProgressEvent::where('event_uuid', $ev['event_uuid'])->exists();
                        if (!$exists) {
                            UserGoalProgressEvent::create([
                                'user_id' => $userId,
                                'goal_id' => $ev['goal_id'],
                                'event_uuid' => $ev['event_uuid'],
                                'value_increment' => $ev['value_increment'],
                                'occurred_at' => $ev['occurred_at'],
                            ]);
                        }
                    }
                }

                // 3. Achievements
                if (in_array('achievements', $modules) && !empty($payload['achievements'])) {
                    $aData = $payload['achievements'];

                    if ($conflictResolution === 'replace') {
                        UserAchievement::where('user_id', $userId)->delete();
                        AchievementEvent::where('user_id', $userId)->delete();
                    }

                    // Restore Achievements
                    foreach ($aData['achievements'] ?? [] as $ach) {
                        UserAchievement::updateOrCreate(
                            ['user_id' => $userId, 'achievement_id' => $ach['achievement_id']],
                            [
                                'current_progress' => $ach['current_progress'],
                                'is_unlocked' => $ach['is_unlocked'],
                                'unlocked_at' => $ach['unlocked_at'],
                            ]
                        );
                    }

                    // Restore Events
                    foreach ($aData['events'] ?? [] as $ev) {
                        $exists = AchievementEvent::where('event_uuid', $ev['event_uuid'] ?? '')->exists();
                        if (!$exists) {
                            AchievementEvent::create([
                                'user_id' => $userId,
                                'achievement_id' => $ev['achievement_id'],
                                'event_uuid' => $ev['event_uuid'] ?? uniqid(),
                                'occurred_at' => $ev['occurred_at'],
                            ]);
                        }
                    }
                }

                // 4. Tasbih Sessions
                if (in_array('sessions', $modules) && !empty($payload['sessions'])) {
                    $sData = $payload['sessions'];

                    if ($conflictResolution === 'replace') {
                        TasbihSession::where('user_id', $userId)->delete();
                        TasbihSessionAggregate::where('user_id', $userId)->delete();
                    }

                    // Restore Sessions
                    foreach ($sData['sessions'] ?? [] as $sess) {
                        // Check duplication by start_time
                        $exists = TasbihSession::where('user_id', $userId)
                            ->where('start_time', $sess['start_time'])
                            ->exists();

                        if (!$exists) {
                            TasbihSession::create([
                                'user_id' => $userId,
                                'dhikr_id' => $sess['dhikr_id'],
                                'custom_dhikr_name' => $sess['custom_dhikr_name'],
                                'start_time' => $sess['start_time'],
                                'end_time' => $sess['end_time'],
                                'duration_seconds' => $sess['duration_seconds'],
                                'total_count' => $sess['total_count'],
                                'avg_per_minute' => $sess['avg_per_minute'],
                                'session_date' => $sess['session_date'],
                                'status' => $sess['status'],
                            ]);
                        }
                    }

                    // Restore Aggregates
                    foreach ($sData['aggregates'] ?? [] as $agg) {
                        TasbihSessionAggregate::updateOrCreate(
                            ['user_id' => $userId, 'activity_date' => $agg['activity_date']],
                            [
                                'total_sessions' => $agg['total_sessions'],
                                'total_dhikr_count' => $agg['total_dhikr_count'],
                                'avg_duration_seconds' => $agg['avg_duration_seconds'],
                            ]
                        );
                    }
                }

                // 5. Reminder Settings
                if (in_array('reminders', $modules) && !empty($payload['reminders']['settings'])) {
                    if ($conflictResolution === 'replace') {
                        UserReminder::where('user_id', $userId)->delete();
                    }

                    foreach ($payload['reminders']['settings'] as $rem) {
                        UserReminder::updateOrCreate(
                            ['user_id' => $userId, 'reminder_type' => $rem['reminder_type']],
                            [
                                'frequency' => $rem['frequency'],
                                'reminder_time' => $rem['reminder_time'],
                                'is_enabled' => $rem['is_enabled'],
                                'schedule' => $rem['schedule'],
                            ]
                        );
                    }
                }

                // 6. Leaderboard Settings & Scores
                if (in_array('leaderboard', $modules) && !empty($payload['leaderboard'])) {
                    $lData = $payload['leaderboard'];

                    if ($conflictResolution === 'replace') {
                        LeaderboardScore::where('user_id', $userId)->delete();
                    }

                    if (!empty($lData['settings'])) {
                        UserLeaderboardSetting::updateOrCreate(
                            ['user_id' => $userId],
                            [
                                'is_anonymous' => $lData['settings']['is_anonymous'],
                                'display_name' => $lData['settings']['display_name'],
                            ]
                        );
                    }

                    foreach ($lData['scores'] ?? [] as $score) {
                        LeaderboardScore::updateOrCreate(
                            ['user_id' => $userId, 'score_type' => $score['score_type'], 'calculated_at' => $score['calculated_at']],
                            ['score' => $score['score']]
                        );
                    }
                }

                // 7. Bookmarks
                if (in_array('bookmarks', $modules) && !empty($payload['bookmarks'])) {
                    if ($conflictResolution === 'replace') {
                        Bookmark::where('user_id', $userId)->delete();
                    }

                    foreach ($payload['bookmarks'] as $bm) {
                        Bookmark::updateOrCreate(
                            ['user_id' => $userId, 'surah_id' => $bm['surah_id'], 'ayah_id' => $bm['ayah_id']],
                            ['note' => $bm['note'] ?? null]
                        );
                    }
                }

                // 8. Favorites
                if (in_array('favorites', $modules) && !empty($payload['favorites'])) {
                    if ($conflictResolution === 'replace') {
                        Favorite::where('user_id', $userId)->delete();
                    }

                    foreach ($payload['favorites'] as $fav) {
                        Favorite::firstOrCreate([
                            'user_id' => $userId,
                            'surah_id' => $fav['surah_id'],
                            'ayah_id' => $fav['ayah_id'],
                        ]);
                    }
                }
            });

            // Mark log success
            $log->update([
                'status' => 'success',
                'completed_at' => Carbon::now(),
            ]);

            Cache::forget($lockKey);
            return $log;

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'completed_at' => Carbon::now(),
            ]);
            Cache::forget($lockKey);
            throw $e;
        }
    }
}
