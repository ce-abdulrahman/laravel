<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDailyGoal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserGoalController extends Controller
{
    /**
     * Display users daily goals overview with analytics.
     */
    public function index(Request $request)
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();
        $date = $request->input('date', $today);

        // 1. Calculate Analytics for the selected date
        $totalUsers = User::count();
        
        $activeGoalsCount = UserDailyGoal::whereDate('goal_date', $date)->count();
        $completedGoalsCount = UserDailyGoal::whereDate('goal_date', $date)->where('is_completed', true)->count();
        
        $completionRate = $activeGoalsCount > 0 ? round(($completedGoalsCount / $activeGoalsCount) * 100, 1) : 0;
        
        $averageProgress = UserDailyGoal::whereDate('goal_date', $date)->avg('today_progress') ?? 0;
        $averageProgress = round($averageProgress, 1);

        // Most popular goal target for the date
        $popularGoal = UserDailyGoal::whereDate('goal_date', $date)
            ->select('goal_value')
            ->groupBy('goal_value')
            ->orderByRaw('COUNT(*) DESC')
            ->value('goal_value') ?? 'N/A';

        // 2. Query Users with their daily goals for the selected date
        $query = User::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->through(function ($user) use ($date) {
            $goal = $user->dailyGoals()->whereDate('goal_date', $date)->first();
            $user->today_goal = $goal;
            return $user;
        });

        return view('user-goals.index', compact(
            'users',
            'activeGoalsCount',
            'completionRate',
            'averageProgress',
            'popularGoal',
            'date',
            'today'
        ));
    }

    /**
     * Reset a user's daily progress manually.
     */
    public function reset(Request $request, $id)
    {
        $date = $request->input('date', Carbon::now('Asia/Baghdad')->toDateString());

        $goal = UserDailyGoal::where('user_id', $id)
            ->whereDate('goal_date', $date)
            ->first();

        if (!$goal) {
            $goal = UserDailyGoal::create([
                'user_id' => $id,
                'goal_date' => $date,
                'goal_value' => 100,
                'today_progress' => 0,
                'is_completed' => false
            ]);
        }

        $goal->update([
            'today_progress' => 0,
            'is_completed' => false
        ]);

        return redirect()->route('user-goals.index', ['date' => $date])
            ->with('success', 'User daily progress reset successfully.');
    }

    /**
     * Edit a user's daily goal and progress values.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'goal_value' => 'required|integer|min:1',
            'today_progress' => 'required|integer|min:0',
            'date' => 'required|date',
        ]);

        $date = $request->input('date');

        $goal = UserDailyGoal::where('user_id', $id)
            ->whereDate('goal_date', $date)
            ->first();

        if (!$goal) {
            $goal = UserDailyGoal::create([
                'user_id' => $id,
                'goal_date' => $date,
                'goal_value' => 100,
                'today_progress' => 0,
                'is_completed' => false
            ]);
        }

        $goalValue = (int) $request->input('goal_value');
        $todayProgress = (int) $request->input('today_progress');

        $goal->update([
            'goal_value' => $goalValue,
            'today_progress' => $todayProgress,
            'is_completed' => $todayProgress >= $goalValue
        ]);

        return redirect()->route('user-goals.index', ['date' => $date])
            ->with('success', 'User daily goal updated successfully.');
    }

    /**
     * Export CSV report of user goals for a date.
     */
    public function exportCsv(Request $request)
    {
        $date = $request->input('date', Carbon::now('Asia/Baghdad')->toDateString());

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=user_daily_goals_{$date}_export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $users = User::all();

        $callback = function () use ($users, $date) {
            $file = fopen('php://output', 'w');
            // Output UTF-8 BOM for proper Arabic/Kurdish characters support in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['User ID', 'User Name', 'Email', 'Goal Date', 'Goal Value', 'Progress', 'Percentage', 'Status']);

            foreach ($users as $user) {
                $goal = $user->dailyGoals()->whereDate('goal_date', $date)->first();
                
                $goalValue = $goal ? $goal->goal_value : 'N/A';
                $progress = $goal ? $goal->today_progress : 0;
                $percentage = $goal && $goal->goal_value > 0 ? round(($progress / $goal->goal_value) * 100, 1) . '%' : '0%';
                $status = $goal ? ($goal->is_completed ? 'Completed' : 'In Progress') : 'Not Started';

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $date,
                    $goalValue,
                    $progress,
                    $percentage,
                    $status
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
