<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTajweedSegmentRequest;
use App\Http\Requests\UpdateTajweedSegmentRequest;
use App\Models\Ayah;
use App\Models\AyahTajweedSegment;
use App\Models\Surah;
use App\Models\TajweedRule;
use App\Models\TajweedRuleCategory;
use App\Services\TajweedSegmentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AyahTajweedSegmentController extends Controller
{
    protected TajweedSegmentService $segmentService;

    /**
     * Inject the TajweedSegmentService.
     */
    public function __construct(TajweedSegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    /**
     * Display a listing of the tajweed segments.
     */
    public function index(Request $request)
    {
        $query = AyahTajweedSegment::with(['ayah.surah', 'tajweedRule']);

        // Filter by Tajweed Rule
        if ($request->filled('tajweed_rule_id')) {
            $query->where('tajweed_rule_id', $request->tajweed_rule_id);
        }

        // Filter by Surah
        if ($request->filled('surah_id')) {
            $query->where('surah_id', $request->surah_id);
        }

        // Filter by Ayah number
        if ($request->filled('ayah_number')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('ayah_number', $request->ayah_number);
            });
        }

        // Filter by Rule Category
        if ($request->filled('category_id')) {
            $query->whereHas('tajweedRule', function ($q) use ($request) {
                $q->where('tajweed_rule_category_id', $request->category_id);
            });
        }

        // Search by matched text
        if ($request->filled('search')) {
            $query->where('matched_text', 'like', '%' . $request->search . '%');
        }

        $segments = $query->orderBy('surah_id')
            ->orderBy('ayah_id')
            ->orderBy('start_index')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $tajweedRules = TajweedRule::active()->orderByTranslation('name')->get();
        $categories = TajweedRuleCategory::active()->orderBy('order')->get();
        $surahs = Surah::orderBy('number')->get();

        $stats = [
            'total_segments' => AyahTajweedSegment::count(),
            'total_rules_used' => AyahTajweedSegment::distinct('tajweed_rule_id')->count('tajweed_rule_id'),
            'total_ayahs_with_tajweed' => AyahTajweedSegment::distinct('ayah_id')->count('ayah_id'),
        ];

        return view('tajweed-segments.index', compact(
            'segments', 'tajweedRules', 'categories', 'surahs', 'stats'
        ));
    }

    /**
     * Show the form for creating a new tajweed segment.
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $tajweedRules = TajweedRule::active()->orderByTranslation('name')->get();
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
    public function store(StoreTajweedSegmentRequest $request)
    {
        $validated = $request->validated();

        if (isset($validated['metadata']) && is_string($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }

        // Auto-resolve surah_id
        $ayah = Ayah::findOrFail($validated['ayah_id']);
        $validated['surah_id'] = $ayah->surah_id;

        $segment = AyahTajweedSegment::create($validated);

        return redirect()
            ->route('tajweed-segments.show', $segment)
            ->with('success', __('tajweed_segments.messages.created'));
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

        $tajweedRules = TajweedRule::orderByTranslation('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        return view('tajweed-segments.edit', compact('tajweedSegment', 'tajweedRules', 'ayahs'));
    }

    /**
     * Update the specified tajweed segment in storage.
     */
    public function update(UpdateTajweedSegmentRequest $request, AyahTajweedSegment $tajweedSegment)
    {
        $validated = $request->validated();

        if (isset($validated['metadata']) && is_string($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }

        // Auto-resolve surah_id
        $ayah = Ayah::findOrFail($validated['ayah_id']);
        $validated['surah_id'] = $ayah->surah_id;

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
     * Import segments from one or more uploaded JSON files.
     * Accepts: files[] (multiple) or file (single, backward-compat).
     */
    public function import(Request $request)
    {
        $this->authorizeAdmin();

        // Determine which field was used and validate accordingly
        if ($request->hasFile('files')) {
            $request->validate([
                'files'   => 'required|array|min:1',
                'files.*' => 'file|extensions:json',
            ]);
            $uploadedFiles = $request->file('files');
        } else {
            $request->validate([
                'file' => 'required|file|extensions:json',
            ]);
            $uploadedFiles = [$request->file('file')];
        }

        $totalImported = 0;
        $totalSkipped  = 0;
        $allErrors     = [];

        foreach ($uploadedFiles as $file) {
            $content   = file_get_contents($file->getRealPath());
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'txt') {
                $extension = str_contains($content, '{') && str_contains($content, '}') ? 'json' : 'csv';
            }

            $result = $this->segmentService->import($content, $extension);

            $totalImported += $result['imported'];
            $totalSkipped  += $result['skipped'];

            foreach ($result['errors'] as $err) {
                $allErrors[] = "[{$file->getClientOriginalName()}] {$err}";
            }
        }

        $fileCount = count($uploadedFiles);

        if (!empty($allErrors)) {
            return redirect()
                ->route('tajweed-segments.index')
                ->with('warning', "Import finished ({$fileCount} file(s)). Imported: {$totalImported}, Skipped: {$totalSkipped}. Some rows had errors:")
                ->withErrors($allErrors);
        }

        return redirect()
            ->route('tajweed-segments.index')
            ->with('success', "Imported {$totalImported} segments from {$fileCount} file(s). Skipped {$totalSkipped} duplicates.");
    }
    /**
     * Export segments in JSON or CSV.
     */
    public function export(Request $request)
    {
        $this->authorizeAdmin();

        $format = $request->input('format', 'json');
        if (!in_array($format, ['json', 'csv'])) {
            $format = 'json';
        }

        $filters = $request->only(['surah_id', 'tajweed_rule_id', 'category_id', 'search', 'ayah_number']);
        $exportData = $this->segmentService->export($filters, $format);

        $filename = 'tajweed_segments_' . date('Ymd_His') . '.' . $format;
        $contentType = $format === 'csv' ? 'text/csv' : 'application/json';

        return response($exportData)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Rebuild segments in transaction safety.
     */
    public function rebuild(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => 'required|file|extensions:json,csv,txt',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'txt') {
            $extension = str_contains($content, '{') && str_contains($content, '}') ? 'json' : 'csv';
        }

        $result = $this->segmentService->rebuild($content, $extension);

        if (!empty($result['errors'])) {
            return redirect()
                ->route('tajweed-segments.index')
                ->with('warning', "Rebuild executed. Clear complete. New imports - Success: {$result['imported']}, Skipped: {$result['skipped']}.")
                ->withErrors($result['errors']);
        }

        return redirect()
            ->route('tajweed-segments.index')
            ->with('success', "Rebuilt segments successfully. Clear complete. {$result['imported']} fresh segments imported.");
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
            'segments' => $segments->map(function ($s) {
                return [
                    'id' => $s->id,
                    'surah_id' => $s->surah_id,
                    'ayah_id' => $s->ayah_id,
                    'tajweed_rule_id' => $s->tajweed_rule_id,
                    'matched_text' => $s->matched_text,
                    'text_segment' => $s->matched_text, // Compatibility
                    'start_index' => $s->start_index,
                    'end_index' => $s->end_index,
                    'metadata' => $s->metadata,
                    'note' => $s->note,
                ];
            }),
        ]);
    }

    /**
     * Authorize admin access.
     */
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, __('common.unauthorized'));
        }
    }
}