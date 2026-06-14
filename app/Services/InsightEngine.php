<?php

namespace App\Services;

use App\Models\User;
use App\Models\InsightLog;
use App\Models\StatisticsSetting;
use App\Models\TasbihSessionAggregate;
use App\Models\UserDailyGoal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsightEngine
{
    /**
     * Generate and persist fresh insights for a user.
     * Called from GenerateUserInsightsJob (queued).
     */
    public function generate(User $user): void
    {
        $expireHours = (int) StatisticsSetting::getValue('insights_expire_hours', 24);

        // Clear stale insights
        InsightLog::where('user_id', $user->id)
            ->where('expires_at', '<', now())
            ->delete();

        $insights = array_merge(
            $this->peakDayInsights($user),
            $this->peakHourInsights($user),
            $this->trendInsights($user),
            $this->streakInsights($user),
            $this->goalInsights($user),
        );

        foreach ($insights as $insight) {
            InsightLog::create([
                'user_id'      => $user->id,
                'insight_type' => $insight['type'],
                'insight_data' => $insight['data'],
                'icon'         => $insight['icon'] ?? '💡',
                'generated_at' => now(),
                'expires_at'   => now()->addHours($expireHours),
            ]);
        }
    }

    /**
     * Get fresh or cached insights for API response.
     */
    public function getForUser(User $user): array
    {
        $insights = InsightLog::where('user_id', $user->id)
            ->fresh()
            ->orderBy('generated_at', 'desc')
            ->limit(10)
            ->get();

        return $insights->map(fn($i) => [
            'type'         => $i->insight_type,
            'data'         => $i->insight_data,
            'icon'         => $i->icon,
            'generated_at' => $i->generated_at->toIso8601String(),
        ])->values()->all();
    }

    // ── Private Insight Generators ────────────────────────────────────────────

    private function peakDayInsights(User $user): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dowExpr = $isSqlite ? "CAST(strftime('%w', activity_date) AS INTEGER) + 1" : "DAYOFWEEK(activity_date)";

        $peakRow = TasbihSessionAggregate::where('user_id', $user->id)
            ->selectRaw("{$dowExpr} as dow, SUM(total_dhikr_count) as total")
            ->groupBy('dow')
            ->orderByDesc('total')
            ->first();

        if (!$peakRow) return [];

        $days = [1=>'Sunday', 2=>'Monday', 3=>'Tuesday', 4=>'Wednesday', 5=>'Thursday', 6=>'Friday', 7=>'Saturday'];
        $dayName = $days[$peakRow->dow] ?? 'Friday';

        return [[
            'type' => 'peak_day',
            'icon' => '📅',
            'data' => [
                'key'    => 'insight.peak_day',
                'params' => ['day' => $dayName],
                'fallback' => "Your activity is highest on {$dayName}.",
            ],
        ]];
    }

    private function peakHourInsights(User $user): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $hourExpr = $isSqlite ? "CAST(strftime('%H', tasbih_session_logs.timestamp) AS INTEGER)" : "HOUR(tasbih_session_logs.timestamp)";

        $peakRow = DB::table('tasbih_session_logs')
            ->join('tasbih_sessions', 'tasbih_session_logs.session_id', '=', 'tasbih_sessions.id')
            ->where('tasbih_sessions.user_id', $user->id)
            ->where('tasbih_session_logs.event_type', 'increment')
            ->selectRaw("{$hourExpr} as h, SUM(tasbih_session_logs.value) as t")
            ->groupBy('h')
            ->orderByDesc('t')
            ->first();

        if (!$peakRow) return [];

        $h    = (int) $peakRow->h;
        $label = match(true) {
            $h >= 4  && $h < 8  => 'Fajr time',
            $h >= 12 && $h < 14 => 'Dhuhr time',
            $h >= 15 && $h < 17 => 'Asr time',
            $h >= 18 && $h < 20 => 'Maghrib time',
            $h >= 20 && $h < 23 => 'Isha time',
            default => sprintf('%02d:00', $h),
        };

        return [[
            'type' => 'peak_hour',
            'icon' => '🕌',
            'data' => [
                'key'      => 'insight.peak_hour',
                'params'   => ['hour' => $label],
                'fallback' => "You are most productive during {$label}.",
            ],
        ]];
    }

    private function trendInsights(User $user): array
    {
        $thisMonth  = TasbihSessionAggregate::where('user_id', $user->id)
            ->whereMonth('activity_date', now()->month)
            ->whereYear('activity_date', now()->year)
            ->sum('total_dhikr_count');

        $lastMonth  = TasbihSessionAggregate::where('user_id', $user->id)
            ->whereMonth('activity_date', now()->subMonth()->month)
            ->whereYear('activity_date', now()->subMonth()->year)
            ->sum('total_dhikr_count');

        if ($lastMonth <= 0) return [];

        $pct = round((($thisMonth - $lastMonth) / $lastMonth) * 100, 0);
        if (abs($pct) < 5) return [];   // skip noise

        $dir = $pct > 0 ? 'more' : 'less';

        return [[
            'type' => 'monthly_trend',
            'icon' => $pct > 0 ? '📈' : '📉',
            'data' => [
                'key'      => 'insight.monthly_trend',
                'params'   => ['pct' => abs($pct), 'direction' => $dir],
                'fallback' => "You completed " . abs($pct) . "% {$dir} dhikr this month vs last month.",
            ],
        ]];
    }

    private function streakInsights(User $user): array
    {
        $streak = DB::table('user_tasbih_streaks')->where('user_id', $user->id)->first();
        if (!$streak) return [];

        $insights = [];

        if ($streak->current_streak > 0 && $streak->current_streak >= $streak->longest_streak) {
            $insights[] = [
                'type' => 'streak_record',
                'icon' => '🔥',
                'data' => [
                    'key'      => 'insight.streak_record',
                    'params'   => ['days' => $streak->current_streak],
                    'fallback' => "Your current streak of {$streak->current_streak} days is your all-time best!",
                ],
            ];
        }

        return $insights;
    }

    private function goalInsights(User $user): array
    {
        $today = UserDailyGoal::where('user_id', $user->id)
            ->whereDate('goal_date', now()->toDateString())
            ->first();

        if (!$today || $today->is_completed) return [];

        $remaining = max(0, ($today->target_count ?? 100) - ($today->current_count ?? 0));

        return [[
            'type' => 'goal_reminder',
            'icon' => '🎯',
            'data' => [
                'key'      => 'insight.goal_remaining',
                'params'   => ['remaining' => $remaining],
                'fallback' => "You need {$remaining} more dhikr to complete today's goal.",
            ],
        ]];
    }
}
