<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LeaderboardScore;
use App\Models\LeaderboardEntry;
use App\Models\LeaderboardPeriod;
use App\Models\UserLeaderboardSetting;
use App\Models\SettingEntry;
use App\Models\UserDailyGoal;
use App\Services\LeaderboardEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LeaderboardAdminController extends Controller
{
    protected $leaderboardEngine;

    public function __construct(LeaderboardEngine $leaderboardEngine)
    {
        $this->leaderboardEngine = $leaderboardEngine;
    }

    /**
     * Display the leaderboard overview.
     */
    public function overview()
    {
        $totalRankedUsers = LeaderboardScore::distinct('user_id')->count('user_id');
        
        $activeParticipants = UserDailyGoal::where('today_progress', '>', 0)
            ->whereDate('goal_date', today())
            ->distinct('user_id')
            ->count('user_id');

        $topUserToday = LeaderboardScore::where('score_type', 'DAILY_DHIKR')
            ->orderByDesc('score_value')
            ->first()?->user;

        $topUserWeekly = LeaderboardScore::where('score_type', 'WEEKLY_DHIKR')
            ->orderByDesc('score_value')
            ->first()?->user;

        $topUserMonthly = LeaderboardScore::where('score_type', 'MONTHLY_DHIKR')
            ->orderByDesc('score_value')
            ->first()?->user;

        $averageScore = LeaderboardScore::where('score_type', 'CUSTOM_SCORING')
            ->avg('score_value') ?? 0;

        return view('admin.leaderboard.overview', compact(
            'totalRankedUsers',
            'activeParticipants',
            'topUserToday',
            'topUserWeekly',
            'topUserMonthly',
            'averageScore'
        ));
    }

    /**
     * Display the leaderboard list/standings management grid.
     */
    public function index(Request $request)
    {
        $periodType = $request->get('period', 'weekly');
        $search = $request->get('q');
        
        $scoreType = $this->leaderboardEngine->getScoreTypeForPeriod($periodType);

        // Fetch standings (including hidden/anonymous users for administrative oversight)
        $query = User::query()
            ->join('leaderboard_scores', 'users.id', '=', 'leaderboard_scores.user_id')
            ->leftJoin('user_leaderboard_settings', 'users.id', '=', 'user_leaderboard_settings.user_id')
            ->where('leaderboard_scores.score_type', $scoreType)
            ->where('users.role', 'user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('leaderboard_scores.score_value')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'leaderboard_scores.score_value as score',
                'leaderboard_scores.calculated_at',
                'user_leaderboard_settings.is_anonymous',
                'user_leaderboard_settings.is_hidden',
                'user_leaderboard_settings.is_public'
            )
            ->paginate(25);

        // Calculate movements dynamically for admin display
        foreach ($users as $index => $u) {
            $absoluteRank = ($users->currentPage() - 1) * $users->perPage() + $index + 1;
            $u->rank_position = $absoluteRank;
            
            // Get movement from latest entries
            $u->movement = LeaderboardEntry::where('user_id', $u->id)
                ->join('leaderboard_periods', 'leaderboard_entries.period_id', '=', 'leaderboard_periods.id')
                ->where('leaderboard_periods.type', $periodType)
                ->orderBy('leaderboard_entries.created_at', 'desc')
                ->value('movement') ?? 'new';
        }

        if ($request->has('export')) {
            return $this->exportCsv($periodType, $scoreType);
        }

        return view('admin.leaderboard.index', compact('users', 'periodType', 'search'));
    }

    /**
     * Display leaderboard weights and configurations.
     */
    public function config()
    {
        $weights = $this->leaderboardEngine->getWeights();
        
        $types = [
            'daily' => $this->leaderboardEngine->isTypeEnabled('daily'),
            'weekly' => $this->leaderboardEngine->isTypeEnabled('weekly'),
            'monthly' => $this->leaderboardEngine->isTypeEnabled('monthly'),
            'alltime' => $this->leaderboardEngine->isTypeEnabled('alltime'),
            'achievement' => $this->leaderboardEngine->isTypeEnabled('achievement'),
            'streak' => $this->leaderboardEngine->isTypeEnabled('streak'),
        ];

        return view('admin.leaderboard.config', compact('weights', 'types'));
    }

    /**
     * Save the configurations.
     */
    public function saveConfig(Request $request)
    {
        $request->validate([
            'dhikr' => 'required|integer|min:0',
            'daily_goal' => 'required|integer|min:0',
            'achievement' => 'required|integer|min:0',
            'streak' => 'required|integer|min:0',
        ]);

        SettingEntry::updateOrCreate(['key' => 'leaderboard_weight_dhikr'], ['value' => $request->input('dhikr')]);
        SettingEntry::updateOrCreate(['key' => 'leaderboard_weight_daily_goal'], ['value' => $request->input('daily_goal')]);
        SettingEntry::updateOrCreate(['key' => 'leaderboard_weight_achievement'], ['value' => $request->input('achievement')]);
        SettingEntry::updateOrCreate(['key' => 'leaderboard_weight_streak'], ['value' => $request->input('streak')]);

        // Toggle types
        foreach (['daily', 'weekly', 'monthly', 'alltime', 'achievement', 'streak'] as $t) {
            SettingEntry::updateOrCreate(
                ['key' => "leaderboard_type_enabled_{$t}"],
                ['value' => $request->has($t) ? '1' : '0']
            );
        }

        // Clear rankings cache
        app(\App\Services\LeaderboardCacheService::class)->clearCache();

        return redirect()->route('admin.leaderboard.config')->with('success', 'Leaderboard settings saved successfully.');
    }

    /**
     * Analytics and Trends page.
     */
    public function analytics()
    {
        // Competitive density (users whose scores are within 10% of top score)
        $topScore = LeaderboardScore::where('score_type', 'CUSTOM_SCORING')->max('score_value') ?? 0;
        $densityCount = 0;
        if ($topScore > 0) {
            $threshold = $topScore * 0.9;
            $densityCount = LeaderboardScore::where('score_type', 'CUSTOM_SCORING')
                ->where('score_value', '>=', $threshold)
                ->count();
        }

        // Participation Rate
        $totalUsers = User::where('role', 'user')->where('status', true)->count();
        $participatingUsers = LeaderboardScore::distinct('user_id')->count('user_id');
        $participationRate = $totalUsers > 0 ? round(($participatingUsers / $totalUsers) * 100, 1) : 0;

        // Growth trends (daily count of goal progressions)
        $dailyTrends = UserDailyGoal::where('goal_date', '>=', now()->subDays(15))
            ->selectRaw('goal_date, SUM(today_progress) as total_dhikr')
            ->groupBy('goal_date')
            ->orderBy('goal_date')
            ->get();

        // Rank movement distribution
        $movementStats = [
            'up' => LeaderboardEntry::where('movement', 'up')->count(),
            'down' => LeaderboardEntry::where('movement', 'down')->count(),
            'none' => LeaderboardEntry::where('movement', 'none')->count(),
            'new' => LeaderboardEntry::where('movement', 'new')->count(),
        ];

        return view('admin.leaderboard.analytics', compact(
            'densityCount',
            'participationRate',
            'dailyTrends',
            'movementStats'
        ));
    }

    /**
     * Export rankings as a CSV file.
     */
    protected function exportCsv(string $periodType, string $scoreType)
    {
        $query = User::query()
            ->join('leaderboard_scores', 'users.id', '=', 'leaderboard_scores.user_id')
            ->leftJoin('user_leaderboard_settings', 'users.id', '=', 'user_leaderboard_settings.user_id')
            ->where('leaderboard_scores.score_type', $scoreType)
            ->where('users.role', 'user')
            ->orderByDesc('leaderboard_scores.score_value')
            ->select(
                'users.name',
                'users.email',
                'leaderboard_scores.score_value as score',
                'user_leaderboard_settings.is_anonymous',
                'user_leaderboard_settings.is_hidden'
            );

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leaderboard_' . $periodType . '_export.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Rank', 'Name', 'Email', 'Score', 'Anonymous', 'Hidden']);

            $rank = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $rank++,
                    $row->is_anonymous ? 'Anonymous' : $row->name,
                    $row->is_anonymous ? 'N/A' : $row->email,
                    $row->score,
                    $row->is_anonymous ? 'Yes' : 'No',
                    $row->is_hidden ? 'Yes' : 'No',
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
