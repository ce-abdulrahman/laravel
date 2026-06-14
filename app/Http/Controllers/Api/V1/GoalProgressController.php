<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GoalProgressService;
use App\Models\User;
use App\Models\DailyGoalTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalProgressController extends Controller
{
    protected $service;

    public function __construct(GoalProgressService $service)
    {
        $this->service = $service;
    }

    /**
     * Update progress toward a specific goal.
     */
    public function update(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|integer',
            'increment_value' => 'required|integer|min:1',
            'event_id' => 'required|string',
            'user_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        }

        $goalId = (int) $request->input('goal_id');
        $incrementValue = (int) $request->input('increment_value');
        $eventId = $request->input('event_id');

        if ($user) {
            $progress = $this->service->incrementProgress($user, $goalId, $incrementValue, $eventId);
            $template = DailyGoalTemplate::find($goalId);
            $goalValue = $template ? $template->value : 100;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_progress' => $progress->current_progress,
                    'goal_value' => $goalValue,
                    'percentage' => (double) $progress->percentage,
                    'is_completed' => $progress->is_completed,
                    'completed_at' => $progress->completed_at ? $progress->completed_at->toIso8601String() : null,
                ]
            ]);
        }

        // Guest mode fallback (echo calculations)
        $goalValue = 100;
        $progressVal = min($incrementValue, $goalValue);
        $percentage = round(($progressVal / $goalValue) * 100, 2);

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_progress' => $progressVal,
                'goal_value' => $goalValue,
                'percentage' => $percentage,
                'is_completed' => $progressVal >= $goalValue,
                'completed_at' => $progressVal >= $goalValue ? now()->toIso8601String() : null,
            ]
        ]);
    }

    /**
     * Get goal progress details.
     */
    public function show(Request $request, $goalId)
    {
        $user = Auth::user();
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        }

        $goalId = (int) $goalId;

        if ($user) {
            $progress = $this->service->getTodayProgressRecord($user, $goalId);
            $template = DailyGoalTemplate::find($goalId);
            $goalValue = $template ? $template->value : 100;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_progress' => $progress->current_progress,
                    'goal_value' => $goalValue,
                    'percentage' => (double) $progress->percentage,
                    'is_completed' => $progress->is_completed,
                    'completed_at' => $progress->completed_at ? $progress->completed_at->toIso8601String() : null,
                ]
            ]);
        }

        // Guest default
        return response()->json([
            'status' => 'success',
            'data' => [
                'current_progress' => 0,
                'goal_value' => 100,
                'percentage' => 0.0,
                'is_completed' => false,
                'completed_at' => null,
            ]
        ]);
    }

    /**
     * Reset today's progress (Admin action / trigger).
     */
    public function reset(Request $request)
    {
        // Simple authentication check or admin check
        $user = Auth::user();
        if ($user && !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $period = $request->input('period', 'daily');
        $userId = $request->input('user_id');

        $targetUser = $userId ? User::find($userId) : null;

        $this->service->resetProgress($period, $targetUser);

        return response()->json([
            'status' => 'success',
            'message' => 'Progress reset successfully.'
        ]);
    }
}
