<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDailyGoal;
use Carbon\Carbon;

class DailyGoalService
{
    /**
     * Fetch or initialize today's daily goal for the user.
     * Uses UTC date representation.
     */
    public function getTodayGoal(User $user): UserDailyGoal
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();

        // Check if today's goal already exists
        $goal = $user->dailyGoals()->whereDate('goal_date', $today)->first();

        if ($goal) {
            return $goal;
        }

        // Otherwise, find the user's most recent daily goal to carry over the target
        $lastGoal = $user->dailyGoals()
            ->orderBy('goal_date', 'desc')
            ->first();

        $target = $lastGoal ? $lastGoal->goal_value : 100; // default to 100 if none exists

        // Create new record for today
        return $user->dailyGoals()->create([
            'goal_value' => $target,
            'today_progress' => 0,
            'goal_date' => $today,
            'is_completed' => false,
        ]);
    }

    /**
     * Safely increment the progress for the user's today goal.
     */
    public function updateProgress(User $user, int $incrementValue): UserDailyGoal
    {
        $goal = $this->getTodayGoal($user);

        $goal->today_progress += $incrementValue;
        
        // Re-evaluate completion status
        if ($goal->today_progress >= $goal->goal_value) {
            $goal->is_completed = true;
        } else {
            $goal->is_completed = false; // Just in case progress was modified downwards in admin
        }

        $goal->save();

        return $goal;
    }

    /**
     * Set a custom goal value for the user's today goal.
     */
    public function setGoal(User $user, int $goalValue): UserDailyGoal
    {
        $goal = $this->getTodayGoal($user);

        $goal->goal_value = max(1, $goalValue); // Min value is 1
        
        // Re-evaluate completion status
        $goal->is_completed = $goal->today_progress >= $goal->goal_value;
        
        $goal->save();

        return $goal;
    }
}
