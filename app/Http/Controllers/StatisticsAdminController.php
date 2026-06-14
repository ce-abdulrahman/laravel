<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStatistic;
use App\Models\InsightLog;
use App\Models\StatisticsSetting;
use App\Models\TasbihSession;
use App\Models\TasbihSessionAggregate;
use App\Models\UserAchievement;
use App\Models\UserDailyGoal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsAdminController extends Controller
{
    /**
     * GET /admin/statistics
     * Global analytics dashboard.
     */
    public function index(Request $request)
    {
        $this->authorize('statistics.view');

        $totalUsers  = User::where('status', true)->count();
        $activeUsers = User::where('status', true)
            ->whereHas('tasbihSessions', fn($q) => $q->whereDate('session_date', '>=', now()->subDays(7)))
            ->count();

        $totalDhikr    = TasbihSession::where('status', 'completed')->sum('total_count');
        $totalSessions = TasbihSession::where('status', 'completed')->count();
        $totalAchieve  = UserAchievement::count();

        $avgStreak = DB::table('user_tasbih_streaks')->avg('current_streak') ?? 0;
        $totalDailyGoals = DB::table('user_daily_goals')->count();
        $avgGoal = $totalDailyGoals > 0 
            ? (DB::table('user_daily_goals')->where('is_completed', true)->count() / $totalDailyGoals) * 100 
            : 0;

        // Daily activity for last 30 days
        $dailyActivity = TasbihSessionAggregate::selectRaw('activity_date, SUM(total_dhikr_count) as total, SUM(total_sessions) as sessions')
            ->where('activity_date', '>=', now()->subDays(29)->toDateString())
            ->groupBy('activity_date')
            ->orderBy('activity_date')
            ->get();

        // Top 10 most active users
        $topUsers = UserStatistic::with('user')
            ->orderByDesc('total_dhikr')
            ->limit(10)
            ->get();

        // Most used dhikr
        $topDhikr = TasbihSession::where('status', 'completed')
            ->leftJoin('tasbihs', 'tasbih_sessions.dhikr_id', '=', 'tasbihs.id')
            ->selectRaw('COALESCE(tasbihs.name, tasbih_sessions.custom_dhikr_name, "Custom") as name, SUM(tasbih_sessions.total_count) as total')
            ->groupBy('name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Productivity score distribution
        $scoreGroups = UserStatistic::selectRaw('productivity_label, COUNT(*) as count')
            ->groupBy('productivity_label')
            ->pluck('count', 'productivity_label')
            ->toArray();

        return view('admin.statistics.index', compact(
            'totalUsers', 'activeUsers', 'totalDhikr', 'totalSessions',
            'totalAchieve', 'avgStreak', 'avgGoal',
            'dailyActivity', 'topUsers', 'topDhikr', 'scoreGroups'
        ));
    }

    /**
     * GET /admin/statistics/users
     * User analytics table.
     */
    public function users(Request $request)
    {
        $this->authorize('statistics.view');

        $search = $request->get('search');
        $sort   = $request->get('sort', 'total_dhikr');
        $order  = $request->get('order', 'desc');

        $allowedSorts = ['total_dhikr', 'total_sessions', 'productivity_score', 'current_streak', 'total_achievements'];
        if (!in_array($sort, $allowedSorts)) $sort = 'total_dhikr';

        $users = UserStatistic::with('user')
            ->when($search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'LIKE', "%{$search}%")->orWhere('email', 'LIKE', "%{$search}%")))
            ->orderBy($sort, $order)
            ->paginate(25);

        return view('admin.statistics.users', compact('users', 'search', 'sort', 'order'));
    }

    /**
     * GET /admin/statistics/insights
     * Global insight analytics.
     */
    public function insights(Request $request)
    {
        $this->authorize('statistics.insights');

        $insightsByType = InsightLog::selectRaw('insight_type, COUNT(*) as count')
            ->where('generated_at', '>=', now()->subDays(7))
            ->groupBy('insight_type')
            ->orderByDesc('count')
            ->get();

        $recentInsights = InsightLog::with('user')
            ->fresh()
            ->orderByDesc('generated_at')
            ->limit(50)
            ->get();

        return view('admin.statistics.insights', compact('insightsByType', 'recentInsights'));
    }

    /**
     * GET /admin/statistics/settings
     * Manage productivity score weights and retention policies.
     */
    public function settings(Request $request)
    {
        $this->authorize('statistics.manage');

        $settings = StatisticsSetting::orderBy('key')->get()->keyBy('key');
        return view('admin.statistics.settings', compact('settings'));
    }

    /**
     * POST /admin/statistics/settings
     */
    public function saveSettings(Request $request)
    {
        $this->authorize('statistics.manage');

        $data = $request->validate([
            'streak_weight'                   => 'required|numeric|min:0|max:1',
            'goal_weight'                     => 'required|numeric|min:0|max:1',
            'session_weight'                  => 'required|numeric|min:0|max:1',
            'achievement_weight'              => 'required|numeric|min:0|max:1',
            'snapshot_daily_retention_days'   => 'required|integer|min:7|max:365',
            'snapshot_weekly_retention_days'  => 'required|integer|min:30|max:3650',
            'insights_expire_hours'           => 'required|integer|min:1|max:168',
        ]);

        // Validate weights sum to approximately 1.0
        $sum = $data['streak_weight'] + $data['goal_weight'] + $data['session_weight'] + $data['achievement_weight'];
        if (abs($sum - 1.0) > 0.01) {
            return back()->withErrors(['weights' => __('statistics.weights_must_sum_to_one')]);
        }

        foreach ($data as $key => $value) {
            StatisticsSetting::setValue($key, $value);
        }

        return back()->with('success', __('statistics.settings_saved'));
    }
}
