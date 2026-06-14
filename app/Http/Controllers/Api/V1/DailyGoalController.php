<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DailyGoalService;
use Illuminate\Http\Request;

class DailyGoalController extends Controller
{
    protected $dailyGoalService;

    public function __construct(DailyGoalService $dailyGoalService)
    {
        $this->dailyGoalService = $dailyGoalService;
    }

    /**
     * Get today's daily goal status.
     * Accessible by both guests and authenticated users.
     */
    public function getToday(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user) {
            // Guest mode: extract values from parameters or use default
            $goalValue = (int) $request->query('goal_value', 100);
            $todayProgress = (int) $request->query('today_progress', 0);
            $percentage = $goalValue > 0 ? round(($todayProgress / $goalValue) * 100, 1) : 0.0;
            $isCompleted = $todayProgress >= $goalValue;

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => $goalValue,
                    'today_progress' => $todayProgress,
                    'percentage' => $percentage,
                    'is_completed' => $isCompleted
                ]
            ]);
        }

        $goal = $this->dailyGoalService->getTodayGoal($user);
        $percentage = $goal->goal_value > 0 ? round(($goal->today_progress / $goal->goal_value) * 100, 1) : 0.0;

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'goal_value' => $goal->goal_value,
                'today_progress' => $goal->today_progress,
                'percentage' => $percentage,
                'is_completed' => $goal->is_completed
            ]
        ]);
    }

    /**
     * Update progress of today's daily goal.
     * Accessible by both guests and authenticated users.
     */
    public function updateProgress(Request $request)
    {
        $user = $request->user('sanctum');
        
        $request->validate([
            'increment_value' => 'required|integer|min:1',
            'goal_value' => 'nullable|integer|min:1',
            'today_progress' => 'nullable|integer|min:0',
        ]);

        $incrementValue = (int) $request->input('increment_value');

        if (!$user) {
            // Guest mode: update parameters locally and echo back
            $goalValue = (int) $request->input('goal_value', 100);
            $todayProgress = (int) $request->input('today_progress', 0) + $incrementValue;
            $percentage = $goalValue > 0 ? round(($todayProgress / $goalValue) * 100, 1) : 0.0;
            $isCompleted = $todayProgress >= $goalValue;

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => $goalValue,
                    'today_progress' => $todayProgress,
                    'percentage' => $percentage,
                    'is_completed' => $isCompleted
                ]
            ]);
        }

        $goal = $this->dailyGoalService->updateProgress($user, $incrementValue);
        $percentage = $goal->goal_value > 0 ? round(($goal->today_progress / $goal->goal_value) * 100, 1) : 0.0;

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'goal_value' => $goal->goal_value,
                'today_progress' => $goal->today_progress,
                'percentage' => $percentage,
                'is_completed' => $goal->is_completed
            ]
        ]);
    }

    /**
     * Set today's daily goal target value.
     * Accessible by both guests and authenticated users.
     */
    public function setGoal(Request $request)
    {
        $user = $request->user('sanctum');

        $request->validate([
            'goal_value' => 'required|integer|min:1',
            'today_progress' => 'nullable|integer|min:0',
        ]);

        $goalValue = (int) $request->input('goal_value');

        if (!$user) {
            // Guest mode
            $todayProgress = (int) $request->input('today_progress', 0);
            $percentage = $goalValue > 0 ? round(($todayProgress / $goalValue) * 100, 1) : 0.0;
            $isCompleted = $todayProgress >= $goalValue;

            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'goal_value' => $goalValue,
                    'today_progress' => $todayProgress,
                    'percentage' => $percentage,
                    'is_completed' => $isCompleted
                ]
            ]);
        }

        $goal = $this->dailyGoalService->setGoal($user, $goalValue);
        $percentage = $goal->goal_value > 0 ? round(($goal->today_progress / $goal->goal_value) * 100, 1) : 0.0;

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'goal_value' => $goal->goal_value,
                'today_progress' => $goal->today_progress,
                'percentage' => $percentage,
                'is_completed' => $goal->is_completed
            ]
        ]);
    }
}
