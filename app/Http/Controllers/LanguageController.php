<?php

namespace App\Http\Controllers;

use App\Http\Requests\LanguageRequest;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LanguageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index', 'show', 'switch', 'switchLang', 'getCurrentLanguage']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $languages = Language::ordered()->paginate(20);
        return view('languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $language = new Language([
            'is_active' => true,
            'is_default' => false,
            'direction' => 'ltr',
            'order' => 0,
        ]);
        return view('languages.create', compact('language'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LanguageRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            if ($validated['is_default']) {
                Language::query()->update(['is_default' => false]);
            }

            Language::create($validated);
        });

        return redirect()->route('languages.index')->with('success', 'Language created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language)
    {
        return view('languages.edit', compact('language'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LanguageRequest $request, Language $language)
    {
        $validated = $request->validated();

        // Check if we are deactivating the only active language
        if (!$validated['is_active'] && $language->is_active && Language::active()->count() <= 1) {
            return back()->with('error', 'Cannot deactivate the last active language.');
        }

        // Check if we are deactivating the default language
        if (!$validated['is_active'] && $language->is_default) {
            return back()->with('error', 'Cannot deactivate the default language.');
        }

        // Check if we are removing default status and no other default language exists
        if (!$validated['is_default'] && $language->is_default) {
            return back()->with('error', 'The default language status must be assigned to another language first.');
        }

        DB::transaction(function () use ($language, $validated) {
            if ($validated['is_default']) {
                Language::query()->where('id', '!=', $language->id)->update(['is_default' => false]);
            }
            $language->update($validated);
        });

        return redirect()->route('languages.index')->with('success', 'Language updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Language $language)
    {
        // 1. Prevent removing the current default language
        if ($language->is_default) {
            return back()->with('error', 'Cannot delete the default language.');
        }

        // 2. Prevent removing the last active language
        if ($language->is_active && Language::active()->count() <= 1) {
            return back()->with('error', 'Cannot delete the last active language.');
        }

        // 3. Prevent deletion of languages already containing translations unless explicitly confirmed
        $hasTranslations = $this->hasActiveTranslations($language->code);

        if ($hasTranslations && !$request->has('confirm_delete')) {
            return view('languages.confirm_delete', compact('language'));
        }

        DB::transaction(function () use ($language) {
            $this->deleteTranslationsForLocale($language->code);
            $language->delete();
        });

        return redirect()->route('languages.index')->with('success', 'Language deleted successfully.');
    }

    /**
     * Check if a locale is used in any translation table.
     */
    private function hasActiveTranslations(string $locale): bool
    {
        $tables = [
            'surah_translations',
            'tajweed_rule_translations',
            'tajweed_rule_category_translations',
            'adhkar_translations',
            'hadith_translations',
            'hadith_category_translations',
            'adhkar_category_translations',
            'translations',
        ];

        foreach ($tables as $table) {
            $column = $table === 'translations' ? 'language_code' : 'locale';
            if (Schema::hasTable($table) && DB::table($table)->where($column, $locale)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete translations for a specific locale.
     */
    private function deleteTranslationsForLocale(string $locale): void
    {
        $tables = [
            'surah_translations',
            'tajweed_rule_translations',
            'tajweed_rule_category_translations',
            'adhkar_translations',
            'hadith_translations',
            'hadith_category_translations',
            'adhkar_category_translations',
            'translations',
        ];

        foreach ($tables as $table) {
            $column = $table === 'translations' ? 'language_code' : 'locale';
            if (Schema::hasTable($table)) {
                DB::table($table)->where($column, $locale)->delete();
            }
        }
    }

    /**
     * Switch application language.
     */
    public function switch(Request $request, string $locale)
    {
        return $this->switchLang($request, $locale);
    }

    /**
     * Switch application language with user preference support.
     */
    public function switchLang(Request $request, string $code)
    {
        $supportedLocales = Language::activeCodes();

        if (in_array($code, $supportedLocales, true)) {
            Session::put('locale', $code);
            App::setLocale($code);

            if (auth()->check()) {
                auth()->user()->update(['preferred_locale' => $code]);
            }
        }

        return redirect()->back();
    }

    /**
     * Get current language.
     */
    public function getCurrentLanguage()
    {
        $locale = App::getLocale();
        $currentLanguage = Language::where('code', $locale)->first() ?? Language::where('is_default', true)->first();

        if (!$currentLanguage) {
            $direction = 'ltr';
            $langData = [
                'name' => 'English',
                'native' => 'English',
                'dir' => 'ltr',
                'flag' => '🇬🇧',
            ];
        } else {
            $direction = $currentLanguage->direction;
            $langData = [
                'name' => $currentLanguage->name,
                'native' => $currentLanguage->native_name,
                'dir' => $currentLanguage->direction,
                'flag' => $currentLanguage->flag,
            ];
        }

        $available = [];
        foreach (Language::active()->ordered()->get() as $lang) {
            $available[$lang->code] = [
                'name' => $lang->name,
                'native' => $lang->native_name,
                'dir' => $lang->direction,
                'flag' => $lang->flag,
            ];
        }

        return response()->json([
            'locale' => $locale,
            'direction' => $direction,
            'language' => $langData,
            'available' => $available,
        ]);
    }
}
