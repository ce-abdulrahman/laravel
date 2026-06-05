<?php
// app/Http/Controllers/TranslationController.php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TranslationController extends Controller
{
    /**
     * Display a listing of the translations.
     */
    public function index(Request $request)
    {
        $query = Translation::with(['ayah.surah'])->where('is_active', true);

        // فلتەر بەپێی زمان
        if ($request->filled('language_code')) {
            $query->where('language_code', $request->language_code);
        }

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // فلتەر بەپێی وەرگێڕ
        if ($request->filled('translator')) {
            $query->where('translator_name', 'like', '%' . $request->translator . '%');
        }

        // گەڕان بەپێی ناوەڕۆک
        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        $translations = $query->orderBy('ayah_id')
            ->orderBy('language_code')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();
        $languages = $this->getAvailableLanguages();
        $translators = Translation::distinct()
            ->whereNotNull('translator_name')
            ->pluck('translator_name');

        $stats = [
            'total_translations' => Translation::count(),
            'total_languages' => Translation::distinct('language_code')->count('language_code'),
            'default_translations' => Translation::where('is_default', true)->count(),
        ];

        return view('translations.index', compact(
            'translations', 'surahs', 'languages', 'translators', 'stats'
        ));
    }

    /**
     * Show the form for creating a new translation.
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $languages = $this->getAvailableLanguages();
        $selectedAyah = null;

        if ($request->filled('ayah_id')) {
            $selectedAyah = Ayah::with('surah')->find($request->ayah_id);
        }

        return view('translations.create', compact('ayahs', 'languages', 'selectedAyah'));
    }

    /**
     * Store a newly created translation in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'language_code' => 'required|string|max:10',
            'translator_name' => 'nullable|string|max:255',
            'content' => 'required|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // پشکنینی دووبارە نەبوونی وەرگێڕان بۆ هەمان ئایەت و زمان
        $exists = Translation::where('ayah_id', $validated['ayah_id'])
            ->where('language_code', $validated['language_code'])
            ->where('translator_name', $validated['translator_name'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['language_code' => __('translations.validation.translation_exists')]);
        }

        // ئەگەر وەرگێڕانەکە دیفۆڵت بێت، ئەوا وەرگێڕانە دیفۆڵتەکانی تر ناچالاک دەکات
        if ($request->boolean('is_default')) {
            Translation::where('ayah_id', $validated['ayah_id'])
                ->where('language_code', $validated['language_code'])
                ->update(['is_default' => false]);
        }

        $translation = Translation::create($validated);

        return redirect()
            ->route('translations.show', $translation)
            ->with('success', __('translations.messages.created'));
    }

    /**
     * Display the specified translation.
     */
    public function show(Translation $translation)
    {
        $translation->load(['ayah.surah']);

        $otherTranslations = Translation::where('ayah_id', $translation->ayah_id)
            ->where('id', '!=', $translation->id)
            ->with('ayah.surah')
            ->get()
            ->groupBy('language_code');

        return view('translations.show', compact('translation', 'otherTranslations'));
    }

    /**
     * Show the form for editing the specified translation.
     */
    public function edit(Translation $translation)
    {
        $this->authorizeAdmin();

        $ayahs = Ayah::with('surah')
            ->orderBy('surah_id')
            ->orderBy('ayah_number')
            ->get();

        $languages = $this->getAvailableLanguages();

        return view('translations.edit', compact('translation', 'ayahs', 'languages'));
    }

    /**
     * Update the specified translation in storage.
     */
    public function update(Request $request, Translation $translation)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'language_code' => 'required|string|max:10',
            'translator_name' => 'nullable|string|max:255',
            'content' => 'required|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // پشکنینی دووبارە نەبوونی وەرگێڕان
        $exists = Translation::where('ayah_id', $validated['ayah_id'])
            ->where('language_code', $validated['language_code'])
            ->where('translator_name', $validated['translator_name'])
            ->where('id', '!=', $translation->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['language_code' => __('translations.validation.translation_exists')]);
        }

        // ئەگەر وەرگێڕانەکە دیفۆڵت بێت
        if ($request->boolean('is_default')) {
            Translation::where('ayah_id', $validated['ayah_id'])
                ->where('language_code', $validated['language_code'])
                ->where('id', '!=', $translation->id)
                ->update(['is_default' => false]);
        }

        $translation->update($validated);

        return redirect()
            ->route('translations.show', $translation)
            ->with('success', __('translations.messages.updated'));
    }

    /**
     * Remove the specified translation from storage.
     */
    public function destroy(Translation $translation)
    {
        $this->authorizeAdmin();

        $translation->delete();

        return redirect()
            ->route('translations.index')
            ->with('success', __('translations.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Translation $translation)
    {
        $this->authorizeAdmin();

        $translation->update(['is_active' => !$translation->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $translation->is_active,
            'message' => $translation->is_active 
                ? __('translations.messages.activated') 
                : __('translations.messages.deactivated'),
        ]);
    }

    /**
     * Set as default translation.
     */
    public function setDefault(Translation $translation)
    {
        $this->authorizeAdmin();

        Translation::where('ayah_id', $translation->ayah_id)
            ->where('language_code', $translation->language_code)
            ->update(['is_default' => false]);

        $translation->update(['is_default' => true]);

        return back()->with('success', __('translations.messages.set_default'));
    }

    /**
     * Get available languages for translations.
     */
    private function getAvailableLanguages(): array
    {
        return [
            'ku' => 'کوردی (Kurdish)',
            'ar' => 'العربية (Arabic)',
            'en' => 'English',
            'fa' => 'فارسی (Persian)',
            'tr' => 'Türkçe (Turkish)',
            'ur' => 'اردو (Urdu)',
            'fr' => 'Français (French)',
            'de' => 'Deutsch (German)',
            'es' => 'Español (Spanish)',
            'id' => 'Bahasa Indonesia',
            'ms' => 'Bahasa Melayu',
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

    public function import(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $translations = json_decode($json, true);

        if (! is_array($translations)) {
            return back()->with('error', 'Invalid JSON file structure.');
        }

        $ayahs = [];
        $imported = 0;

        foreach ($translations as $transData) {
            $surahNumber = $transData['surah_number'] ?? null;
            $ayahNumber = $transData['ayah_number'] ?? null;
            $ayahId = $transData['ayah_id'] ?? null;
            $languageCode = $transData['language_code'] ?? null;
            $content = $transData['content'] ?? null;

            if (empty($languageCode) || empty($content)) {
                continue;
            }

            if (empty($ayahId)) {
                if (empty($surahNumber) || empty($ayahNumber)) {
                    continue;
                }
                $key = "{$surahNumber}_{$ayahNumber}";
                if (!isset($ayahs[$key])) {
                    $ayah = Ayah::whereHas('surah', function ($q) use ($surahNumber) {
                        $q->where('number', $surahNumber);
                    })->where('ayah_number', $ayahNumber)->first();

                    if (!$ayah) {
                        continue;
                    }
                    $ayahs[$key] = $ayah->id;
                }
                $ayahId = $ayahs[$key];
            }

            if (filter_var($transData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                Translation::where('ayah_id', $ayahId)
                    ->where('language_code', $languageCode)
                    ->update(['is_default' => false]);
            }

            Translation::updateOrCreate(
                [
                    'ayah_id' => $ayahId,
                    'language_code' => $languageCode,
                    'translator_name' => $transData['translator_name'] ?? null,
                ],
                [
                    'content' => $content,
                    'is_default' => filter_var($transData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'is_active' => filter_var($transData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );
            $imported++;
        }

        return redirect()->route('translations.index')->with('success', "Imported {$imported} Translations successfully.");
    }
}