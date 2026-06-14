<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\AchievementEvent;
use App\Models\User;
use App\Models\UserAchievement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * AchievementEngine — Event-driven achievement evaluation service.
 *
 * Usage:
 *   $engine = app(AchievementEngine::class);
 *   $unlocked = $engine->evaluate($user, [
 *       'total_dhikr_count'  => 1500,
 *       'current_streak'     => 7,
 *       'goals_completed'    => 10,
 *       'session_dhikr_count'=> 250,
 *   ]);
 *
 * Returns array of newly unlocked Achievement instances.
 */
class AchievementEngine
{
    /**
     * Evaluate all relevant condition types from the unified payload.
     * Returns newly unlocked UserAchievement records (with achievement loaded).
     */
    public function evaluate(User $user, array $payload): array
    {
        $newlyUnlocked = [];

        // Map payload keys → condition types
        $conditionMap = [
            'total_dhikr_count'   => Achievement::CONDITION_TOTAL_DHIKR,
            'current_streak'      => Achievement::CONDITION_CURRENT_STREAK,
            'longest_streak'      => Achievement::CONDITION_LONGEST_STREAK,
            'goals_completed'     => Achievement::CONDITION_GOALS_COMPLETED,
            'session_dhikr_count' => Achievement::CONDITION_SESSION_DHIKR,
            'consecutive_days'    => Achievement::CONDITION_CONSECUTIVE_DAYS,
            'fingerprint_total_counts'   => Achievement::CONDITION_FINGERPRINT_TOTAL_COUNTS,
            'fingerprint_total_sessions' => Achievement::CONDITION_FINGERPRINT_TOTAL_SESSIONS,
            'fingerprint_blind_sessions' => Achievement::CONDITION_FINGERPRINT_BLIND_SESSIONS,
            'fingerprint_focus_sessions' => Achievement::CONDITION_FINGERPRINT_FOCUS_SESSIONS,
        ];

        foreach ($conditionMap as $payloadKey => $conditionType) {
            if (!isset($payload[$payloadKey])) {
                continue;
            }

            $value = (int) $payload[$payloadKey];
            if ($value <= 0) {
                continue;
            }

            $unlocked = $this->evaluateConditionType($user, $conditionType, $value);
            $newlyUnlocked = array_merge($newlyUnlocked, $unlocked);
        }

        // Special event evaluation (time-based hidden achievements)
        if (isset($payload['total_dhikr_count']) && $payload['total_dhikr_count'] > 0) {
            $specialUnlocked = $this->evaluateSpecialEvents($user);
            $newlyUnlocked = array_merge($newlyUnlocked, $specialUnlocked);
        }

        return $newlyUnlocked;
    }

    /**
     * Evaluate a single condition type against all matching achievements.
     */
    private function evaluateConditionType(User $user, string $conditionType, int $value): array
    {
        $achievements = $this->getAchievementsForConditionType($conditionType);
        if (empty($achievements)) {
            return [];
        }

        // Load existing user achievement rows in one query (keyed by achievement_id)
        $achievementIds = array_column($achievements, 'id');
        $existingRows = UserAchievement::where('user_id', $user->id)
            ->whereIn('achievement_id', $achievementIds)
            ->get()
            ->keyBy('achievement_id');

        $newlyUnlocked = [];

        DB::transaction(function () use ($user, $achievements, $value, $existingRows, &$newlyUnlocked) {
            foreach ($achievements as $achievementData) {
                $achievementId    = $achievementData['id'];
                $conditionValue   = (int) $achievementData['condition_value'];
                $version          = (int) ($achievementData['version'] ?? 1);
                $existingRow      = $existingRows->get($achievementId);

                // Skip if already completed
                if ($existingRow && $existingRow->is_completed) {
                    continue;
                }

                // Determine new progress value (never go backwards)
                $newProgress = max($existingRow?->progress_value ?? 0, $value);

                if ($existingRow) {
                    // Update progress
                    $existingRow->progress_value = $newProgress;

                    if ($newProgress >= $conditionValue) {
                        $existingRow->is_completed    = true;
                        $existingRow->completed_at    = Carbon::now('UTC');
                        $existingRow->unlocked_version = $version;
                        $existingRow->save();

                        $this->logEvent($user->id, $achievementId, 'unlocked', $newProgress);
                        $newlyUnlocked[] = $existingRow->load('achievement.translations', 'achievement.category');
                    } else {
                        $existingRow->save();
                        $this->logEvent($user->id, $achievementId, 'progress_updated', $newProgress);
                    }
                } else {
                    // Create new progress row
                    $isCompleted = $newProgress >= $conditionValue;

                    $userAchievement = UserAchievement::create([
                        'user_id'          => $user->id,
                        'achievement_id'   => $achievementId,
                        'progress_value'   => $newProgress,
                        'is_completed'     => $isCompleted,
                        'completed_at'     => $isCompleted ? Carbon::now('UTC') : null,
                        'unlocked_version' => $isCompleted ? $version : 1,
                    ]);

                    $eventType = $isCompleted ? 'unlocked' : 'progress_updated';
                    $this->logEvent($user->id, $achievementId, $eventType, $newProgress);

                    if ($isCompleted) {
                        $newlyUnlocked[] = $userAchievement->load('achievement.translations', 'achievement.category');
                    }
                }
            }
        });

        return $newlyUnlocked;
    }

    /**
     * Evaluate SPECIAL_EVENT achievements (time-of-day based hidden ones).
     */
    private function evaluateSpecialEvents(User $user): array
    {
        $achievements = $this->getAchievementsForConditionType(Achievement::CONDITION_SPECIAL_EVENT);
        if (empty($achievements)) {
            return [];
        }

        $now = Carbon::now('Asia/Baghdad');
        $hour = $now->hour;
        $newlyUnlocked = [];

        $achievementIds = array_column($achievements, 'id');
        $existingRows = UserAchievement::where('user_id', $user->id)
            ->whereIn('achievement_id', $achievementIds)
            ->completed()
            ->pluck('achievement_id')
            ->flip();

        DB::transaction(function () use ($user, $achievements, $hour, $existingRows, &$newlyUnlocked) {
            foreach ($achievements as $achievementData) {
                $achievementId = $achievementData['id'];

                // Skip already unlocked
                if ($existingRows->has($achievementId)) {
                    continue;
                }

                $meta = $achievementData['condition_meta'] ?? [];
                $qualifies = false;

                // Example: midnight_worshipper = hour between 0-3
                if (!empty($meta['hour_start']) && !empty($meta['hour_end'])) {
                    $qualifies = $hour >= $meta['hour_start'] && $hour <= $meta['hour_end'];
                }

                if (!$qualifies) {
                    continue;
                }

                $userAchievement = UserAchievement::updateOrCreate(
                    ['user_id' => $user->id, 'achievement_id' => $achievementId],
                    [
                        'progress_value'   => 1,
                        'is_completed'     => true,
                        'completed_at'     => Carbon::now('UTC'),
                        'unlocked_version' => (int) ($achievementData['version'] ?? 1),
                    ]
                );

                $this->logEvent($user->id, $achievementId, 'unlocked', 1);
                $newlyUnlocked[] = $userAchievement->load('achievement.translations', 'achievement.category');
            }
        });

        return $newlyUnlocked;
    }

    /**
     * Get all active achievements for a given condition type from the 15-minute cache.
     */
    private function getAchievementsForConditionType(string $conditionType): array
    {
        $cacheKey = "achievements:condition:{$conditionType}";

        return Cache::remember($cacheKey, 900, function () use ($conditionType) {
            return Achievement::active()
                ->forCondition($conditionType)
                ->with('translations')
                ->ordered()
                ->get()
                ->toArray();
        });
    }

    /**
     * Log an achievement event to the audit table.
     */
    private function logEvent(int $userId, int $achievementId, string $eventType, int $value): void
    {
        AchievementEvent::create([
            'user_id'        => $userId,
            'achievement_id' => $achievementId,
            'event_type'     => $eventType,
            'event_value'    => $value,
            'created_at'     => Carbon::now('UTC'),
        ]);
    }

    /**
     * Get all achievements for a user with their progress (for API listing).
     */
    public function getUserAchievements(User $user, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        // All active achievements
        $allAchievements = Achievement::active()
            ->with(['translations', 'category.translations'])
            ->ordered()
            ->get();

        // User's progress rows keyed by achievement_id
        $userRows = UserAchievement::where('user_id', $user->id)
            ->get()
            ->keyBy('achievement_id');

        return $allAchievements->map(function (Achievement $achievement) use ($userRows, $locale) {
            $userRow = $userRows->get($achievement->id);
            $isCompleted = $userRow?->is_completed ?? false;
            $isHidden = $achievement->is_hidden && !$isCompleted;

            return [
                'id'              => $achievement->id,
                'key'             => $achievement->key,
                // Hide name/description/icon for undiscovered hidden achievements
                'name'            => $isHidden ? '???' : ($achievement->getTranslation('name', $locale) ?? $achievement->key),
                'description'     => $isHidden ? __('achievements.hidden_description') : $achievement->getTranslation('description', $locale),
                'icon'            => $isHidden ? '🔒' : $achievement->icon,
                'badge_image'     => $isHidden ? null : $achievement->badge_image,
                'category'        => $achievement->category ? [
                    'id'   => $achievement->category->id,
                    'name' => $achievement->category->getTranslation('name', $locale),
                    'icon' => $achievement->category->icon,
                ] : null,
                'condition_type'  => $achievement->condition_type,
                'condition_value' => $achievement->condition_value,
                'reward_type'     => $achievement->reward_type,
                'reward_points'   => $achievement->reward_points,
                'reward_value'    => $achievement->reward_value,
                'version'         => $achievement->version,
                'is_hidden'       => $achievement->is_hidden,
                // User progress
                'is_completed'    => $isCompleted,
                'progress_value'  => $userRow?->progress_value ?? 0,
                'completed_at'    => $userRow?->completed_at?->toIso8601String(),
                'unlocked_version'=> $userRow?->unlocked_version,
            ];
        })->toArray();
    }

    /**
     * Get newly unlocked achievements for the user since a given timestamp.
     * Used by the mobile app to show unlock modals.
     */
    public function getNewlyUnlocked(User $user, ?string $since = null): array
    {
        $query = UserAchievement::where('user_id', $user->id)
            ->where('is_completed', true)
            ->with(['achievement.translations', 'achievement.category.translations']);

        if ($since) {
            $query->where('completed_at', '>=', Carbon::parse($since, 'UTC'));
        } else {
            // Default: last 5 minutes
            $query->where('completed_at', '>=', Carbon::now('UTC')->subMinutes(5));
        }

        return $query->orderBy('completed_at', 'desc')->get()->map(function (UserAchievement $ua) {
            return [
                'id'            => $ua->id,
                'achievement_id'=> $ua->achievement_id,
                'name'          => $ua->achievement->getTranslation('name'),
                'description'   => $ua->achievement->getTranslation('description'),
                'icon'          => $ua->achievement->icon,
                'badge_image'   => $ua->achievement->badge_image,
                'reward_points' => $ua->achievement->reward_points,
                'reward_type'   => $ua->achievement->reward_type,
                'completed_at'  => $ua->completed_at?->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Bust all condition-type caches (call from admin on achievement save/delete).
     */
    public function bustCache(): void
    {
        $types = [
            Achievement::CONDITION_TOTAL_DHIKR,
            Achievement::CONDITION_CURRENT_STREAK,
            Achievement::CONDITION_LONGEST_STREAK,
            Achievement::CONDITION_GOALS_COMPLETED,
            Achievement::CONDITION_SESSION_DHIKR,
            Achievement::CONDITION_CONSECUTIVE_DAYS,
            Achievement::CONDITION_SPECIAL_EVENT,
            Achievement::CONDITION_CUSTOM_RULE,
            Achievement::CONDITION_FINGERPRINT_TOTAL_COUNTS,
            Achievement::CONDITION_FINGERPRINT_TOTAL_SESSIONS,
            Achievement::CONDITION_FINGERPRINT_BLIND_SESSIONS,
            Achievement::CONDITION_FINGERPRINT_FOCUS_SESSIONS,
        ];
        foreach ($types as $type) {
            Cache::forget("achievements:condition:{$type}");
        }
    }
}
