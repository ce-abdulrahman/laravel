<?php

namespace App\Http\Controllers;

use App\Models\MemorizationPlan;
use App\Models\MemorizationPlanItem;
use App\Models\Surah;
use App\Models\Ayah;
use App\Models\MemorizationReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MemorizationPlanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('admin', except: ['index', 'show', 'updateItemStatus']),
        ];
    }

    /**
     * Display a listing of the memorization plans.
     * هەموو بەکارهێنەرێک دەتوانێت پلانەکان ببینێت
     */
    public function index(Request $request)
    {
        // ئەدمین هەموو پلانەکان دەبینێت
        // بەکارهێنەری ئاسایی تەنها پلانە چالاکەکان دەبینێت
        $query = MemorizationPlan::withCount('items')
            ->with(['items' => function ($q) {
                $q->where('status', 'completed');
            }]);

        if (auth()->user()->role !== 'admin') {
            $query->where('status', 'active');
        }

        // فلتەر بەپێی دۆخ (تەنها بۆ ئەدمین)
        if ($request->filled('status') && auth()->user()->role === 'admin') {
            $query->where('status', $request->status);
        }

        // فلتەر بەپێی جۆر
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        $plans = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12)
            ->withQueryString();

        $stats = $this->getStats();
        $planTypes = $this->getPlanTypes();
        $statuses = $this->getStatuses();

        return view('memorization-plans.index', compact('plans', 'stats', 'planTypes', 'statuses'));
    }

    /**
     * Show the form for creating a new memorization plan.
     */
    public function create()
    {
        $surahs = Surah::orderBy('id')->get();
        $planTypes = $this->getPlanTypes();
        $dailyTargetTypes = $this->getDailyTargetTypes();

        return view('memorization-plans.create', compact('surahs', 'planTypes', 'dailyTargetTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'plan_type' => 'required|in:juz,surah,custom',
            'start_date' => 'required|date',
            'target_end_date' => 'nullable|date|after_or_equal:start_date',
            'daily_target_type' => 'required|in:ayahs,pages,juz,hizb',
            'daily_target_value' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'active';

        $plan = MemorizationPlan::create($validated);

        // دروستکردنی بڕگەکانی پلان
        if ($request->plan_type === 'surah' && $request->filled('surah_id')) {
            $this->generateSurahPlan($plan, $request->surah_id);
        } elseif ($request->plan_type === 'juz' && $request->filled('juz_number')) {
            $this->generateJuzPlan($plan, $request->juz_number);
        } elseif ($request->plan_type === 'custom' && $request->filled('ayahs')) {
            $this->generateCustomPlan($plan, $request->ayahs);
        }

        return redirect()
            ->route('memorization-plans.show', $plan)
            ->with('success', __('memorization_plans.messages.created'));
    }

    /**
     * Generate plan for a specific surah.
     */
    private function generateSurahPlan($plan, $surahId)
    {
        $ayahs = Ayah::where('surah_id', $surahId)
            ->orderBy('ayah_number')
            ->get();

        $dailyTarget = $plan->daily_target_value;
        $chunks = $ayahs->chunk($dailyTarget);
        $dayNumber = 1;
        $startDate = Carbon::parse($plan->start_date);

        foreach ($chunks as $chunk) {
            MemorizationPlanItem::create([
                'memorization_plan_id' => $plan->id,
                'surah_id' => $surahId,
                'from_ayah_id' => $chunk->first()->id,
                'to_ayah_id' => $chunk->last()->id,
                'day_number' => $dayNumber,
                'target_date' => $startDate->copy()->addDays($dayNumber - 1),
                'status' => 'pending',
            ]);
            $dayNumber++;
        }
    }

    /**
     * Generate plan for a specific juz.
     */
    private function generateJuzPlan($plan, $juzNumber)
    {
        $ayahs = Ayah::where('juz_number', $juzNumber)
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $dailyTarget = $plan->daily_target_value;
        $chunks = $ayahs->chunk($dailyTarget);
        $dayNumber = 1;
        $startDate = Carbon::parse($plan->start_date);

        foreach ($chunks as $chunk) {
            MemorizationPlanItem::create([
                'memorization_plan_id' => $plan->id,
                'surah_id' => $chunk->first()->surah_id,
                'from_ayah_id' => $chunk->first()->id,
                'to_ayah_id' => $chunk->last()->id,
                'day_number' => $dayNumber,
                'target_date' => $startDate->copy()->addDays($dayNumber - 1),
                'status' => 'pending',
            ]);
            $dayNumber++;
        }
    }

    /**
     * Generate custom plan from selected ayahs.
     */
    private function generateCustomPlan($plan, $ayahsData)
    {
        $dayNumber = 1;
        $startDate = Carbon::parse($plan->start_date);

        foreach ($ayahsData as $item) {
            MemorizationPlanItem::create([
                'memorization_plan_id' => $plan->id,
                'surah_id' => $item['surah_id'] ?? null,
                'from_ayah_id' => $item['from_ayah_id'],
                'to_ayah_id' => $item['to_ayah_id'] ?? $item['from_ayah_id'],
                'day_number' => $dayNumber,
                'target_date' => $startDate->copy()->addDays($dayNumber - 1),
                'status' => 'pending',
            ]);
            $dayNumber++;
        }
    }

    /**
     * Display the specified memorization plan.
     */
    public function show(MemorizationPlan $memorizationPlan)
    {
        $memorizationPlan->load(['items.fromAyah.surah', 'items.toAyah.surah']);

        $items = $memorizationPlan->items()
            ->orderBy('day_number')
            ->get();

        $stats = $this->getPlanStats($memorizationPlan, $items);
        $progress = $stats['total_days'] > 0 
            ? round(($stats['completed_days'] / $stats['total_days']) * 100) 
            : 0;

        $todayItem = $items->where('target_date', today()->toDateString())->first();

        // بەکارهێنەر دەتوانێت تەنها پلانە چالاکەکان ببینێت (ئەگەر ئەدمین نەبێت)
        if (auth()->user()->role !== 'admin' && $memorizationPlan->status !== 'active') {
            abort(404);
        }

        return view('memorization-plans.show', compact(
            'memorizationPlan', 'items', 'stats', 'progress', 'todayItem'
        ));
    }

    /**
     * Show the form for editing the specified memorization plan.
     */
    public function edit(MemorizationPlan $memorizationPlan)
    {
        $planTypes = $this->getPlanTypes();
        $dailyTargetTypes = $this->getDailyTargetTypes();
        $statuses = $this->getStatuses();

        return view('memorization-plans.edit', compact(
            'memorizationPlan', 'planTypes', 'dailyTargetTypes', 'statuses'
        ));
    }

    /**
     * Update the specified memorization plan in storage.
     */
    public function update(Request $request, MemorizationPlan $memorizationPlan)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'target_end_date' => 'nullable|date|after_or_equal:start_date',
            'daily_target_type' => 'required|in:ayahs,pages,juz,hizb',
            'daily_target_value' => 'required|integer|min:1',
            'status' => 'required|in:active,paused,completed',
            'notes' => 'nullable|string',
        ]);

        $memorizationPlan->update($validated);

        return redirect()
            ->route('memorization-plans.show', $memorizationPlan)
            ->with('success', __('memorization_plans.messages.updated'));
    }

    /**
     * Remove the specified memorization plan from storage.
     */
    public function destroy(MemorizationPlan $memorizationPlan)
    {
        $memorizationPlan->delete();

        return redirect()
            ->route('memorization-plans.index')
            ->with('success', __('memorization_plans.messages.deleted'));
    }

    /**
     * Update plan item status.
     */
    public function updateItemStatus(Request $request, MemorizationPlan $memorizationPlan, MemorizationPlanItem $item)
    {
        // پشکنینی ئایا بەکارهێنەر دەتوانێت ئەم پلانە ببینێت
        if (auth()->user()->role !== 'admin' && $memorizationPlan->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,skipped',
        ]);

        $item->update(['status' => $validated['status']]);

        // ئەگەر بەکارهێنەر تەواوی کرد، پێداچوونەوەیەکی خۆکارانە دروست بکە
        if ($validated['status'] === 'completed' && auth()->user()->role !== 'admin') {
            $this->createAutoReview($item);
        }

        // پشکنینی ئایا هەموو بڕگەکان تەواو بوون
        $totalItems = $memorizationPlan->items()->count();
        $completedItems = $memorizationPlan->items()->where('status', 'completed')->count();

        if ($totalItems > 0 && $totalItems === $completedItems) {
            $memorizationPlan->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'status' => $item->status,
            'plan_completed' => $totalItems === $completedItems,
            'message' => __('memorization_plans.messages.item_updated'),
        ]);
    }

    /**
     * دروستکردنی پێداچوونەوەی خۆکارانە کاتێک بەکارهێنەر ڕۆژێک تەواو دەکات
     */
    private function createAutoReview($item): void
    {
        // هەموو ئایەتەکانی ئەم ڕۆژە
        $ayahs = Ayah::where('surah_id', $item->surah_id)
            ->whereBetween('ayah_number', [
                $item->fromAyah->ayah_number, 
                $item->toAyah->ayah_number
            ])
            ->get();

        foreach ($ayahs as $ayah) {
            // پشکنینی ئایا پێشتر پێداچوونەوەی بۆ کراوە لەم ڕۆژەدا
            $exists = MemorizationReview::where('user_id', auth()->id())
                ->where('ayah_id', $ayah->id)
                ->whereDate('review_date', today())
                ->exists();

            if (!$exists) {
                MemorizationReview::create([
                    'user_id' => auth()->id(),
                    'ayah_id' => $ayah->id,
                    'review_date' => today(),
                    'review_level' => 'new',
                    'notes' => __('memorization_reviews.messages.auto_created', [
                        'plan' => $item->memorizationPlan->title
                    ]),
                ]);
            }
        }
    }

    /**
     * Get statistics for a specific plan.
     */
    private function getPlanStats($plan, $items): array
    {
        return [
            'total_days' => $items->count(),
            'completed_days' => $items->where('status', 'completed')->count(),
            'pending_days' => $items->where('status', 'pending')->count(),
            'skipped_days' => $items->where('status', 'skipped')->count(),
            'total_ayahs' => $items->sum(function ($item) {
                if ($item->fromAyah && $item->toAyah && $item->fromAyah->surah_id === $item->toAyah->surah_id) {
                    return $item->toAyah->ayah_number - $item->fromAyah->ayah_number + 1;
                }
                return 0;
            }),
        ];
    }

    /**
     * Get statistics for index page.
     */
    private function getStats(): array
    {
        if (auth()->user()->role === 'admin') {
            return [
                'total_plans' => MemorizationPlan::count(),
                'active_plans' => MemorizationPlan::where('status', 'active')->count(),
                'completed_plans' => MemorizationPlan::where('status', 'completed')->count(),
                'total_users' => MemorizationPlan::distinct('user_id')->count('user_id'),
            ];
        }

        return [
            'total_plans' => MemorizationPlan::where('status', 'active')->count(),
            'active_plans' => MemorizationPlan::where('status', 'active')->count(),
            'completed_plans' => 0,
            'total_items' => MemorizationPlanItem::whereHas('memorizationPlan', function ($q) {
                $q->where('status', 'active');
            })->count(),
        ];
    }

    /**
     * Get plan types.
     */
    private function getPlanTypes(): array
    {
        return [
            'juz' => __('memorization_plans.plan_types.juz'),
            'surah' => __('memorization_plans.plan_types.surah'),
            'custom' => __('memorization_plans.plan_types.custom'),
        ];
    }

    /**
     * Get daily target types.
     */
    private function getDailyTargetTypes(): array
    {
        return [
            'ayahs' => __('memorization_plans.target_types.ayahs'),
            'pages' => __('memorization_plans.target_types.pages'),
            'juz' => __('memorization_plans.target_types.juz'),
            'hizb' => __('memorization_plans.target_types.hizb'),
        ];
    }

    /**
     * Get statuses.
     */
    private function getStatuses(): array
    {
        return [
            'active' => __('memorization_plans.statuses.active'),
            'paused' => __('memorization_plans.statuses.paused'),
            'completed' => __('memorization_plans.statuses.completed'),
        ];
    }

    /**
     * Get ayahs by surah for AJAX.
     */
    public function getSurahAyahs($surahId)
    {
        $ayahs = Ayah::where('surah_id', $surahId)
            ->orderBy('ayah_number')
            ->get(['id', 'ayah_number', 'text_uthmani']);

        return response()->json($ayahs);
    }

    /**
     * Get ayahs by juz for AJAX.
     */
    public function getJuzAyahs($juzNumber)
    {
        $ayahs = Ayah::where('juz_number', $juzNumber)
            ->with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        return response()->json($ayahs);
    }
}