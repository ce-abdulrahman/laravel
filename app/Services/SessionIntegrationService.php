<?php

namespace App\Services;

use App\Models\User;
use App\Models\TasbihSession;
use App\Models\UserDailyGoal;
use App\Services\DailyGoalService;
use App\Services\StreakService;
use App\Services\AchievementEngine;
use App\Services\LeaderboardCacheService;

class SessionIntegrationService
{
    public function __construct(
        private readonly DailyGoalService $dailyGoalService,
        private readonly StreakService $streakService,
        private readonly AchievementEngine $achievementEngine,
        private readonly LeaderboardCacheService $cacheService
    ) {}

    /**
     * Integrate completed session metrics across goals, streaks, achievements, and leaderboard.
     */
    public function integrateSession(User $user, TasbihSession $session): void
    {
        // 1. Sync session count to user's daily goals progress
        $this->dailyGoalService->updateProgress($user, $session->total_count);

        // 2. Sync session date to user's streak system
        $this->streakService->updateStreak($user);

        // 3. Recompute total user dhikr to supply to the achievement evaluation engine
        $totalDhikr = (int) UserDailyGoal::where('user_id', $user->id)->sum('today_progress');

        // Evaluate achievements
        $this->achievementEngine->evaluate($user, [
            'session_dhikr_count' => $session->total_count,
            'total_dhikr_count'   => $totalDhikr,
        ]);

        // 4. Force leaderboard cache updates for the user
        $this->cacheService->clearCache();
    }
}
