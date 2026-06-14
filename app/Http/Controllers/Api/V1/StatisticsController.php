<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateUserInsightsJob;
use App\Jobs\RecalculateUserStatistics;
use App\Services\StatisticsService;
use App\Services\InsightEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/statistics/*
 *
 * All endpoints require auth:sanctum.
 * Period query param: today | 7d | 30d | 90d | 12m | all  (default: 30d)
 */
class StatisticsController extends Controller
{
    public function __construct(
        private readonly StatisticsService $service,
        private readonly InsightEngine     $insightEngine,
    ) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data'   => $this->service->getDashboard($user),
        ]);
    }

    // ── Dhikr Analytics ───────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/dhikr?period=30d
     */
    public function dhikr(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getDhikrAnalytics($request->user(), $period),
        ]);
    }

    // ── Session Analytics ─────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/sessions?period=30d
     */
    public function sessions(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getSessionAnalytics($request->user(), $period),
        ]);
    }

    // ── Goal Analytics ────────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/goals?period=30d
     */
    public function goals(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getGoalAnalytics($request->user(), $period),
        ]);
    }

    // ── Achievement Analytics ─────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/achievements?period=all
     */
    public function achievements(Request $request): JsonResponse
    {
        $period = $request->get('period', 'all');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getAchievementAnalytics($request->user(), $period),
        ]);
    }

    // ── Streak Analytics ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/streaks?period=12m
     */
    public function streaks(Request $request): JsonResponse
    {
        $period = $request->get('period', '12m');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getStreakAnalytics($request->user(), $period),
        ]);
    }

    // ── Leaderboard Analytics ─────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->service->getLeaderboardAnalytics($request->user()),
        ]);
    }

    // ── Fingerprint Analytics ─────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/fingerprint?period=30d
     */
    public function fingerprint(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getFingerprintAnalytics($request->user(), $period),
        ]);
    }

    // ── Reminder Analytics ────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/reminders?period=30d
     */
    public function reminders(Request $request): JsonResponse
    {
        $period = $request->get('period', '30d');
        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data'   => $this->service->getReminderAnalytics($request->user(), $period),
        ]);
    }

    // ── Insights ──────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/insights
     *
     * Returns cached insights; dispatches regeneration job if stale.
     */
    public function insights(Request $request): JsonResponse
    {
        $user     = $request->user();
        $insights = $this->insightEngine->getForUser($user);

        // If no fresh insights exist, dispatch background job
        if (empty($insights)) {
            GenerateUserInsightsJob::dispatch($user->id)->onQueue('statistics');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $insights,
        ]);
    }

    // ── Milestones ────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/statistics/milestones
     */
    public function milestones(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->service->getMilestones($request->user()),
        ]);
    }

    // ── Export (Phase 2 stub) ─────────────────────────────────────────────────

    /**
     * POST /api/v1/statistics/export
     */
    public function export(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => 'coming_soon',
            'message' => __('statistics.export_coming_soon'),
        ], 501);
    }

    // ── Refresh (force recalculate) ───────────────────────────────────────────

    /**
     * POST /api/v1/statistics/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        RecalculateUserStatistics::dispatch($user->id)->onQueue('statistics');
        GenerateUserInsightsJob::dispatch($user->id)->onQueue('statistics');

        return response()->json([
            'status'  => 'success',
            'message' => __('statistics.refresh_queued'),
        ]);
    }
}
