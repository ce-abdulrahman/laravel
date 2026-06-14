<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserGoalProgress;
use App\Models\UserGoalProgressEvent;
use App\Models\DailyGoalTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GoalProgressService
{
    public function __construct(
        private readonly AchievementEngine $achievementEngine
    ) {}
    /**
     * Get or initialize today's progress record for the user and goal template.
     */
    public function getTodayProgressRecord(User $user, int $goalId, string $period = 'daily'): UserGoalProgress
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();

        $template = DailyGoalTemplate::find($goalId);
        $goalValue = $template ? $template->value : 100;

        return UserGoalProgress::firstOrCreate([
            'user_id' => $user->id,
            'goal_id' => $goalId,
            'goal_date' => $today,
            'period' => $period,
        ], [
            'current_progress' => 0,
            'percentage' => 0.00,
            'is_completed' => false,
        ]);
    }

    /**
     * Increment progress safely, preventing duplicates.
     */
    public function incrementProgress(User $user, int $goalId, int $incrementValue, string $eventId, string $period = 'daily'): UserGoalProgress
    {
        return DB::transaction(function () use ($user, $goalId, $incrementValue, $eventId, $period) {
            // 1. Check if the event_id has already been processed (idempotency)
            $eventExists = UserGoalProgressEvent::where('event_id', $eventId)->exists();
            if ($eventExists) {
                return $this->getTodayProgressRecord($user, $goalId, $period);
            }

            // Record the event immediately
            UserGoalProgressEvent::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'created_at' => Carbon::now('UTC'),
            ]);

            // 2. Fetch or create the progress record
            $progress = $this->getTodayProgressRecord($user, $goalId, $period);

            // Completed goals are read-only until reset
            if ($progress->is_completed) {
                return $progress;
            }

            // Fetch goal value from template directly (backend single source of truth)
            $template = DailyGoalTemplate::find($goalId);
            $goalValue = $template ? $template->value : 100;

            // Increment and cap
            $newProgress = $progress->current_progress + $incrementValue;
            $progress->current_progress = min($newProgress, $goalValue);
            $progress->percentage = round(($progress->current_progress / $goalValue) * 100, 2);

            // Completion check
            if ($progress->current_progress >= $goalValue) {
                $progress->is_completed = true;
                $progress->completed_at = Carbon::now('UTC');

                // Increment user completed goals count
                $user->increment('total_completed_goals');
                $user->refresh();
                
                // Evaluate dynamic badges
                $this->evaluateBadges($user);

                // Evaluate goal-related achievements
                $this->achievementEngine->evaluate($user, [
                    'goals_completed' => $user->total_completed_goals,
                ]);
            }

            $progress->save();

            return $progress;
        });
    }

    /**
     * Evaluate and award Bronze, Silver, Gold badges.
     */
    public function evaluateBadges(User $user): void
    {
        $completionsCount = $user->total_completed_goals;

        $badgeThresholds = [
            'bronze' => 1,
            'silver' => 10,
            'gold' => 50,
        ];

        foreach ($badgeThresholds as $badgeType => $threshold) {
            if ($completionsCount >= $threshold) {
                $user->badges()->firstOrCreate([
                    'badge_type' => $badgeType,
                ], [
                    'awarded_at' => Carbon::now('UTC'),
                ]);
            }
        }

        $user->last_badge_calculated_at = Carbon::now('UTC');
        $user->save();
    }

    /**
     * Reset today's progress values.
     */
    public function resetProgress(string $period = 'daily', ?User $user = null): void
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();

        $query = UserGoalProgress::where('period', $period)->where('goal_date', $today);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $query->update([
            'current_progress' => 0,
            'percentage' => 0.00,
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }
}
