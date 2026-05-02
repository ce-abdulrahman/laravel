<?php
 
namespace App\Http\Controllers;

use App\Models\MemorizationReview;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MemorizationReviewController extends Controller
{ 
    /**
     * Display a listing of the user's memorization reviews.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = MemorizationReview::where('user_id', $user->id)
            ->with(['ayah.surah']);

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // فلتەر بەپێی ئاست
        if ($request->filled('review_level')) {
            $query->where('review_level', $request->review_level);
        }

        // فلتەر بەپێی ئەنجام
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        // فلتەر بەپێی ڕێکەوت
        if ($request->filled('date_from')) {
            $query->whereDate('review_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('review_date', '<=', $request->date_to);
        }

        $reviews = $query->orderBy('review_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();
        $reviewLevels = $this->getReviewLevels();
        $results = $this->getResults();

        $stats = [
            'total_reviews' => MemorizationReview::where('user_id', $user->id)->count(),
            'today_reviews' => MemorizationReview::where('user_id', $user->id)
                ->whereDate('review_date', today())
                ->count(),
            'perfect_reviews' => MemorizationReview::where('user_id', $user->id)
                ->where('result', 'perfect')
                ->count(),
            'needs_work' => MemorizationReview::where('user_id', $user->id)
                ->whereIn('result', ['needs_work', 'forgot'])
                ->count(),
            'avg_retention' => $this->calculateAverageRetention($user->id),
        ];

        // پێداچوونەوەکانی ئەمڕۆ
        $todayReviews = MemorizationReview::where('user_id', $user->id)
            ->whereDate('review_date', today())
            ->with('ayah.surah')
            ->get();

        // پێداچوونەوەکانی سبەینێ (پێشنیارکراو)
        $suggestedReviews = $this->getSuggestedReviews($user->id);

        return view('memorization-reviews.index', compact(
            'reviews', 'surahs', 'reviewLevels', 'results', 'stats', 
            'todayReviews', 'suggestedReviews'
        ));
    }

    /**
     * Show the form for creating a new memorization review.
     */
    public function create(Request $request)
    {
        $surahs = Surah::orderBy('id')->get();
        $reviewLevels = $this->getReviewLevels();
        $results = $this->getResults();

        $selectedAyah = null;
        if ($request->filled('ayah_id')) {
            $selectedAyah = Ayah::with('surah')->find($request->ayah_id);
        }

        return view('memorization-reviews.create', compact(
            'surahs', 'reviewLevels', 'results', 'selectedAyah'
        ));
    }

    /**
     * Store a newly created memorization review in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'review_date' => 'required|date',
            'review_level' => 'nullable|in:new,learning,reviewing,mastered',
            'result' => 'nullable|in:perfect,good,fair,needs_work,forgot',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $user->id;

        $review = MemorizationReview::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('memorization_reviews.messages.created'),
                'review' => $review,
            ]);
        }

        return redirect()
            ->route('memorization-reviews.index')
            ->with('success', __('memorization_reviews.messages.created'));
    }

    /**
     * Store multiple reviews at once.
     */
    public function storeBatch(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'reviews' => 'required|array',
            'reviews.*.ayah_id' => 'required|exists:ayahs,id',
            'reviews.*.review_level' => 'nullable|in:new,learning,reviewing,mastered',
            'reviews.*.result' => 'nullable|in:perfect,good,fair,needs_work,forgot',
            'reviews.*.notes' => 'nullable|string',
        ]);

        $reviewDate = $request->review_date ?? today();

        foreach ($validated['reviews'] as $reviewData) {
            MemorizationReview::create([
                'user_id' => $user->id,
                'ayah_id' => $reviewData['ayah_id'],
                'review_date' => $reviewDate,
                'review_level' => $reviewData['review_level'] ?? null,
                'result' => $reviewData['result'] ?? null,
                'notes' => $reviewData['notes'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('memorization_reviews.messages.created_batch'),
        ]);
    }

    /**
     * Display the specified memorization review.
     */
    public function show(MemorizationReview $memorizationReview)
    {
        $this->authorize('view', $memorizationReview);

        $memorizationReview->load(['ayah.surah']);

        $previousReviews = MemorizationReview::where('user_id', Auth::id())
            ->where('ayah_id', $memorizationReview->ayah_id)
            ->where('id', '!=', $memorizationReview->id)
            ->orderBy('review_date', 'desc')
            ->limit(5)
            ->get();

        return view('memorization-reviews.show', compact('memorizationReview', 'previousReviews'));
    }

    /**
     * Show the form for editing the specified memorization review.
     */
    public function edit(MemorizationReview $memorizationReview)
    {
        $this->authorize('update', $memorizationReview);

        $memorizationReview->load(['ayah.surah']);
        $reviewLevels = $this->getReviewLevels();
        $results = $this->getResults();

        return view('memorization-reviews.edit', compact('memorizationReview', 'reviewLevels', 'results'));
    }

    /**
     * Update the specified memorization review in storage.
     */
    public function update(Request $request, MemorizationReview $memorizationReview)
    {
        $this->authorize('update', $memorizationReview);

        $validated = $request->validate([
            'review_level' => 'nullable|in:new,learning,reviewing,mastered',
            'result' => 'nullable|in:perfect,good,fair,needs_work,forgot',
            'notes' => 'nullable|string',
        ]);

        $memorizationReview->update($validated);

        return redirect()
            ->route('memorization-reviews.show', $memorizationReview)
            ->with('success', __('memorization_reviews.messages.updated'));
    }

    /**
     * Remove the specified memorization review from storage.
     */
    public function destroy(MemorizationReview $memorizationReview)
    {
        $this->authorize('delete', $memorizationReview);

        $memorizationReview->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('memorization_reviews.messages.deleted'),
            ]);
        }

        return redirect()
            ->route('memorization-reviews.index')
            ->with('success', __('memorization_reviews.messages.deleted'));
    }

    /**
     * Get review calendar data.
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        
        $year = $request->year ?? date('Y');
        $month = $request->month ?? date('m');

        $reviews = MemorizationReview::where('user_id', $user->id)
            ->whereYear('review_date', $year)
            ->whereMonth('review_date', $month)
            ->selectRaw('DATE(review_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        return response()->json($reviews);
    }

    /**
     * Get statistics.
     */
    public function stats()
    {
        $user = Auth::user();

        $stats = [
            'by_level' => $this->getStatsByLevel($user->id),
            'by_result' => $this->getStatsByResult($user->id),
            'by_surah' => $this->getStatsBySurah($user->id),
            'monthly_trend' => $this->getMonthlyTrend($user->id),
            'retention_rate' => $this->calculateRetentionRate($user->id),
            'mastered_count' => MemorizationReview::where('user_id', $user->id)
                ->where('review_level', 'mastered')
                ->distinct('ayah_id')
                ->count('ayah_id'),
        ];

        return view('memorization-reviews.stats', compact('stats'));
    }

    /**
     * Calculate average retention.
     */
    private function calculateAverageRetention($userId): int
    {
        $total = MemorizationReview::where('user_id', $userId)->count();
        if ($total === 0) return 0;

        $good = MemorizationReview::where('user_id', $userId)
            ->whereIn('result', ['perfect', 'good'])
            ->count();

        return round(($good / $total) * 100);
    }

    /**
     * Get suggested reviews for tomorrow.
     */
    private function getSuggestedReviews($userId)
    {
        // ئەو ئایەتانەی کە پێویستیان بە پێداچوونەوە هەیە
        // بەپێی سیستەمی دووبارەکردنەوەی بۆشایی (Spaced Repetition)
        
        return MemorizationReview::where('user_id', $userId)
            ->whereIn('result', ['fair', 'needs_work'])
            ->with('ayah.surah')
            ->orderBy('review_date', 'desc')
            ->limit(10)
            ->get()
            ->unique('ayah_id');
    }

    /**
     * Get stats by level.
     */
    private function getStatsByLevel($userId): array
    {
        return MemorizationReview::where('user_id', $userId)
            ->selectRaw('review_level, COUNT(DISTINCT ayah_id) as count')
            ->whereNotNull('review_level')
            ->groupBy('review_level')
            ->get()
            ->pluck('count', 'review_level')
            ->toArray();
    }

    /**
     * Get stats by result.
     */
    private function getStatsByResult($userId): array
    {
        return MemorizationReview::where('user_id', $userId)
            ->selectRaw('result, COUNT(*) as count')
            ->whereNotNull('result')
            ->groupBy('result')
            ->get()
            ->pluck('count', 'result')
            ->toArray();
    }

    /**
     * Get stats by surah.
     */
    private function getStatsBySurah($userId): array
    {
        return MemorizationReview::where('user_id', $userId)
            ->join('ayahs', 'memorization_reviews.ayah_id', '=', 'ayahs.id')
            ->join('surahs', 'ayahs.surah_id', '=', 'surahs.id')
            ->selectRaw('surahs.id, surahs.name_ar, COUNT(DISTINCT ayahs.id) as count')
            ->groupBy('surahs.id', 'surahs.name_ar')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get monthly trend.
     */
    private function getMonthlyTrend($userId): array
    {
        return MemorizationReview::where('user_id', $userId)
            ->where('review_date', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(review_date, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    /**
     * Calculate retention rate.
     */
    private function calculateRetentionRate($userId): array
    {
        $rates = [];
        $levels = ['new', 'learning', 'reviewing', 'mastered'];
        
        foreach ($levels as $level) {
            $total = MemorizationReview::where('user_id', $userId)
                ->where('review_level', $level)
                ->distinct('ayah_id')
                ->count('ayah_id');
            
            $good = MemorizationReview::where('user_id', $userId)
                ->where('review_level', $level)
                ->whereIn('result', ['perfect', 'good'])
                ->distinct('ayah_id')
                ->count('ayah_id');
            
            $rates[$level] = $total > 0 ? round(($good / $total) * 100) : 0;
        }
        
        return $rates;
    }

    /**
     * Get review levels.
     */
    private function getReviewLevels(): array
    {
        return [
            'new' => __('memorization_reviews.levels.new'),
            'learning' => __('memorization_reviews.levels.learning'),
            'reviewing' => __('memorization_reviews.levels.reviewing'),
            'mastered' => __('memorization_reviews.levels.mastered'),
        ];
    }

    /**
     * Get results.
     */
    private function getResults(): array
    {
        return [
            'perfect' => __('memorization_reviews.results.perfect'),
            'good' => __('memorization_reviews.results.good'),
            'fair' => __('memorization_reviews.results.fair'),
            'needs_work' => __('memorization_reviews.results.needs_work'),
            'forgot' => __('memorization_reviews.results.forgot'),
        ];
    }
}