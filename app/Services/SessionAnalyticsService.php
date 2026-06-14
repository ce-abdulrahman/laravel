<?php

namespace App\Services;

use App\Models\User;
use App\Models\TasbihSession;
use App\Models\TasbihSessionLog;
use App\Models\TasbihSessionAggregate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionAnalyticsService
{
    /**
     * Compute daily summary metrics and save them to the aggregates table.
     */
    public function aggregateDailyStats(User $user, string $date): TasbihSessionAggregate
    {
        $sessions = TasbihSession::where('user_id', $user->id)
            ->whereDate('session_date', $date)
            ->where('status', 'completed')
            ->get();

        $totalSessions = $sessions->count();
        $totalDhikr = $sessions->sum('total_count');
        $avgDuration = $totalSessions > 0 ? (int) round($sessions->avg('duration_seconds')) : 0;

        return TasbihSessionAggregate::updateOrCreate(
            [
                'user_id' => $user->id,
                'activity_date' => $date,
            ],
            [
                'total_sessions' => $totalSessions,
                'total_dhikr_count' => $totalDhikr,
                'avg_duration_seconds' => $avgDuration,
            ]
        );
    }

    /**
     * Get aggregate analytics metrics for the dashboard.
     */
    public function getUserAnalytics(User $user): array
    {
        $aggregates = TasbihSessionAggregate::where('user_id', $user->id)
            ->orderBy('activity_date', 'asc')
            ->get();

        $totalSessions = TasbihSession::where('user_id', $user->id)->where('status', 'completed')->count();
        $totalDhikr = TasbihSession::where('user_id', $user->id)->where('status', 'completed')->sum('total_count');
        $avgDuration = TasbihSession::where('user_id', $user->id)->where('status', 'completed')->avg('duration_seconds') ?? 0;

        // Longest session
        $longestSession = TasbihSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('duration_seconds')
            ->first();

        // Fastest rate
        $fastestSession = TasbihSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderByDesc('avg_per_minute')
            ->first();

        // Peak productivity hours (based on increment events)
        if (config('database.default') === 'sqlite') {
            $peakHours = TasbihSessionLog::join('tasbih_sessions', 'tasbih_session_logs.session_id', '=', 'tasbih_sessions.id')
                ->where('tasbih_sessions.user_id', $user->id)
                ->where('tasbih_session_logs.event_type', 'increment')
                ->selectRaw('strftime("%H", tasbih_session_logs.timestamp, "+3 hours") as event_hour, SUM(tasbih_session_logs.value) as total_taps')
                ->groupBy('event_hour')
                ->orderBy('total_taps', 'desc')
                ->get()
                ->pluck('total_taps', 'event_hour')
                ->toArray();
        } else {
            $peakHours = TasbihSessionLog::join('tasbih_sessions', 'tasbih_session_logs.session_id', '=', 'tasbih_sessions.id')
                ->where('tasbih_sessions.user_id', $user->id)
                ->where('tasbih_session_logs.event_type', 'increment')
                ->selectRaw('HOUR(CONVERT_TZ(tasbih_session_logs.timestamp, "+00:00", "+03:00")) as event_hour, SUM(tasbih_session_logs.value) as total_taps')
                ->groupBy('event_hour')
                ->orderBy('total_taps', 'desc')
                ->get()
                ->pluck('total_taps', 'event_hour')
                ->toArray();
        }

        // Standardize peak hours to always have 24 hours represented
        $hourlyTaps = [];
        for ($h = 0; $h < 24; $h++) {
            $keyStr = sprintf('%02d', $h);
            $hourlyTaps[$h] = (int) ($peakHours[$h] ?? $peakHours[$keyStr] ?? 0);
        }

        // Dhikr Rate Intensity Distribution
        $rates = [
            'slow' => TasbihSession::where('user_id', $user->id)->where('status', 'completed')->where('avg_per_minute', '<', 20)->count(),
            'medium' => TasbihSession::where('user_id', $user->id)->where('status', 'completed')->where('avg_per_minute', '>=', 20)->where('avg_per_minute', '<=', 50)->count(),
            'fast' => TasbihSession::where('user_id', $user->id)->where('status', 'completed')->where('avg_per_minute', '>', 50)->count(),
        ];

        return [
            'overview' => [
                'total_sessions' => $totalSessions,
                'total_dhikr_count' => $totalDhikr,
                'avg_duration_seconds' => (int) round($avgDuration),
                'longest_session_seconds' => $longestSession ? $longestSession->duration_seconds : 0,
                'longest_session_count' => $longestSession ? $longestSession->total_count : 0,
                'fastest_rate_per_min' => $fastestSession ? (float) $fastestSession->avg_per_minute : 0.0,
            ],
            'daily_trends' => $aggregates->map(function ($agg) {
                return [
                    'date' => $agg->activity_date->toDateString(),
                    'sessions_count' => $agg->total_sessions,
                    'dhikr_count' => $agg->total_dhikr_count,
                    'avg_duration_seconds' => $agg->avg_duration_seconds,
                ];
            }),
            'hourly_peaks' => $hourlyTaps,
            'rates_distribution' => $rates,
        ];
    }
}
