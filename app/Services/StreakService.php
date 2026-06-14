<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTasbihStreak;
use Carbon\Carbon;

class StreakService
{
    public function __construct(
        private readonly AchievementEngine $achievementEngine
    ) {}
    /**
     * Update/Sync the user's tasbih streak safely.
     * Ensure no double increment per day using UTC comparison.
     * Handles conflict resolution when mobile provides offline streak counts.
     */
    public function updateStreak(User $user, ?int $mobileCurrent = null, ?int $mobileLongest = null, ?string $mobileLastActivity = null): UserTasbihStreak
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();
        $yesterday = Carbon::now('Asia/Baghdad')->subDay()->toDateString();

        $streak = $user->tasbihStreak()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_activity_date' => null,
        ]);

        $dbLastActivity = $streak->last_activity_date ? $streak->last_activity_date->toDateString() : null;

        // If user already active today, check if client has higher offline values to sync
        if ($dbLastActivity === $today) {
            if ($mobileCurrent !== null && $mobileLastActivity === $today) {
                if ($mobileCurrent > $streak->current_streak) {
                    $streak->current_streak = $mobileCurrent;
                }
                if ($mobileLongest !== null && $mobileLongest > $streak->longest_streak) {
                    $streak->longest_streak = $mobileLongest;
                }
                $streak->save();
            }
            return $streak;
        }

        // Calculate based on last database activity date
        if ($dbLastActivity === $yesterday) {
            // Continuation of streak
            $streak->current_streak += 1;
            if ($streak->current_streak > $streak->longest_streak) {
                $streak->longest_streak = $streak->current_streak;
            }
            $streak->last_activity_date = $today;
        } else {
            // Gap > 1 day or first activity -> reset/start at 1
            $streak->current_streak = 1;
            if ($streak->longest_streak === 0) {
                $streak->longest_streak = 1;
            }
            $streak->last_activity_date = $today;
        }

        // Apply client sync logic if they have a higher streak logged for today
        if ($mobileCurrent !== null && $mobileLastActivity === $today) {
            if ($mobileCurrent > $streak->current_streak) {
                $streak->current_streak = $mobileCurrent;
            }
            if ($mobileLongest !== null && $mobileLongest > $streak->longest_streak) {
                $streak->longest_streak = $mobileLongest;
            }
        }

        $streak->save();

        // Evaluate streak-based achievements after saving
        $this->achievementEngine->evaluate($user, [
            'current_streak'   => $streak->current_streak,
            'longest_streak'   => $streak->longest_streak,
            'consecutive_days' => $streak->current_streak,
        ]);

        return $streak;
    }
}
