<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTasbihStreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserStreakController extends Controller
{
    /**
     * Display users streak overview page with analytics.
     */
    public function index(Request $request)
    {
        $today = Carbon::now('Asia/Baghdad')->toDateString();
        $yesterday = Carbon::now('Asia/Baghdad')->subDay()->toDateString();

        // 1. Calculate Analytics
        $totalUsers = User::count();
        
        $activeCount = UserTasbihStreak::whereIn('last_activity_date', [$today, $yesterday])->count();
        $dropRate = $totalUsers > 0 ? round((($totalUsers - $activeCount) / $totalUsers) * 100, 1) : 0;
        
        $averageStreak = UserTasbihStreak::where('current_streak', '>', 0)->avg('current_streak') ?? 0;
        $averageStreak = round($averageStreak, 1);

        $activeToday = UserTasbihStreak::where('last_activity_date', $today)->count();

        // Top 10 users by streak
        $topUsers = UserTasbihStreak::with('user')
            ->orderBy('current_streak', 'desc')
            ->take(10)
            ->get();

        // 2. Paginated Users with Streaks
        $query = User::with('tasbihStreak');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return view('user-streaks.index', compact(
            'users',
            'dropRate',
            'averageStreak',
            'activeToday',
            'topUsers',
            'today',
            'yesterday'
        ));
    }

    /**
     * Reset a user's streak manually.
     */
    public function reset($id)
    {
        $streak = UserTasbihStreak::firstOrCreate([
            'user_id' => $id
        ]);
        
        $streak->update([
            'current_streak' => 0,
            'last_activity_date' => null
        ]);

        return redirect()->route('user-streaks.index')
            ->with('success', 'User streak reset successfully.');
    }

    /**
     * Edit a user's streak values.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'current_streak' => 'required|integer|min:0',
            'longest_streak' => 'required|integer|min:0',
            'last_activity_date' => 'nullable|date',
        ]);

        $streak = UserTasbihStreak::firstOrCreate([
            'user_id' => $id
        ]);
        
        $streak->update($request->only(['current_streak', 'longest_streak', 'last_activity_date']));

        return redirect()->route('user-streaks.index')
            ->with('success', 'User streak updated successfully.');
    }

    /**
     * Export CSV report of all user streaks.
     */
    public function exportCsv()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=user_tasbih_streaks_export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $users = User::with('tasbihStreak')->get();

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            // Output UTF-8 BOM for proper Arabic/Kurdish characters support in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['User ID', 'User Name', 'Email', 'Current Streak', 'Longest Streak', 'Last Activity Date', 'Status']);

            $today = Carbon::now('Asia/Baghdad')->toDateString();
            $yesterday = Carbon::now('Asia/Baghdad')->subDay()->toDateString();

            foreach ($users as $user) {
                $streak = $user->tasbihStreak;
                $lastActivity = $streak && $streak->last_activity_date ? $streak->last_activity_date->toDateString() : null;
                $status = ($lastActivity === $today || $lastActivity === $yesterday) ? 'Active' : 'Broken';
                
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $streak ? $streak->current_streak : 0,
                    $streak ? $streak->longest_streak : 0,
                    $lastActivity ?? 'N/A',
                    $status
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
