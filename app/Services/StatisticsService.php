<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStatistic;
use App\Models\StatisticsSnapshot;
use App\Models\StatisticsSetting;
use App\Models\TasbihSession;
use App\Models\TasbihSessionAggregate;
use App\Models\UserDailyGoal;
use App\Models\UserAchievement;
use App\Models\Achievement;
use App\Models\ReminderLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatisticsService
{
    // ── Period Helpers ─────────────────────────────────────────────────────────

    /**
     * Returns [Carbon $from, Carbon $to] for a named period string.
     */
    public function periodRange(string $period): array
    {
        $now = Carbon::now();
        return match ($period) {
            'today'  => [Carbon::today(), $now],
            '7d'     => [$now->copy()->subDays(6)->startOfDay(), $now],
            '30d'    => [$now->copy()->subDays(29)->startOfDay(), $now],
            '90d'    => [$now->copy()->subDays(89)->startOfDay(), $now],
            '12m'    => [$now->copy()->subMonths(11)->startOfMonth(), $now],
            'all'    => [Carbon::createFromDate(2020, 1, 1), $now],
            default  => [$now->copy()->subDays(29)->startOfDay(), $now],
        };
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    /**
     * Full dashboard metrics — uses cached row or recalculates.
     */
    public function getDashboard(User $user): array
    {
        $stat = UserStatistic::where('user_id', $user->id)->first();

        // If no cached row or stale (> 6 hours), recalculate inline
        if (!$stat || $stat->last_calculated_at?->lt(now()->subHours(6))) {
            $stat = $this->recalculate($user);
        }

        return [
            'total_dhikr'               => $stat->total_dhikr,
            'total_sessions'            => $stat->total_sessions,
            'current_streak'            => $stat->current_streak,
            'longest_streak'            => $stat->longest_streak,
            'total_streak_days'         => $stat->total_streak_days,
            'total_goals_completed'     => $stat->total_goals_completed,
            'total_goals_missed'        => $stat->total_goals_missed,
            'goal_completion_rate'      => $stat->goal_completion_rate,
            'total_achievements'        => $stat->total_achievements,
            'rare_achievements'         => $stat->rare_achievements,
            'fingerprint_total_counts'  => $stat->fingerprint_total_counts,
            'fingerprint_total_sessions'=> $stat->fingerprint_total_sessions,
            'current_rank'              => $stat->current_rank,
            'highest_rank'              => $stat->highest_rank,
            'reminders_sent'            => $stat->reminders_sent,
            'reminders_opened'          => $stat->reminders_opened,
            'productivity_score'        => $stat->productivity_score,
            'productivity_label'        => $stat->productivity_label,
            'last_calculated_at'        => $stat->last_calculated_at?->toIso8601String(),
        ];
    }

    // ── Dhikr Analytics ───────────────────────────────────────────────────────

    /**
     * Daily dhikr counts for chart + trend comparison.
     */
    public function getDhikrAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        // Current period
        $current = TasbihSessionAggregate::where('user_id', $user->id)
            ->whereBetween('activity_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('activity_date')
            ->get(['activity_date', 'total_dhikr_count', 'total_sessions']);

        $totalCurrent = $current->sum('total_dhikr_count');

        // Previous equivalent period for trend comparison
        $periodDays = $from->diffInDays($to) + 1;
        $prevFrom   = $from->copy()->subDays($periodDays);
        $prevTo     = $from->copy()->subDay();

        $totalPrevious = TasbihSessionAggregate::where('user_id', $user->id)
            ->whereBetween('activity_date', [$prevFrom->toDateString(), $prevTo->toDateString()])
            ->sum('total_dhikr_count');

        $trendPct = $totalPrevious > 0
            ? round((($totalCurrent - $totalPrevious) / $totalPrevious) * 100, 1)
            : ($totalCurrent > 0 ? 100.0 : 0.0);

        // Dhikr breakdown by tasbih name
        $breakdown = TasbihSession::where('tasbih_sessions.user_id', $user->id)
            ->whereBetween('tasbih_sessions.session_date', [$from->toDateString(), $to->toDateString()])
            ->where('tasbih_sessions.status', 'completed')
            ->leftJoin('tasbihs', 'tasbih_sessions.dhikr_id', '=', 'tasbihs.id')
            ->selectRaw('COALESCE(tasbihs.name, tasbih_sessions.custom_dhikr_name, "Custom") as dhikr_name, SUM(tasbih_sessions.total_count) as total')
            ->groupBy('dhikr_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $breakdownTotal = $breakdown->sum('total') ?: 1;

        return [
            'chart_data'  => $current->map(fn($r) => [
                'date'  => $r->activity_date->toDateString(),
                'count' => (int) $r->total_dhikr_count,
            ])->values()->all(),
            'total_current'   => (int) $totalCurrent,
            'total_previous'  => (int) $totalPrevious,
            'trend_pct'       => $trendPct,
            'trend_direction' => $trendPct >= 0 ? 'up' : 'down',
            'breakdown'       => $breakdown->map(fn($r) => [
                'name'       => $r->dhikr_name,
                'total'      => (int) $r->total,
                'percentage' => round(($r->total / $breakdownTotal) * 100, 1),
            ])->values()->all(),
        ];
    }

    // ── Session Analytics ─────────────────────────────────────────────────────

    public function getSessionAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $sessions = TasbihSession::where('user_id', $user->id)
            ->whereBetween('session_date', [$from->toDateString(), $to->toDateString()])
            ->where('status', 'completed')
            ->get();

        $prevDays  = $from->diffInDays($to) + 1;
        $prevFrom  = $from->copy()->subDays($prevDays);
        $prevTo    = $from->copy()->subDay();
        $prevCount = TasbihSession::where('user_id', $user->id)
            ->whereBetween('session_date', [$prevFrom->toDateString(), $prevTo->toDateString()])
            ->where('status', 'completed')
            ->count();

        $count        = $sessions->count();
        $avgDuration  = $count > 0 ? (int) round($sessions->avg('duration_seconds')) : 0;
        $longestSecs  = $sessions->max('duration_seconds') ?? 0;
        $avgPerMin    = $count > 0 ? round($sessions->avg('avg_per_minute'), 1) : 0.0;
        $trendPct     = $prevCount > 0
            ? round((($count - $prevCount) / $prevCount) * 100, 1)
            : ($count > 0 ? 100.0 : 0.0);

        // Most productive hour
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $hourExpr = $isSqlite ? "CAST(strftime('%H', tasbih_session_logs.timestamp) AS INTEGER)" : "HOUR(tasbih_session_logs.timestamp)";

        $peakHourRow = DB::table('tasbih_session_logs')
            ->join('tasbih_sessions', 'tasbih_session_logs.session_id', '=', 'tasbih_sessions.id')
            ->where('tasbih_sessions.user_id', $user->id)
            ->whereBetween('tasbih_sessions.session_date', [$from->toDateString(), $to->toDateString()])
            ->where('tasbih_session_logs.event_type', 'increment')
            ->selectRaw("{$hourExpr} as h, SUM(tasbih_session_logs.value) as t")
            ->groupBy('h')
            ->orderByDesc('t')
            ->first();

        $peakHour = $peakHourRow ? (int)$peakHourRow->h : null;

        // Most productive day of week
        $dowExpr = $isSqlite ? "CAST(strftime('%w', activity_date) AS INTEGER) + 1" : "DAYOFWEEK(activity_date)";

        $peakDayRow = TasbihSessionAggregate::where('user_id', $user->id)
            ->whereBetween('activity_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("{$dowExpr} as dow, SUM(total_dhikr_count) as total")
            ->groupBy('dow')
            ->orderByDesc('total')
            ->first();

        $daysMap = [1 => 'Sunday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Wednesday', 5 => 'Thursday', 6 => 'Friday', 7 => 'Saturday'];
        $peakDay = $peakDayRow ? ($daysMap[$peakDayRow->dow] ?? null) : null;

        return [
            'total_sessions'        => $count,
            'avg_duration_seconds'  => $avgDuration,
            'longest_session_secs'  => (int) $longestSecs,
            'avg_dhikr_per_minute'  => $avgPerMin,
            'most_productive_hour'  => $peakHour,
            'most_productive_day'   => $peakDay,
            'sessions_trend_pct'    => $trendPct,
            'prev_period_sessions'  => $prevCount,
        ];
    }

    // ── Goal Analytics ────────────────────────────────────────────────────────

    public function getGoalAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $goals     = UserDailyGoal::where('user_id', $user->id)
            ->whereBetween('goal_date', [$from->toDateString(), $to->toDateString()])
            ->get();
        $completed = $goals->where('is_completed', true)->count();
        $missed    = $goals->where('is_completed', false)->count();
        $total     = $goals->count();
        $rate      = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        $periodDays = $from->diffInDays($to) + 1;
        $prevFrom   = $from->copy()->subDays($periodDays);
        $prevTo     = $from->copy()->subDay();
        $prevRate   = 0.0;
        $prevGoals  = UserDailyGoal::where('user_id', $user->id)
            ->whereBetween('goal_date', [$prevFrom->toDateString(), $prevTo->toDateString()])
            ->get();
        if ($prevGoals->count() > 0) {
            $prevRate = round(($prevGoals->where('is_completed', true)->count() / $prevGoals->count()) * 100, 1);
        }

        // Daily completion trend for chart
        $trend = UserDailyGoal::where('user_id', $user->id)
            ->whereBetween('goal_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('goal_date, MAX(is_completed) as completed, COALESCE(goal_value, 100) as target, COALESCE(today_progress, 0) as current')
            ->groupBy('goal_date', 'goal_value', 'today_progress')
            ->orderBy('goal_date')
            ->get();

        return [
            'goals_completed'   => $completed,
            'goals_missed'      => $missed,
            'completion_rate'   => $rate,
            'prev_rate'         => $prevRate,
            'trend_pct'         => $rate - $prevRate,
            'chart_data'        => $trend->map(fn($r) => [
                'date'       => $r->goal_date,
                'completed'  => (bool)$r->completed,
                'percentage' => $r->target > 0 ? min(100, round(($r->current / $r->target) * 100)) : 0,
            ])->values()->all(),
        ];
    }

    // ── Achievement Analytics ─────────────────────────────────────────────────

    public function getAchievementAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $all        = UserAchievement::where('user_id', $user->id)->count();
        $rareTotal  = Achievement::where('is_rare', true)->count();
        $rareEarned = UserAchievement::where('user_id', $user->id)
            ->whereHas('achievement', fn($q) => $q->where('is_rare', true))
            ->count();

        $totalAchievements = Achievement::count();
        $completionPct     = $totalAchievements > 0 ? round(($all / $totalAchievements) * 100, 1) : 0;

        $timeline = UserAchievement::where('user_id', $user->id)
            ->whereBetween('unlocked_at', [$from, $to])
            ->with('achievement')
            ->orderBy('unlocked_at', 'desc')
            ->limit(20)
            ->get();

        // Next unlockable achievement
        $nextAchievement = Achievement::whereNotIn('id', UserAchievement::where('user_id', $user->id)->pluck('achievement_id'))
            ->orderBy('sort_order')
            ->first();

        return [
            'total_earned'      => $all,
            'rare_earned'       => $rareEarned,
            'rare_total'        => $rareTotal,
            'completion_pct'    => $completionPct,
            'timeline'          => $timeline->map(fn($ua) => [
                'id'          => $ua->achievement_id,
                'name'        => $ua->achievement?->name ?? '',
                'icon'        => $ua->achievement?->icon ?? '🏆',
                'unlocked_at' => $ua->unlocked_at?->toDateString(),
            ])->values()->all(),
            'next_achievement'  => $nextAchievement ? [
                'id'   => $nextAchievement->id,
                'name' => $nextAchievement->name ?? '',
                'icon' => $nextAchievement->icon ?? '🏆',
            ] : null,
        ];
    }

    // ── Streak Analytics + Heatmap ────────────────────────────────────────────

    public function getStreakAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $streak = DB::table('user_tasbih_streaks')->where('user_id', $user->id)->first();

        // Heatmap: dhikr count per calendar day
        $heatmap = TasbihSessionAggregate::where('user_id', $user->id)
            ->whereBetween('activity_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('activity_date')
            ->get(['activity_date', 'total_dhikr_count'])
            ->mapWithKeys(fn($r) => [$r->activity_date->toDateString() => (int) $r->total_dhikr_count])
            ->all();

        $activeDays   = count(array_filter($heatmap));
        $totalDays    = $from->diffInDays($to) + 1;
        $successRate  = $totalDays > 0 ? round(($activeDays / $totalDays) * 100, 1) : 0.0;

        return [
            'current_streak'    => $streak?->current_streak ?? 0,
            'longest_streak'    => $streak?->longest_streak ?? 0,
            'total_streak_days' => $streak?->total_streak_days ?? 0,
            'success_rate'      => $successRate,
            'heatmap'           => $heatmap,
        ];
    }

    // ── Fingerprint Analytics ─────────────────────────────────────────────────

    public function getFingerprintAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $stats = DB::table('fingerprint_statistics')->where('user_id', $user->id)->first();

        $sessions = DB::table('fingerprint_session_logs')
            ->where('user_id', $user->id)
            ->whereBetween('start_time', [$from, $to])
            ->get();

        $count       = $sessions->count();
        $avgDuration = $count > 0 ? round($sessions->avg('duration_seconds')) : 0;

        $modeCounts = $sessions->groupBy('count_mode')->map->count();
        $favoriteMode = $modeCounts->sortDesc()->keys()->first() ?? 'single_touch';

        $avgRate = $count > 0 ? round($sessions->avg('total_count') / max($sessions->avg('duration_seconds') / 60, 1), 1) : 0.0;

        return [
            'total_counts'          => $stats?->total_counts ?? 0,
            'total_sessions'        => $stats?->total_sessions ?? 0,
            'period_sessions'       => $count,
            'avg_session_duration'  => (int) $avgDuration,
            'favorite_mode'         => $favoriteMode,
            'avg_touch_rate_pm'     => $avgRate,
            'blind_sessions'        => $stats?->total_blind_sessions ?? 0,
            'focus_sessions'        => $stats?->total_focus_sessions ?? 0,
        ];
    }

    // ── Reminder Effectiveness ────────────────────────────────────────────────

    public function getReminderAnalytics(User $user, string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $logs = ReminderLog::where('user_id', $user->id)
            ->whereBetween('sent_at', [$from, $to])
            ->get();

        $sent   = $logs->count();
        $opened = $logs->whereNotNull('opened_at')->count();
        $openRate = $sent > 0 ? round(($opened / $sent) * 100, 1) : 0.0;

        $byType = $logs->groupBy('channel')->map(function ($group) {
            $s = $group->count();
            $o = $group->whereNotNull('opened_at')->count();
            return ['sent' => $s, 'opened' => $o, 'rate' => $s > 0 ? round(($o / $s) * 100, 1) : 0.0];
        });

        return [
            'notifications_sent'   => $sent,
            'notifications_opened' => $opened,
            'open_rate_pct'        => $openRate,
            'by_channel'           => $byType->toArray(),
        ];
    }

    // ── Leaderboard Analytics ─────────────────────────────────────────────────

    public function getLeaderboardAnalytics(User $user): array
    {
        $currentRank = DB::table('users')
            ->where('points_total', '>', $user->points_total ?? 0)
            ->where('status', true)
            ->count() + 1;

        $entries = DB::table('leaderboard_entries')
            ->where('user_id', $user->id)
            ->orderBy('calculated_at', 'desc')
            ->limit(12)
            ->get(['rank', 'score', 'calculated_at']);

        $highestRank = $entries->min('rank') ?? $currentRank;

        return [
            'current_rank'  => $currentRank,
            'highest_rank'  => $highestRank,
            'rank_history'  => $entries->map(fn($e) => [
                'rank' => $e->rank,
                'score' => $e->score,
                'date' => Carbon::parse($e->calculated_at)->toDateString(),
            ])->values()->all(),
        ];
    }

    // ── Milestones ────────────────────────────────────────────────────────────

    public function getMilestones(User $user): array
    {
        $stat = UserStatistic::where('user_id', $user->id)->first();
        if (!$stat) return [];

        $dhikr    = $stat->total_dhikr;
        $sessions = $stat->total_sessions;
        $achieve  = $stat->total_achievements;
        $streak   = $stat->longest_streak;

        $milestones = [
            ['key' => 'dhikr_10k',    'label' => '10,000 Dhikr',       'target' => 10000,  'current' => $dhikr],
            ['key' => 'dhikr_100k',   'label' => '100,000 Dhikr',      'target' => 100000, 'current' => $dhikr],
            ['key' => 'dhikr_1m',     'label' => '1,000,000 Dhikr',    'target' => 1000000,'current' => $dhikr],
            ['key' => 'sessions_100', 'label' => '100 Sessions',        'target' => 100,    'current' => $sessions],
            ['key' => 'achieve_50',   'label' => '50 Achievements',     'target' => 50,     'current' => $achieve],
            ['key' => 'streak_7',     'label' => '7 Day Streak',        'target' => 7,      'current' => $streak],
            ['key' => 'streak_30',    'label' => '30 Day Streak',       'target' => 30,     'current' => $streak],
            ['key' => 'streak_365',   'label' => '365 Day Streak',      'target' => 365,    'current' => $streak],
        ];

        return array_map(function ($m) {
            $m['progress_pct'] = $m['target'] > 0
                ? min(100, round(($m['current'] / $m['target']) * 100, 1))
                : 0;
            $m['completed']    = $m['current'] >= $m['target'];
            return $m;
        }, $milestones);
    }

    // ── Recalculate & Persist ─────────────────────────────────────────────────

    /**
     * Fully recalculate and upsert user_statistics row.
     * Called by RecalculateUserStatistics job.
     */
    public function recalculate(User $user): UserStatistic
    {
        $weights = StatisticsSetting::getProductivityWeights();

        $totalDhikr   = (int) TasbihSession::where('user_id', $user->id)->where('status', 'completed')->sum('total_count');
        $totalSessions= (int) TasbihSession::where('user_id', $user->id)->where('status', 'completed')->count();

        $streakRow    = DB::table('user_tasbih_streaks')->where('user_id', $user->id)->first();
        $currentStreak= $streakRow?->current_streak ?? 0;
        $longestStreak= $streakRow?->longest_streak ?? 0;
        $totalStreakDays = $streakRow?->total_streak_days ?? 0;

        $totalGoals   = UserDailyGoal::where('user_id', $user->id)->count();
        $completedGoals = UserDailyGoal::where('user_id', $user->id)->where('is_completed', true)->count();
        $missedGoals  = max(0, $totalGoals - $completedGoals);
        $goalRate     = $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 2) : 0;

        $totalAchieve = UserAchievement::where('user_id', $user->id)->count();
        $rareAchieve  = UserAchievement::where('user_id', $user->id)
            ->whereHas('achievement', fn($q) => $q->where('is_rare', true))->count();

        $fpStats = DB::table('fingerprint_statistics')->where('user_id', $user->id)->first();
        $fpCounts   = $fpStats?->total_counts ?? 0;
        $fpSessions = $fpStats?->total_sessions ?? 0;

        $currentRank = DB::table('users')
            ->where('points_total', '>', $user->points_total ?? 0)
            ->where('status', true)->count() + 1;

        $reminderLogs = ReminderLog::where('user_id', $user->id);
        $reminderSent = $reminderLogs->count();
        $reminderOpen = ReminderLog::where('user_id', $user->id)->whereNotNull('opened_at')->count();

        // Productivity score (all weights sum to 1.0)
        $maxStreak   = 365;
        $maxSessions = 1000;
        $maxAchieve  = Achievement::count() ?: 1;

        $streakFactor  = min(1.0, $longestStreak / $maxStreak);
        $goalFactor    = $goalRate / 100;
        $sessionFactor = min(1.0, $totalSessions / $maxSessions);
        $achieveFactor = min(1.0, $totalAchieve / $maxAchieve);

        $score = (int) round(
            ($streakFactor  * $weights['streak'])      * 100 +
            ($goalFactor    * $weights['goal'])        * 100 +
            ($sessionFactor * $weights['session'])     * 100 +
            ($achieveFactor * $weights['achievement']) * 100
        );
        $score = max(0, min(100, $score));

        $label = match(true) {
            $score >= 81 => 'master',
            $score >= 61 => 'advanced',
            $score >= 41 => 'dedicated',
            $score >= 21 => 'active',
            default      => 'beginner',
        };

        $stat = UserStatistic::updateOrCreate(
            ['user_id' => $user->id],
            [
                'total_dhikr'               => $totalDhikr,
                'total_sessions'            => $totalSessions,
                'current_streak'            => $currentStreak,
                'longest_streak'            => $longestStreak,
                'total_streak_days'         => $totalStreakDays,
                'total_goals_completed'     => $completedGoals,
                'total_goals_missed'        => $missedGoals,
                'goal_completion_rate'      => $goalRate,
                'total_achievements'        => $totalAchieve,
                'rare_achievements'         => $rareAchieve,
                'fingerprint_total_counts'  => $fpCounts,
                'fingerprint_total_sessions'=> $fpSessions,
                'current_rank'              => $currentRank,
                'reminders_sent'            => $reminderSent,
                'reminders_opened'          => $reminderOpen,
                'productivity_score'        => $score,
                'productivity_label'        => $label,
                'last_calculated_at'        => now(),
            ]
        );

        return $stat;
    }

    // ── Snapshot Management ───────────────────────────────────────────────────

    /**
     * Save today's snapshot and prune old ones per retention policy.
     */
    public function saveSnapshot(User $user, string $type = 'daily'): void
    {
        $dashboard = $this->getDashboard($user);

        StatisticsSnapshot::updateOrCreate(
            [
                'user_id'       => $user->id,
                'snapshot_date' => now()->toDateString(),
                'snapshot_type' => $type,
            ],
            ['data_json' => $dashboard]
        );

        $this->pruneSnapshots($user->id);
    }

    /**
     * Remove snapshots beyond retention policy.
     */
    private function pruneSnapshots(int $userId): void
    {
        $dailyDays  = (int) StatisticsSetting::getValue('snapshot_daily_retention_days',  90);
        $weeklyDays = (int) StatisticsSetting::getValue('snapshot_weekly_retention_days', 730);

        StatisticsSnapshot::where('user_id', $userId)
            ->where('snapshot_type', 'daily')
            ->where('snapshot_date', '<', now()->subDays($dailyDays)->toDateString())
            ->delete();

        StatisticsSnapshot::where('user_id', $userId)
            ->where('snapshot_type', 'weekly')
            ->where('snapshot_date', '<', now()->subDays($weeklyDays)->toDateString())
            ->delete();

        // Monthly snapshots are kept forever — no pruning
    }
}
