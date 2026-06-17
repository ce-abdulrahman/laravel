<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAyahProgress;
use App\Models\MemorizationSession;
use App\Services\SpacedRepetitionService;
use App\Services\CompletionForecastService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserAyahProgressController extends Controller
{
    public function index(Request $request)
    {
        $progress = UserAyahProgress::where('user_id', $request->user()->id)
                                   ->with(['ayah.surah'])
                                   ->when($request->memorize_status, function ($q) use ($request) {
                                       return $q->where('memorize_status', $request->memorize_status);
                                   })
                                   ->orderBy('last_reviewed_at', 'desc')
                                   ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ]);
    }

    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;
        $version = app(SpacedRepetitionService::class)->getCacheVersion($userId);
        $cacheKey = "user_{$userId}_v{$version}_memorization_dashboard";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            return $this->computeDashboardData($userId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function fullStatistics(Request $request)
    {
        $userId = $request->user()->id;
        $version = app(SpacedRepetitionService::class)->getCacheVersion($userId);
        $cacheKey = "user_{$userId}_v{$version}_memorization_stats";

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            return $this->computeStatisticsData($userId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function detailedProgress(Request $request)
    {
        $userId = $request->user()->id;

        $recentMemorized = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->with(['ayah.surah'])
            ->orderBy('last_memorized_at', 'desc')
            ->take(20)
            ->get();

        $recentReviews = DB::table('memorization_reviews')
            ->where('user_id', $userId)
            ->join('ayahs', 'memorization_reviews.ayah_id', '=', 'ayahs.id')
            ->join('surahs', 'ayahs.surah_id', '=', 'surahs.id')
            ->select(
                'memorization_reviews.*',
                'ayahs.ayah_number',
                'surahs.number as surah_number'
            )
            ->orderBy('memorization_reviews.review_date', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'recent_memorized' => $recentMemorized,
                'recent_reviews' => $recentReviews,
            ]
        ]);
    }

    public function forecast(Request $request)
    {
        $userId = $request->user()->id;
        $forecast = app(CompletionForecastService::class)->getForecast($userId);

        return response()->json([
            'status' => 'success',
            'data' => $forecast
        ]);
    }

    public function storeSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_type' => 'required|in:memorization,review,quiz',
            'status' => 'required|in:completed,interrupted,abandoned',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'duration_seconds' => 'required|integer|min:0',
            'ayahs_reviewed' => 'required|integer|min:0',
            'ayahs_memorized' => 'required|integer|min:0',
            'score' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = $request->status;
        $endedAt = $request->ended_at;
        $completedAt = $request->completed_at;

        if ($status !== 'completed') {
            $endedAt = null;
            $completedAt = null;
        }

        $session = MemorizationSession::create([
            'user_id' => $request->user()->id,
            'session_type' => $request->session_type,
            'status' => $status,
            'started_at' => $request->started_at,
            'ended_at' => $endedAt,
            'completed_at' => $completedAt,
            'duration_seconds' => $request->duration_seconds,
            'ayahs_reviewed' => $request->ayahs_reviewed,
            'ayahs_memorized' => $request->ayahs_memorized,
            'score' => $request->score,
        ]);

        app(SpacedRepetitionService::class)->invalidateCache($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Session logged successfully',
            'data' => $session
        ], 201);
    }

    public function warmCache(int $userId): void
    {
        $version = app(SpacedRepetitionService::class)->getCacheVersion($userId);
        $cacheKeyDashboard = "user_{$userId}_v{$version}_memorization_dashboard";
        $cacheKeyStats = "user_{$userId}_v{$version}_memorization_stats";

        $dashboardData = $this->computeDashboardData($userId);
        Cache::put($cacheKeyDashboard, $dashboardData, now()->addMinutes(5));

        $statsData = $this->computeStatisticsData($userId);
        Cache::put($cacheKeyStats, $statsData, now()->addMinutes(10));
    }

    private function computeDashboardData(int $userId): array
    {
        $totalMemorized = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->count();

        $totalLearning = UserAyahProgress::where('user_id', $userId)
            ->where('memorize_status', 'learning')
            ->count();

        $totalReviews = DB::table('memorization_reviews')
            ->where('user_id', $userId)
            ->count();

        $todayReviews = DB::table('memorization_reviews')
            ->where('user_id', $userId)
            ->whereDate('review_date', Carbon::today())
            ->count();

        $streak = $this->calculateStreak($userId);

        $today = Carbon::today()->toDateString();
        $dueReviewsCount = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->whereDate('next_review_date', '<=', $today)
            ->count();

        $weakAyahsCount = UserAyahProgress::where('user_id', $userId)
            ->where('strength_score', '<', 60)
            ->count();

        $forecast = app(CompletionForecastService::class)->getForecast($userId);

        return [
            'total_memorized' => $totalMemorized,
            'total_learning' => $totalLearning,
            'total_reviews' => $totalReviews,
            'today_reviews' => $todayReviews,
            'streak_days' => $streak,
            'due_reviews_count' => $dueReviewsCount,
            'weak_ayahs_count' => $weakAyahsCount,
            'estimated_completion_date' => $forecast['estimated_completion_date'],
            'remaining_days' => $forecast['remaining_days'],
            'remaining_ayahs' => $forecast['remaining_ayahs'],
            'daily_target' => $forecast['daily_target'],
        ];
    }

    private function computeStatisticsData(int $userId): array
    {
        $totalQuranAyahs = 6236;
        $totalMemorized = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->count();

        $overallCompletionPercentage = round(($totalMemorized / $totalQuranAyahs) * 100, 2);

        $userMemorizedBySurah = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->join('ayahs', 'user_ayah_progress.ayah_id', '=', 'ayahs.id')
            ->select('ayahs.surah_id', DB::raw('count(*) as count'))
            ->groupBy('ayahs.surah_id')
            ->pluck('count', 'ayahs.surah_id')
            ->toArray();

        $surahs = \App\Models\Surah::select('id', 'number', 'ayah_count')->get();
        $surahProgress = [];
        foreach ($surahs as $surah) {
            $memorized = $userMemorizedBySurah[$surah->id] ?? 0;
            $surahProgress[] = [
                'surah_id' => $surah->id,
                'surah_number' => $surah->number,
                'name' => $surah->name,
                'ayah_count' => $surah->ayah_count,
                'memorized_count' => $memorized,
                'percentage' => $surah->ayah_count > 0 ? round(($memorized / $surah->ayah_count) * 100, 2) : 0,
            ];
        }

        $totalAyahsPerJuz = Cache::remember('quran_total_ayahs_per_juz', now()->addDays(30), function () {
            return DB::table('ayahs')
                ->select('juz_number', DB::raw('count(*) as count'))
                ->groupBy('juz_number')
                ->pluck('count', 'juz_number')
                ->toArray();
        });

        $userMemorizedByJuz = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->join('ayahs', 'user_ayah_progress.ayah_id', '=', 'ayahs.id')
            ->select('ayahs.juz_number', DB::raw('count(*) as count'))
            ->groupBy('ayahs.juz_number')
            ->pluck('count', 'ayahs.juz_number')
            ->toArray();

        $juzProgress = [];
        for ($juz = 1; $juz <= 30; $juz++) {
            $total = $totalAyahsPerJuz[$juz] ?? 0;
            $memorized = $userMemorizedByJuz[$juz] ?? 0;
            $juzProgress[] = [
                'juz_number' => $juz,
                'total_ayahs' => $total,
                'memorized_count' => $memorized,
                'percentage' => $total > 0 ? round(($memorized / $total) * 100, 2) : 0,
            ];
        }

        return [
            'overall_completion_percentage' => $overallCompletionPercentage,
            'total_memorized' => $totalMemorized,
            'surahs_progress' => $surahProgress,
            'juz_progress' => $juzProgress,
        ];
    }

    private function calculateStreak($userId)
    {
        $streak = 0;
        $date = Carbon::today();

        while (true) {
            $hasActivity = DB::table('memorization_reviews')
                             ->where('user_id', $userId)
                             ->whereDate('review_date', $date)
                             ->exists();

            if (!$hasActivity) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'memorize_status' => 'required|in:not_started,learning,memorized,mastered',
            'notes' => 'nullable|string',
        ]);

        $progress = UserAyahProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'ayah_id' => $request->ayah_id,
            ],
            [
                'memorize_status' => $validated['memorize_status'],
                'last_memorized_at' => in_array($validated['memorize_status'], ['memorized', 'mastered']) ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        app(SpacedRepetitionService::class)->invalidateCache($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Progress saved successfully',
            'data' => $progress->load('ayah')
        ]);
    }

    public function update(Request $request, $id)
    {
        $progress = UserAyahProgress::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'memorize_status' => 'sometimes|in:not_started,learning,memorized,mastered',
            'notes' => 'nullable|string',
        ]);

        $progress->update($validated);

        app(SpacedRepetitionService::class)->invalidateCache($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Progress updated successfully',
            'data' => $progress
        ]);
    }
}

