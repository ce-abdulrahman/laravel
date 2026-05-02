<?php

namespace App\Http\Controllers;

use App\Models\QiraatText;
use App\Models\Qiraat;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;

class QiraatTextController extends Controller
{
    /**
     * Display a listing of the qiraat texts.
     */
    public function index(Request $request)
    {
        $query = QiraatText::with(['qiraat', 'ayah.surah']);

        // فلتەر بەپێی قیرائەت
        if ($request->filled('qiraah_id')) {
            $query->where('qiraah_id', $request->qiraah_id);
        }

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // گەڕان بەپێی دەق
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('text_variant', 'like', '%' . $request->search . '%')
                  ->orWhere('note', 'like', '%' . $request->search . '%');
            });
        }

        $qiraatTexts = $query->orderBy('qiraah_id')
            ->orderBy('ayah_id')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $qiraats = Qiraat::active()->orderBy('name')->get();
        $surahs = Surah::orderBy('id')->get();

        $stats = [
            'total_texts' => QiraatText::count(),
            'total_qiraats_used' => QiraatText::distinct('qiraah_id')->count('qiraah_id'),
            'total_ayahs_with_qiraat' => QiraatText::distinct('ayah_id')->count('ayah_id'),
        ];

        return view('qiraat-texts.index', compact('qiraatTexts', 'qiraats', 'surahs', 'stats'));
    }

    /**
     * Show the form for creating a new qiraat text.
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $qiraats = Qiraat::active()->orderBy('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $selectedQiraat = null;
        $selectedAyah = null;

        if ($request->filled('qiraah_id')) {
            $selectedQiraat = Qiraat::find($request->qiraah_id);
        }

        if ($request->filled('ayah_id')) {
            $selectedAyah = Ayah::with('surah')->find($request->ayah_id);
        }

        return view('qiraat-texts.create', compact(
            'qiraats', 'ayahs', 'selectedQiraat', 'selectedAyah'
        ));
    }

    /**
     * Store a newly created qiraat text in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'qiraah_id' => 'required|exists:qiraats,id',
            'ayah_id' => 'required|exists:ayahs,id',
            'text_variant' => 'required|string',
            'note' => 'nullable|string',
        ]);

        // پشکنینی دووبارە نەبوون
        $exists = QiraatText::where('qiraah_id', $validated['qiraah_id'])
            ->where('ayah_id', $validated['ayah_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['qiraah_id' => __('qiraat_texts.validation.text_exists')]);
        }

        $qiraatText = QiraatText::create($validated);

        return redirect()
            ->route('qiraat-texts.show', $qiraatText)
            ->with('success', __('qiraat_texts.messages.created'));
    }

    /**
     * Display the specified qiraat text.
     */
    public function show(QiraatText $qiraatText)
    {
        $qiraatText->load(['qiraat', 'ayah.surah']);

        $otherVariants = QiraatText::where('ayah_id', $qiraatText->ayah_id)
            ->where('id', '!=', $qiraatText->id)
            ->with('qiraat')
            ->get();

        $originalAyah = $qiraatText->ayah;

        return view('qiraat-texts.show', compact('qiraatText', 'otherVariants', 'originalAyah'));
    }

    /**
     * Show the form for editing the specified qiraat text.
     */
    public function edit(QiraatText $qiraatText)
    {
        $this->authorizeAdmin();

        $qiraats = Qiraat::orderBy('name')->get();
        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        return view('qiraat-texts.edit', compact('qiraatText', 'qiraats', 'ayahs'));
    }

    /**
     * Update the specified qiraat text in storage.
     */
    public function update(Request $request, QiraatText $qiraatText)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'qiraah_id' => 'required|exists:qiraats,id',
            'ayah_id' => 'required|exists:ayahs,id',
            'text_variant' => 'required|string',
            'note' => 'nullable|string',
        ]);

        // پشکنینی دووبارە نەبوون
        $exists = QiraatText::where('qiraah_id', $validated['qiraah_id'])
            ->where('ayah_id', $validated['ayah_id'])
            ->where('id', '!=', $qiraatText->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['qiraah_id' => __('qiraat_texts.validation.text_exists')]);
        }

        $qiraatText->update($validated);

        return redirect()
            ->route('qiraat-texts.show', $qiraatText)
            ->with('success', __('qiraat_texts.messages.updated'));
    }

    /**
     * Remove the specified qiraat text from storage.
     */
    public function destroy(QiraatText $qiraatText)
    {
        $this->authorizeAdmin();

        $qiraatText->delete();

        return redirect()
            ->route('qiraat-texts.index')
            ->with('success', __('qiraat_texts.messages.deleted'));
    }

    /**
     * Get ayahs by surah for AJAX.
     */
    public function getAyahs($surahId)
    {
        $ayahs = Ayah::where('surah_id', $surahId)
            ->orderBy('ayah_number')
            ->get(['id', 'ayah_number', 'text_uthmani']);

        return response()->json($ayahs);
    }

    /**
     * Compare qiraat variants for an ayah.
     */
    public function compare($ayahId)
    {
        $ayah = Ayah::with('surah')->findOrFail($ayahId);
        
        $variants = QiraatText::where('ayah_id', $ayahId)
            ->with('qiraat')
            ->get();

        return view('qiraat-texts.compare', compact('ayah', 'variants'));
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