<?php

namespace App\Http\Controllers;

use App\Models\Reciter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReciterController extends Controller
{
    /**
     * Display a listing of the reciters.
     */
    public function index(Request $request)
    {
        $query = Reciter::withCount('audioFiles');

        // فلتەر بەپێی زمان
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        // فلتەر بەپێی ڕیوایەت
        if ($request->filled('riwayah')) {
            $query->where('riwayah', $request->riwayah);
        }

        // گەڕان بەپێی ناو
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // فلتەر بەپێی دۆخ
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $reciters = $query->orderBy('name')
            ->paginate($request->per_page ?? 12)
            ->withQueryString();

        $languages = Reciter::distinct()->whereNotNull('language')->pluck('language');
        $riwayahs = Reciter::distinct()->whereNotNull('riwayah')->pluck('riwayah');

        $stats = [
            'total_reciters' => Reciter::count(),
            'active_reciters' => Reciter::where('is_active', true)->count(),
            'total_audio_files' => \App\Models\AudioFile::count(),
        ];

        return view('reciters.index', compact('reciters', 'languages', 'riwayahs', 'stats'));
    }

    /**
     * Show the form for creating a new reciter.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $riwayahs = $this->getRiwayahs();
        $languages = $this->getLanguages();

        return view('reciters.create', compact('riwayahs', 'languages'));
    }

    /**
     * Store a newly created reciter in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:reciters,name',
            'riwayah' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('reciters', 'public');
        }

        $reciter = Reciter::create($validated);

        return redirect()
            ->route('reciters.show', $reciter)
            ->with('success', __('reciters.messages.created'));
    }

    /**
     * Display the specified reciter.
     */
    public function show(Reciter $reciter)
    {
        $reciter->load(['audioFiles' => function ($query) {
            $query->with(['surah', 'ayah'])->orderBy('surah_id')->orderBy('ayah_id');
        }]);

        $audioStats = [
            'total' => $reciter->audioFiles->count(),
            'full_surahs' => $reciter->audioFiles->where('ayah_id', null)->count(),
            'individual_ayahs' => $reciter->audioFiles->where('ayah_id', '!=', null)->count(),
        ];

        return view('reciters.show', compact('reciter', 'audioStats'));
    }

    /**
     * Show the form for editing the specified reciter.
     */
    public function edit(Reciter $reciter)
    {
        $this->authorizeAdmin();

        $riwayahs = $this->getRiwayahs();
        $languages = $this->getLanguages();

        return view('reciters.edit', compact('reciter', 'riwayahs', 'languages'));
    }

    /**
     * Update the specified reciter in storage.
     */
    public function update(Request $request, Reciter $reciter)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:reciters,name,' . $reciter->id,
            'riwayah' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // سڕینەوەی وێنەی پێشوو
            if ($reciter->image) {
                Storage::disk('public')->delete($reciter->image);
            }
            $validated['image'] = $request->file('image')->store('reciters', 'public');
        }

        $reciter->update($validated);

        return redirect()
            ->route('reciters.show', $reciter)
            ->with('success', __('reciters.messages.updated'));
    }

    /**
     * Remove the specified reciter from storage.
     */
    public function destroy(Reciter $reciter)
    {
        $this->authorizeAdmin();

        // پشکنینی ئایا فایلی دەنگی هەیە
        if ($reciter->audioFiles()->count() > 0) {
            return back()->with('error', __('reciters.messages.has_audio'));
        }

        // سڕینەوەی وێنە
        if ($reciter->image) {
            Storage::disk('public')->delete($reciter->image);
        }

        $reciter->delete();

        return redirect()
            ->route('reciters.index')
            ->with('success', __('reciters.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Reciter $reciter)
    {
        $this->authorizeAdmin();

        $reciter->update(['is_active' => !$reciter->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $reciter->is_active,
            'message' => $reciter->is_active 
                ? __('reciters.messages.activated') 
                : __('reciters.messages.deactivated'),
        ]);
    }

    /**
     * Get riwayah list.
     */
    private function getRiwayahs(): array
    {
        return [
            'hafs' => 'حفص عن عاصم',
            'warsh' => 'ورش عن نافع',
            'qaloon' => 'قالون عن نافع',
            'al_doori' => 'الدوري عن أبي عمرو',
            'al_sousi' => 'السوسي عن أبي عمرو',
            'ibn_amer' => 'ابن عامر',
            'ibn_kathir' => 'ابن كثير',
            'hamzah' => 'حمزة',
            'al_kisai' => 'الكسائي',
            'khalaf' => 'خلف عن حمزة',
            'yaqoub' => 'يعقوب الحضرمي',
            'other' => 'أخرى',
        ];
    }

    /**
     * Get language list.
     */
    private function getLanguages(): array
    {
        return [
            'ar' => 'العربية',
            'ku' => 'کوردی',
            'en' => 'English',
            'fa' => 'فارسی',
            'tr' => 'Türkçe',
            'ur' => 'اردو',
        ];
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