<?php

namespace App\Services;

use App\Models\User;
use App\Models\TasbihSession;
use App\Models\UserDailyGoal;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProfileStatisticsService
{
    /**
     * Get aggregated profile statistics, utilizing caching.
     */
    public function getStats(User $user): array
    {
        $cacheKey = "user_profile_stats_{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $totalDhikr = (int) TasbihSession::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('total_count');

            $streakRecord = DB::table('user_tasbih_streaks')
                ->where('user_id', $user->id)
                ->first();

            $currentStreak = $streakRecord ? (int) $streakRecord->current_streak : 0;
            $longestStreak = $streakRecord ? (int) $streakRecord->longest_streak : 0;

            $totalGoals = UserDailyGoal::where('user_id', $user->id)->count();
            $completedGoals = UserDailyGoal::where('user_id', $user->id)
                ->where('is_completed', true)
                ->count();

            $goalCompletionRate = $totalGoals > 0 
                ? (int) round(($completedGoals / $totalGoals) * 100) 
                : 0;

            $achievementsCount = UserAchievement::where('user_id', $user->id)->count();
            $totalSessions = TasbihSession::where('user_id', $user->id)->count();

            $fingerprintStats = DB::table('fingerprint_statistics')
                ->where('user_id', $user->id)
                ->first();
            $fingerprintTotalCounts = $fingerprintStats ? (int) $fingerprintStats->total_counts : 0;

            return [
                'total_dhikrs' => $totalDhikr,
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
                'goals_completed_count' => $completedGoals,
                'goal_completion_rate' => $goalCompletionRate,
                'achievements_count' => $achievementsCount,
                'total_sessions' => $totalSessions,
                'fingerprint_total_counts' => $fingerprintTotalCounts,
            ];
        });
    }

    /**
     * Invalidate the cached statistics for a user.
     */
    public function invalidateCache(int $userId): void
    {
        Cache::forget("user_profile_stats_{$userId}");
    }
}
