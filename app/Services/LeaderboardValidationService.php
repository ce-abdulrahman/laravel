<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyGoal;
use App\Models\UserTasbihStreak;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\Log;

class LeaderboardValidationService
{
    /**
     * Recompute and validate the user's score to detect/prevent spoofing.
     * Compares raw database activity with reported values.
     */
    public function validateAndRecompute(User $user, string $scoreType): int
    {
        $calculatedScore = 0;

        switch ($scoreType) {
            case 'TOTAL_DHIKR':
            case 'ALL_TIME_DHIKR':
                // Sum of all progress logged in user_daily_goals
                $calculatedScore = (int) UserDailyGoal::where('user_id', $user->id)->sum('today_progress');
                break;

            case 'DAILY_DHIKR':
                // Today's progress
                $today = \Carbon\Carbon::now('Asia/Baghdad')->toDateString();
                $calculatedScore = (int) UserDailyGoal::where('user_id', $user->id)
                    ->whereDate('goal_date', $today)
                    ->value('today_progress');
                break;

            case 'WEEKLY_DHIKR':
                // Sum of progress this week
                $startOfWeek = \Carbon\Carbon::now('Asia/Baghdad')->startOfWeek()->toDateString();
                $calculatedScore = (int) UserDailyGoal::where('user_id', $user->id)
                    ->where('goal_date', '>=', $startOfWeek)
                    ->sum('today_progress');
                break;

            case 'MONTHLY_DHIKR':
                // Sum of progress this month
                $startOfMonth = \Carbon\Carbon::now('Asia/Baghdad')->startOfMonth()->toDateString();
                $calculatedScore = (int) UserDailyGoal::where('user_id', $user->id)
                    ->where('goal_date', '>=', $startOfMonth)
                    ->sum('today_progress');
                break;

            case 'CURRENT_STREAK':
                $calculatedScore = (int) UserTasbihStreak::where('user_id', $user->id)->value('current_streak');
                break;

            case 'LONGEST_STREAK':
                $calculatedScore = (int) UserTasbihStreak::where('user_id', $user->id)->value('longest_streak');
                break;

            case 'GOALS_COMPLETED':
                // Count of completed goals
                $calculatedScore = (int) UserDailyGoal::where('user_id', $user->id)
                    ->where('is_completed', true)
                    ->count();
                break;

            case 'ACHIEVEMENTS_EARNED':
                // Count of completed achievements
                $calculatedScore = (int) UserAchievement::where('user_id', $user->id)
                    ->where('is_completed', true)
                    ->count();
                break;

            case 'ACHIEVEMENT_POINTS':
                // Sum of points for unlocked achievements
                $calculatedScore = (int) UserAchievement::where('user_id', $user->id)
                    ->where('is_completed', true)
                    ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
                    ->sum('achievements.reward_points');
                break;

            default:
                $calculatedScore = 0;
                break;
        }

        return $calculatedScore;
    }

    /**
     * Checks if a score submission is within humanly-possible boundaries.
     * Prevents automated API flooding cheats.
     */
    public function isIncrementCheat(int $incrementValue): bool
    {
        // If a single increment exceeds 10,000 tasbihs, flag it
        if ($incrementValue > 10000) {
            Log::warning("Cheat detection flagged: bulk increment of {$incrementValue} requested.");
            return true;
        }
        return false;
    }
}
