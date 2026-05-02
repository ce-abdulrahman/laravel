<?php
// app/Http/Controllers/AyahTajweedSegmentController.php

namespace App\Http\Controllers;

use App\Models\AyahTajweedSegment;
use App\Models\TajweedRule;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;

class AyahTajweedSegmentController extends Controller
{
    /**
     * Display a listing of the tajweed segments.
     */
    public function index(Request $request)
    {
        $query = AyahTajweedSegment::with(['ayah.surah', 'tajweedRule']);

        // فلتەر بەپێی یاسای تەجوید
        if ($request->filled('tajweed_rule_id')) {
            $query->where('tajweed_rule_id', $request->tajweed_rule_id);
        }

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // گەڕان بەپێی دەق
        if ($request->filled('search')) {
            $query->where('text_segment', 'like', '%' . $request->search . '%');
        }

        $segments = $query->orderBy('ayah_id')
            ->orderBy('start_index')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $tajweedRules = TajweedRule::active()->orderBy('category')->orderBy('name')->get();
        $surahs = Surah::orderBy('id')->get();

        $stats = [
            'total_segments' => AyahTajweedSegment::count(),
            'total_rules_used' => AyahTajweedSegment::distinct('tajweed_rule_id')->count('tajweed_rule_id'),
            'total_ayahs_with_tajweed' => AyahTajweedSegment::distinct('ayah_id')->count('ayah_id'),
        ];

        return view('tajweed-segments.index', compact(
            'segments', 'tajweedRules', 'surahs', 'stats'
        ));
    }

    /**
     * Show the form for creating a new tajweed segment.
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $tajweedRules = TajweedRule::active()->orderBy('category')->orderBy('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $selectedRule = null;
        $selectedAyah = null;

        if ($request->filled('tajweed_rule_id')) {
            $selectedRule = TajweedRule::find($request->tajweed_rule_id);
        }

        if ($request->filled('ayah_id')) {
            $selectedAyah = Ayah::with('surah')->find($request->ayah_id);
        }

        return view('tajweed-segments.create', compact(
            'tajweedRules', 'ayahs', 'selectedRule', 'selectedAyah'
        ));
    }

    /**
     * Store a newly created tajweed segment in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'tajweed_rule_id' => 'required|exists:tajweed_rules,id',
            'text_segment' => 'required|string',
            'start_index' => 'nullable|integer|min:0',
            'end_index' => 'nullable|integer|min:0|gte:start_index',
            'note' => 'nullable|string',
        ]);

        $segment = AyahTajweedSegment::create($validated);

        return redirect()
            ->route('tajweed-segments.show', $segment)
            ->with('success', __('tajweed_segments.messages.created'));
    }

    /**
     * Store multiple tajweed segments at once.
     */
    public function storeBatch(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'segments' => 'required|array|min:1',
            'segments.*.tajweed_rule_id' => 'required|exists:tajweed_rules,id',
            'segments.*.text_segment' => 'required|string',
            'segments.*.start_index' => 'nullable|integer|min:0',
            'segments.*.end_index' => 'nullable|integer|min:0',
            'segments.*.note' => 'nullable|string',
        ]);

        foreach ($validated['segments'] as $segmentData) {
            AyahTajweedSegment::create([
                'ayah_id' => $validated['ayah_id'],
                'tajweed_rule_id' => $segmentData['tajweed_rule_id'],
                'text_segment' => $segmentData['text_segment'],
                'start_index' => $segmentData['start_index'] ?? null,
                'end_index' => $segmentData['end_index'] ?? null,
                'note' => $segmentData['note'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('tajweed_segments.messages.created_batch'),
        ]);
    }

    /**
     * Display the specified tajweed segment.
     */
    public function show(AyahTajweedSegment $tajweedSegment)
    {
        $tajweedSegment->load(['ayah.surah', 'tajweedRule']);

        $otherSegments = AyahTajweedSegment::where('ayah_id', $tajweedSegment->ayah_id)
            ->with('tajweedRule')
            ->orderBy('start_index')
            ->get();

        return view('tajweed-segments.show', compact('tajweedSegment', 'otherSegments'));
    }

    /**
     * Show the form for editing the specified tajweed segment.
     */
    public function edit(AyahTajweedSegment $tajweedSegment)
    {
        $this->authorizeAdmin();

        $tajweedRules = TajweedRule::orderBy('category')->orderBy('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        return view('tajweed-segments.edit', compact('tajweedSegment', 'tajweedRules', 'ayahs'));
    }

    /**
     * Update the specified tajweed segment in storage.
     */
    public function update(Request $request, AyahTajweedSegment $tajweedSegment)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'tajweed_rule_id' => 'required|exists:tajweed_rules,id',
            'text_segment' => 'required|string',
            'start_index' => 'nullable|integer|min:0',
            'end_index' => 'nullable|integer|min:0|gte:start_index',
            'note' => 'nullable|string',
        ]);

        $tajweedSegment->update($validated);

        return redirect()
            ->route('tajweed-segments.show', $tajweedSegment)
            ->with('success', __('tajweed_segments.messages.updated'));
    }

    /**
     * Remove the specified tajweed segment from storage.
     */
    public function destroy(AyahTajweedSegment $tajweedSegment)
    {
        $this->authorizeAdmin();

        $tajweedSegment->delete();

        return redirect()
            ->route('tajweed-segments.index')
            ->with('success', __('tajweed_segments.messages.deleted'));
    }

    /**
     * Get segments for a specific ayah.
     */
    public function byAyah($ayahId)
    {
        $segments = AyahTajweedSegment::where('ayah_id', $ayahId)
            ->with('tajweedRule')
            ->orderBy('start_index')
            ->get();

        $ayah = Ayah::with('surah')->findOrFail($ayahId);

        return response()->json([
            'ayah' => [
                'id' => $ayah->id,
                'text' => $ayah->text_uthmani,
                'surah' => $ayah->surah->name_ar,
                'ayah_number' => $ayah->ayah_number,
            ],
            'segments' => $segments,
        ]);
    }

    /**
     * Authorize admin access.
     */
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }
    }
}