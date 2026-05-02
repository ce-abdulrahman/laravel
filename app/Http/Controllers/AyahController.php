<?php

namespace App\Http\Controllers;

use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AyahController extends Controller
{
    /**
     * Display a listing of the ayahs.
     */
    public function index(Request $request)
    {
        $query = Ayah::with('surah')->active();

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->where('surah_id', $request->surah_id);
        }

        // فلتەر بەپێی جوز
        if ($request->filled('juz_number')) {
            $query->where('juz_number', $request->juz_number);
        }

        // گەڕان بەپێی دەقی ئایەت
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('text_uthmani', 'like', '%' . $request->search . '%')
                  ->orWhere('text_simple', 'like', '%' . $request->search . '%');
            });
        }

        $ayahs = $query->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();
        $juzNumbers = range(1, 30);

        $stats = [
            'total_ayahs' => Ayah::count(),
            'total_surahs' => Surah::count(),
            'sajda_ayahs' => Ayah::where('sajda_flag', true)->count(),
        ];

        return view('ayahs.index', compact('ayahs', 'surahs', 'juzNumbers', 'stats'));
    }

    /**
     * Show the form for creating a new ayah.
     */
    public function create()
    {
        $surahs = Surah::orderBy('id')->get();
        $juzNumbers = range(1, 30);

        return view('ayahs.create', compact('surahs', 'juzNumbers'));
    }

    /**
     * Store a newly created ayah in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'surah_id' => 'required|exists:surahs,id',
            'ayah_number' => 'required|integer|min:1',
            'text_uthmani' => 'required|string',
            'text_simple' => 'nullable|string',
            'page_number' => 'nullable|integer|min:1',
            'juz_number' => 'nullable|integer|min:1|max:30',
            'hizb_number' => 'nullable|integer|min:1|max:60',
            'rub_number' => 'nullable|integer|min:1|max:240',
            'sajda_flag' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // پشکنینی دووبارە نەبوونی ئایەت لە هەمان سورەتدا
        $exists = Ayah::where('surah_id', $validated['surah_id'])
            ->where('ayah_number', $validated['ayah_number'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['ayah_number' => __('forms.validation.ayah_number_exists')]);
        }

        $ayah = Ayah::create($validated);

        return redirect()
            ->route('ayahs.show', $ayah)
            ->with('success', __('forms.messages.ayah_created'));
    }

    /**
     * Display the specified ayah.
     */
    public function show(Ayah $ayah)
    {
        $ayah->load(['surah', 'translations.language', 'tafsirs.tafsirBook', 'audioFiles.reciter']);

        $nextAyah = Ayah::where('surah_id', $ayah->surah_id)
            ->where('ayah_number', '>', $ayah->ayah_number)
            ->orderBy('ayah_number')
            ->first();

        $prevAyah = Ayah::where('surah_id', $ayah->surah_id)
            ->where('ayah_number', '<', $ayah->ayah_number)
            ->orderBy('ayah_number', 'desc')
            ->first();

        // ئایەتی داهاتوو لە سورەتی دواتر
        if (!$nextAyah && $ayah->surah_id < 114) {
            $nextAyah = Ayah::where('surah_id', $ayah->surah_id + 1)
                ->orderBy('ayah_number')
                ->first();
        }

        // ئایەتی پێشوو لە سورەتی پێشوو
        if (!$prevAyah && $ayah->surah_id > 1) {
            $prevAyah = Ayah::where('surah_id', $ayah->surah_id - 1)
                ->orderBy('ayah_number', 'desc')
                ->first();
        }

        $userBookmark = null;
        if (auth()->check()) {
            $userBookmark = $ayah->bookmarks()
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('ayahs.show', compact('ayah', 'nextAyah', 'prevAyah', 'userBookmark'));
    }

    /**
     * Show the form for editing the specified ayah.
     */
    public function edit(Ayah $ayah)
    {
        $surahs = Surah::orderBy('id')->get();
        $juzNumbers = range(1, 30);

        return view('ayahs.edit', compact('ayah', 'surahs', 'juzNumbers'));
    }

    /**
     * Update the specified ayah in storage.
     */
    public function update(Request $request, Ayah $ayah)
    {
        $validated = $request->validate([
            'surah_id' => 'required|exists:surahs,id',
            'ayah_number' => 'required|integer|min:1',
            'text_uthmani' => 'required|string',
            'text_simple' => 'nullable|string',
            'page_number' => 'nullable|integer|min:1',
            'juz_number' => 'nullable|integer|min:1|max:30',
            'hizb_number' => 'nullable|integer|min:1|max:60',
            'rub_number' => 'nullable|integer|min:1|max:240',
            'sajda_flag' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // پشکنینی دووبارە نەبوونی ئایەت لە هەمان سورەتدا
        $exists = Ayah::where('surah_id', $validated['surah_id'])
            ->where('ayah_number', $validated['ayah_number'])
            ->where('id', '!=', $ayah->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['ayah_number' => __('forms.validation.ayah_number_exists')]);
        }

        $ayah->update($validated);

        return redirect()
            ->route('ayahs.show', $ayah)
            ->with('success', __('forms.messages.ayah_updated'));
    }

    /**
     * Remove the specified ayah from storage.
     */
    public function destroy(Ayah $ayah)
    {
        $ayah->delete();

        return redirect()
            ->route('ayahs.index')
            ->with('success', __('forms.messages.ayah_deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Ayah $ayah)
    {
        $ayah->update(['is_active' => !$ayah->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $ayah->is_active,
            'message' => $ayah->is_active
                ? __('forms.messages.ayah_activated')
                : __('forms.messages.ayah_deactivated'),
        ]);
    }
}
