<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FingerprintSetting;
use App\Models\FingerprintStatistic;
use App\Models\FingerprintSessionLog;
use App\Models\TasbihSession;
use App\Models\UserDailyGoal;
use App\Services\DailyGoalService;
use App\Services\StreakService;
use App\Services\AchievementEngine;
use App\Services\ProfileStatisticsService;
use App\Services\LeaderboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FingerprintApiController extends Controller
{
    public function __construct(
        private readonly DailyGoalService $dailyGoalService,
        private readonly StreakService $streakService,
        private readonly AchievementEngine $achievementEngine,
        private readonly ProfileStatisticsService $profileStatsService,
        private readonly LeaderboardCacheService $leaderboardCacheService
    ) {}

    /**
     * GET /api/v1/fingerprint/settings
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();

        $settings = FingerprintSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'count_mode' => 'single_touch',
                'hold_interval_seconds' => 1,
                'haptic_profile' => 'normal',
                'custom_haptic_vibration_ms' => 50,
                'audio_profile' => 'soft_click',
                'blind_mode' => false,
                'focus_mode' => false,
            ]
        );

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * POST /api/v1/fingerprint/settings
     */
    public function saveSettings(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'count_mode' => 'required|string|in:single_touch,hold_to_count,continuous',
            'hold_interval_seconds' => 'required|integer|in:1,2,3',
            'haptic_profile' => 'required|string|in:light,normal,strong,custom,disabled',
            'custom_haptic_vibration_ms' => 'required|integer|min:1|max:1000',
            'audio_profile' => 'required|string|in:soft_click,tasbih_bead,water_drop,silent',
            'blind_mode' => 'required|boolean',
            'focus_mode' => 'required|boolean',
        ]);

        $settings = FingerprintSetting::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => 'Fingerprint settings updated successfully.',
            'data' => $settings
        ]);
    }

    /**
     * GET /api/v1/fingerprint/statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        $statistics = FingerprintStatistic::firstOrCreate(
            ['user_id' => $user->id],
            [
                'total_counts' => 0,
                'total_sessions' => 0,
                'total_blind_sessions' => 0,
                'total_focus_sessions' => 0,
                'avg_touch_rate' => 0.00,
                'favorite_mode' => 'single_touch',
                'last_used_at' => null,
            ]
        );

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * POST /api/v1/fingerprint/session
     */
    public function syncSession(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'dhikr_id' => 'nullable|integer|exists:tasbihs,id',
            'custom_dhikr_name' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'duration_seconds' => 'required|integer|min:1',
            'total_count' => 'required|integer|min:0',
            'is_blind' => 'required|boolean',
            'is_focus' => 'required|boolean',
            'count_mode' => 'required|string|in:single_touch,hold_to_count,continuous',
        ]);

        $totalCount = (int) $validated['total_count'];
        $duration = (int) $validated['duration_seconds'];

        // Security / Cheat Detection: Validate touch rate
        if ($duration > 0) {
            $touchesPerSecond = $totalCount / $duration;
            if ($touchesPerSecond > 12.0) {
                Log::warning("Cheat detection: Fingerprint touch rate of {$touchesPerSecond} touches/sec exceeded threshold for User #{$user->id}. Session rejected.");
                return response()->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Abnormal counting rate detected. Session rejected.'
                ], 400);
            }
        }

        try {
            $newlyUnlocked = DB::transaction(function () use ($user, $validated, $totalCount, $duration) {
                // 1. Create standard TasbihSession
                $sessionDate = Carbon::parse($validated['start_time'])->tz('Asia/Baghdad')->toDateString();
                $avgPerMinute = $duration > 0 ? round(($totalCount / ($duration / 60)), 2) : 0.00;

                $tasbihSession = TasbihSession::create([
                    'user_id' => $user->id,
                    'dhikr_id' => $validated['dhikr_id'] ?? null,
                    'custom_dhikr_name' => $validated['custom_dhikr_name'] ?? null,
                    'start_time' => Carbon::parse($validated['start_time']),
                    'end_time' => Carbon::parse($validated['end_time']),
                    'duration_seconds' => $duration,
                    'total_count' => $totalCount,
                    'avg_per_minute' => $avgPerMinute,
                    'session_date' => $sessionDate,
                    'status' => 'completed',
                ]);

                // 2. Create FingerprintSessionLog
                FingerprintSessionLog::create([
                    'session_id' => $tasbihSession->id,
                    'touch_count' => $totalCount,
                    'duration_seconds' => $duration,
                    'touch_rate' => $avgPerMinute,
                    'is_blind' => $validated['is_blind'],
                    'is_focus' => $validated['is_focus'],
                ]);

                // 3. Update FingerprintStatistic
                $stats = FingerprintStatistic::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'total_counts' => 0,
                        'total_sessions' => 0,
                        'total_blind_sessions' => 0,
                        'total_focus_sessions' => 0,
                        'avg_touch_rate' => 0.00,
                        'favorite_mode' => 'single_touch',
                    ]
                );

                $stats->total_counts += $totalCount;
                $stats->total_sessions += 1;
                if ($validated['is_blind']) {
                    $stats->total_blind_sessions += 1;
                }
                if ($validated['is_focus']) {
                    $stats->total_focus_sessions += 1;
                }
                $stats->last_used_at = Carbon::now('UTC');

                // Recalculate avg_touch_rate over all user fingerprint sessions
                $allLogs = DB::table('fingerprint_session_logs')
                    ->join('tasbih_sessions', 'fingerprint_session_logs.session_id', '=', 'tasbih_sessions.id')
                    ->where('tasbih_sessions.user_id', $user->id)
                    ->selectRaw('SUM(touch_count) as total_touch, SUM(duration_seconds) as total_duration')
                    ->first();

                $totalTouchSum = (int) ($allLogs->total_touch ?? 0) + $totalCount;
                $totalDurationSum = (int) ($allLogs->total_duration ?? 0) + $duration;
                $stats->avg_touch_rate = $totalDurationSum > 0 ? round(($totalTouchSum / ($totalDurationSum / 60)), 2) : 0.00;

                // Determine favorite mode (most common count_mode in synced sessions)
                // Since count_mode isn't stored in logs, update favorite_mode if settings changed or just keep current
                $stats->favorite_mode = $validated['count_mode'];
                $stats->save();

                // 4. Update Daily Goals
                $this->dailyGoalService->updateProgress($user, $totalCount);

                // 5. Update Streak
                $this->streakService->updateStreak($user);

                // 6. Evaluate Achievements
                $totalDhikr = (int) UserDailyGoal::where('user_id', $user->id)->sum('today_progress');

                $unlocked = $this->achievementEngine->evaluate($user, [
                    'session_dhikr_count' => $totalCount,
                    'total_dhikr_count' => $totalDhikr,
                    'fingerprint_total_counts' => $stats->total_counts,
                    'fingerprint_total_sessions' => $stats->total_sessions,
                    'fingerprint_blind_sessions' => $stats->total_blind_sessions,
                    'fingerprint_focus_sessions' => $stats->total_focus_sessions,
                ]);

                // 7. Invalidate caches
                $this->profileStatsService->invalidateCache($user->id);
                $this->leaderboardCacheService->clearCache();

                return [
                    'session' => $tasbihSession,
                    'newly_unlocked' => $unlocked
                ];
            });

            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Fingerprint session synced successfully.',
                'data' => $newlyUnlocked['session'],
                'newly_unlocked' => $newlyUnlocked['newly_unlocked']
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to sync fingerprint session: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Failed to sync session: ' . $e->getMessage()
            ], 500);
        }
    }
}
