<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TasbihSession;
use App\Services\SessionLifecycleService;
use App\Services\SessionAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TasbihSessionController extends Controller
{
    public function __construct(
        private readonly SessionLifecycleService $lifecycleService,
        private readonly SessionAnalyticsService $analyticsService
    ) {}

    /**
     * POST /api/v1/sessions/start
     */
    public function start(Request $request)
    {
        $request->validate([
            'dhikr_id' => 'nullable|integer|exists:tasbihs,id',
            'custom_dhikr_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $dhikrId = $request->input('dhikr_id');
        $customName = $request->input('custom_dhikr_name');

        try {
            $session = $this->lifecycleService->startSession($user, $dhikrId, $customName);
            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Tasbih session started successfully.',
                'data' => $session
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Failed to start session: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/v1/sessions/increment
     */
    public function increment(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:tasbih_sessions,id',
            'increments' => 'required|array',
            'increments.*.event_uuid' => 'required|string',
            'increments.*.value' => 'required|integer|min:1',
            'increments.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $user = $request->user();
        $sessionId = (int) $request->input('session_id');
        $increments = $request->input('increments');

        // Security / Anti-Cheat Check: Validate tap speed intensity
        if (!$this->validateTapRate($increments)) {
            Log::warning("Cheat detection: Unrealistic tap rate flagged for User #{$user->id} on Session #{$sessionId}.");
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Unrealistic tap speed detected. Increment rejected.'
            ], 400);
        }

        try {
            $session = $this->lifecycleService->syncBatchIncrements($user, $sessionId, $increments);
            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => [
                    'session_id' => $session->id,
                    'total_count' => $session->total_count,
                    'status' => $session->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/v1/sessions/pause
     */
    public function pause(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:tasbih_sessions,id',
            'event_uuid' => 'required|string',
            'timestamp' => 'nullable|date',
        ]);

        $user = $request->user();
        $sessionId = (int) $request->input('session_id');
        $uuid = $request->input('event_uuid');
        $time = $request->input('timestamp');

        try {
            $session = $this->lifecycleService->pauseSession($user, $sessionId, $uuid, $time);
            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => $session
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/v1/sessions/resume
     */
    public function resume(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:tasbih_sessions,id',
            'event_uuid' => 'required|string',
            'timestamp' => 'nullable|date',
        ]);

        $user = $request->user();
        $sessionId = (int) $request->input('session_id');
        $uuid = $request->input('event_uuid');
        $time = $request->input('timestamp');

        try {
            $session = $this->lifecycleService->resumeSession($user, $sessionId, $uuid, $time);
            return response()->json([
                'status' => 'success',
                'success' => true,
                'data' => $session
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/v1/sessions/end
     */
    public function end(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:tasbih_sessions,id',
            'event_uuid' => 'required|string',
            'final_count' => 'nullable|integer|min:0',
            'timestamp' => 'nullable|date',
        ]);

        $user = $request->user();
        $sessionId = (int) $request->input('session_id');
        $uuid = $request->input('event_uuid');
        $finalCount = $request->input('final_count');
        $time = $request->input('timestamp');

        try {
            $session = $this->lifecycleService->endSession($user, $sessionId, $uuid, $finalCount, $time);
            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => 'Tasbih session completed successfully.',
                'data' => $session
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * GET /api/v1/sessions/active
     */
    public function active(Request $request)
    {
        $user = $request->user();
        $active = TasbihSession::where('user_id', $user->id)
            ->whereIn('status', ['active', 'paused'])
            ->first();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $active
        ]);
    }

    /**
     * GET /api/v1/sessions/history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $sessions = TasbihSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['dhikr'])
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * GET /api/v1/sessions/analytics
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Helper: Validates that increments are humanly possible.
     */
    private function validateTapRate(array $increments): bool
    {
        if (empty($increments)) {
            return true;
        }

        $totalTaps = 0;
        $timestamps = [];

        foreach ($increments as $inc) {
            $val = (int) ($inc['value'] ?? 1);
            if ($val > 100) { // Limit increment to maximum 100 taps in single log
                return false;
            }
            $totalTaps += $val;
            $timestamps[] = strtotime($inc['timestamp']);
        }

        if (count($timestamps) >= 2) {
            $minTime = min($timestamps);
            $maxTime = max($timestamps);
            $duration = max(1, $maxTime - $minTime);

            $tapsPerSec = $totalTaps / $duration;
            if ($tapsPerSec > 15) { // Reject if user averages > 15 taps per second
                return false;
            }
        }

        return true;
    }
}
