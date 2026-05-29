<?php

namespace App\Http\Controllers;

use App\Models\ReadingHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'weekly');

        $from = match ($period) {
            'daily'   => Carbon::today(),
            'weekly'  => Carbon::now()->startOfWeek(),
            'monthly' => Carbon::now()->startOfMonth(),
            default   => null,
        };

        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        if ($from) {
            $subQuery = ReadingHistory::select('user_id', DB::raw('COUNT(*) as period_points'))
                ->where('last_read_at', '>=', $from)
                ->groupBy('user_id');

            $query = User::select(
                    'users.id', 'users.name', 'users.email',
                    'users.streak_days', 'users.longest_streak',
                    'users.points_total as alltime_points',
                    'users.created_at',
                    'users.last_read_date',
                    DB::raw('COALESCE(rh.period_points, 0) as period_points')
                )
                ->leftJoinSub($subQuery, 'rh', 'users.id', '=', 'rh.user_id')
                ->where('users.status', true)
                ->where('users.role', 'user')
                ->orderByDesc('period_points')
                ->orderByDesc('users.streak_days');
        } else {
            $query = User::select('id', 'name', 'email', 'points_total', 'streak_days', 'longest_streak', 'created_at', 'last_read_date')
                ->where('status', true)
                ->where('role', 'user')
                ->orderByDesc('points_total');
        }

        if ($isAdmin) {
            $query->withCount(['bookmarks', 'favorites', 'memorizationPlans'])
                  ->withSum('readingHistories as total_seconds_spent', 'seconds_spent');
        }

        $users = $query->paginate(25);

        // Summary stats
        $totalUsers       = User::where('role', 'user')->count();
        $activeToday      = ReadingHistory::whereDate('last_read_at', today())
                                ->distinct('user_id')->count('user_id');
        $activeThisWeek   = ReadingHistory::where('last_read_at', '>=', Carbon::now()->startOfWeek())
                                ->distinct('user_id')->count('user_id');
        $topStreaker       = User::where('role', 'user')->orderByDesc('streak_days')->first();

        return view('leaderboard', compact(
            'users', 'period', 'totalUsers',
            'activeToday', 'activeThisWeek', 'topStreaker'
        ));
    }

    public function resetUserPoints(Request $request, User $user)
    {
        $user->update(['points_total' => 0, 'streak_days' => 0]);
        return back()->with('success', "Points for {$user->name} have been reset.");
    }
}
