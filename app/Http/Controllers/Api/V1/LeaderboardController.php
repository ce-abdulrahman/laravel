<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReadingHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    /**
     * GET /api/v1/leaderboard?period=daily|weekly|monthly|alltime
     * Public route — no auth required.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $limit  = min((int) $request->get('limit', 20), 50);

        $query = User::select('id', 'name', 'points_total', 'streak_days', 'longest_streak')
            ->where('status', true)
            ->where('role', 'user');

        // For time-scoped periods, compute points from reading_histories
        if ($period !== 'alltime') {
            $from = match ($period) {
                'daily'   => Carbon::today(),
                'weekly'  => Carbon::now()->startOfWeek(),
                'monthly' => Carbon::now()->startOfMonth(),
                default   => Carbon::today(),
            };

            // Sub-query: points earned in period = count of distinct reading entries per user
            $subQuery = ReadingHistory::select('user_id', DB::raw('COUNT(*) as period_points'))
                ->where('last_read_at', '>=', $from)
                ->groupBy('user_id');

            $query = User::select(
                    'users.id',
                    'users.name',
                    'users.streak_days',
                    'users.longest_streak',
                    DB::raw('COALESCE(rh.period_points, 0) as points_total')
                )
                ->leftJoinSub($subQuery, 'rh', 'users.id', '=', 'rh.user_id')
                ->where('users.status', true)
                ->where('users.role', 'user');
        }

        $users = $query->orderByDesc('points_total')
            ->orderByDesc('streak_days')
            ->limit($limit)
            ->get();

        // Add rank
        $leaderboard = $users->values()->map(function ($user, $index) {
            return [
                'rank'           => $index + 1,
                'user_id'        => $user->id,
                'name'           => $user->name,
                'points'         => (int) $user->points_total,
                'streak_days'    => $user->streak_days,
                'longest_streak' => $user->longest_streak,
            ];
        });

        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $leaderboard,
        ]);
    }

    /**
     * GET /api/v1/me/stats — authenticated user's own stats
     */
    public function myStats(Request $request)
    {
        $user   = $request->user();
        $userId = $user->id;

        // Rank among all users
        $rank = User::where('points_total', '>', $user->points_total)
            ->where('status', true)
            ->count() + 1;

        $totalUsers = User::where('status', true)->count();

        // Weekly points
        $weeklyPoints = ReadingHistory::where('user_id', $userId)
            ->where('last_read_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        // Monthly points
        $monthlyPoints = ReadingHistory::where('user_id', $userId)
            ->where('last_read_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        // Reading days this week (for mini heatmap)
        $weeklyActivity = ReadingHistory::where('user_id', $userId)
            ->where('last_read_at', '>=', Carbon::now()->startOfWeek())
            ->selectRaw('DATE(last_read_at) as date, COUNT(*) as ayahs_read')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map(fn($d) => $d->ayahs_read);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user'            => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'points_total'   => $user->points_total,
                    'streak_days'    => $user->streak_days,
                    'longest_streak' => $user->longest_streak,
                    'last_read_date' => $user->last_read_date?->toDateString(),
                ],
                'rank'            => $rank,
                'total_users'     => $totalUsers,
                'weekly_points'   => $weeklyPoints,
                'monthly_points'  => $monthlyPoints,
                'weekly_activity' => $weeklyActivity,
            ],
        ]);
    }
}
