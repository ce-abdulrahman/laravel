<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGoalProgress;
use App\Models\DailyGoalTemplate;
use App\Services\GoalProgressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserGoalProgressController extends Controller
{
    protected $service;

    public function __construct(GoalProgressService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the goal progress monitoring dashboard and records.
     */
    public function index(Request $request)
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();
        $date = $request->input('date', $today);

        // 1. Calculate Analytics
        $totalUsers = User::count();
        $activeGoalsCount = UserGoalProgress::whereDate('goal_date', $date)->count();
        $completedGoalsCount = UserGoalProgress::whereDate('goal_date', $date)->where('is_completed', true)->count();
        
        $completionRate = $activeGoalsCount > 0 ? round(($completedGoalsCount / $activeGoalsCount) * 100, 1) : 0;
        $averagePercentage = UserGoalProgress::whereDate('goal_date', $date)->avg('percentage') ?? 0;
        $averagePercentage = round($averagePercentage, 1);

        // Most active goal type
        $mostActiveRecord = UserGoalProgress::whereDate('goal_date', $date)
            ->select('goal_id', DB::raw('count(*) as count'))
            ->groupBy('goal_id')
            ->orderBy('count', 'desc')
            ->first();

        $mostActiveGoalType = 'N/A';
        if ($mostActiveRecord) {
            $template = DailyGoalTemplate::find($mostActiveRecord->goal_id);
            if ($template) {
                // Get Kurdish name if available, fallback to default translation
                $translation = $template->translations()->where('locale', app()->getLocale())->first() 
                    ?? $template->translations()->first();
                $mostActiveGoalType = $translation ? $translation->title : "Template #{$template->id}";
            }
        }

        // 2. Load User Records
        $users = User::with(['goalProgress' => function ($query) use ($date) {
            $query->whereDate('goal_date', $date);
        }])->paginate(15);

        // Map progress helper status
        foreach ($users as $user) {
            $progress = $user->goalProgress->first();
            $user->current_progress_record = $progress;
        }

        $templates = DailyGoalTemplate::with('translations')->where('is_active', true)->get();

        return view('user-goal-progress.index', compact(
            'users',
            'date',
            'totalUsers',
            'completedGoalsCount',
            'completionRate',
            'averagePercentage',
            'mostActiveGoalType',
            'templates'
        ));
    }

    /**
     * Reset progress record for user.
     */
    public function reset(Request $request, $id)
    {
        $date = $request->input('date', Carbon::now('Asia/Baghdad')->toDateString());
        $goalId = $request->input('goal_id');

        if ($goalId) {
            UserGoalProgress::where('user_id', $id)
                ->where('goal_id', $goalId)
                ->whereDate('goal_date', $date)
                ->update([
                    'current_progress' => 0,
                    'percentage' => 0.00,
                    'is_completed' => false,
                    'completed_at' => null,
                ]);
        }

        return redirect()->route('user-goal-progress.index', ['date' => $date])
            ->with('success', 'User goal progress has been reset.');
    }

    /**
     * Adjust user progress manually.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'goal_id' => 'required|integer',
            'current_progress' => 'required|integer|min:0',
            'date' => 'required|date',
        ]);

        $goalId = (int) $request->input('goal_id');
        $currentProgress = (int) $request->input('current_progress');
        $date = $request->input('date');

        $template = DailyGoalTemplate::findOrFail($goalId);
        $goalValue = $template->value;

        $progress = UserGoalProgress::firstOrCreate([
            'user_id' => $id,
            'goal_id' => $goalId,
            'goal_date' => $date,
        ], [
            'current_progress' => 0,
            'percentage' => 0.00,
            'is_completed' => false,
        ]);

        $cappedProgress = min($currentProgress, $goalValue);
        $wasCompleted = $progress->is_completed;
        $isNowCompleted = $cappedProgress >= $goalValue;

        $progress->update([
            'current_progress' => $cappedProgress,
            'percentage' => round(($cappedProgress / $goalValue) * 100, 2),
            'is_completed' => $isNowCompleted,
            'completed_at' => ($isNowCompleted && !$wasCompleted) ? Carbon::now('UTC') : $progress->completed_at,
        ]);

        if ($isNowCompleted && !$wasCompleted) {
            $user = User::findOrFail($id);
            $user->increment('total_completed_goals');
            $this->service->evaluateBadges($user);
        }

        return redirect()->route('user-goal-progress.index', ['date' => $date])
            ->with('success', 'User goal progress updated successfully.');
    }

    /**
     * Force complete progress record for user.
     */
    public function forceComplete(Request $request, $id)
    {
        $request->validate([
            'goal_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $goalId = (int) $request->input('goal_id');
        $date = $request->input('date');

        $template = DailyGoalTemplate::findOrFail($goalId);
        $goalValue = $template->value;

        $progress = UserGoalProgress::firstOrCreate([
            'user_id' => $id,
            'goal_id' => $goalId,
            'goal_date' => $date,
        ], [
            'current_progress' => 0,
            'percentage' => 0.00,
            'is_completed' => false,
        ]);

        if (!$progress->is_completed) {
            $progress->update([
                'current_progress' => $goalValue,
                'percentage' => 100.00,
                'is_completed' => true,
                'completed_at' => Carbon::now('UTC'),
            ]);

            $user = User::findOrFail($id);
            $user->increment('total_completed_goals');
            $this->service->evaluateBadges($user);
        }

        return redirect()->route('user-goal-progress.index', ['date' => $date])
            ->with('success', 'User goal marked completed successfully.');
    }

    /**
     * Export user goal progress as CSV.
     */
    public function exportCsv(Request $request)
    {
        $date = $request->input('date', Carbon::now('Asia/Baghdad')->toDateString());

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=user_goal_progress_{$date}_export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $users = User::with(['goalProgress' => function ($query) use ($date) {
            $query->whereDate('goal_date', $date);
        }])->get();

        $callback = function () use ($users, $date) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['User ID', 'User Name', 'Email', 'Goal ID', 'Current Progress', 'Goal Value', 'Percentage', 'Status', 'Completed At']);

            foreach ($users as $user) {
                $progress = $user->goalProgress->first();
                
                $goalId = $progress ? $progress->goal_id : 'N/A';
                $currentProgress = $progress ? $progress->current_progress : 0;
                
                $goalValue = 100;
                if ($progress) {
                    $template = DailyGoalTemplate::find($progress->goal_id);
                    $goalValue = $template ? $template->value : 100;
                }

                $percentage = $progress ? $progress->percentage : 0;
                $status = ($progress && $progress->is_completed) ? 'Completed' : 'In Progress';
                $completedAt = ($progress && $progress->completed_at) ? $progress->completed_at->toIso8601String() : '';

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $goalId,
                    $currentProgress,
                    $goalValue,
                    $percentage . '%',
                    $status,
                    $completedAt
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
