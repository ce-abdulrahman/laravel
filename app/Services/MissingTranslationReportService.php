<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\DB;

class MissingTranslationReportService
{
    protected TranslationRegistryService $registryService;

    public function __construct(TranslationRegistryService $registryService)
    {
        $this->registryService = $registryService;
    }

    /**
     * Get list of keys that are missing translations in one or more active languages.
     */
    public function getMissingTranslations(): array
    {
        $activeLanguageIds = Language::active()->pluck('id')->toArray();
        $activeLanguageCount = count($activeLanguageIds);

        if ($activeLanguageCount === 0) {
            return [];
        }

        // Keys that have less completed translations than the active languages count,
        // or have empty values for active languages.
        return TranslationKey::where(function ($query) use ($activeLanguageIds, $activeLanguageCount) {
            $query->whereHas('translations', function ($sub) use ($activeLanguageIds) {
                $sub->whereIn('language_id', $activeLanguageIds)
                    ->where(function ($q) {
                        $q->whereNull('value')->orWhere('value', '');
                    });
            })
            ->orWhereDoesntHave('translations')
            ->orHas('translations', '<', $activeLanguageCount);
        })
        ->orderBy('key')
        ->get()
        ->toArray();
    }

    /**
     * Get list of keys in DB that are not found in the codebase.
     */
    public function getUnusedKeys(): array
    {
        $codebaseKeys = $this->registryService->scanCodebase();
        
        // Find keys in DB that are not in the codebase
        return TranslationKey::whereNotIn('key', $codebaseKeys)
            ->orderBy('key')
            ->get()
            ->toArray();
    }

    /**
     * Get orphan translations (translations without a matching translation key).
     */
    public function getOrphanTranslations(): array
    {
        return UiTranslation::whereDoesntHave('key')
            ->with('language')
            ->get()
            ->toArray();
    }

    /**
     * Get general report statistics.
     */
    public function getReportStats(): array
    {
        $activeLanguages = Language::active()->get();
        $activeCodes = $activeLanguages->pluck('code')->toArray();
        $activeLanguageIds = $activeLanguages->pluck('id')->toArray();
        $activeCount = count($activeLanguageIds);

        $totalKeys = TranslationKey::count();

        // Expected translation units = total keys * active languages count
        $expectedUnits = $totalKeys * $activeCount;

        // Completed translation units = count of non-empty ui_translations for active languages
        $completedUnits = UiTranslation::whereIn('language_id', $activeLanguageIds)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();

        $missingUnits = max(0, $expectedUnits - $completedUnits);
        $coverage = $expectedUnits > 0 ? round(($completedUnits / $expectedUnits) * 100, 2) : 100.00;

        // Count of fully translated keys (translated in all active languages)
        $fullyTranslatedKeysCount = TranslationKey::whereDoesntHave('translations', function ($query) use ($activeLanguageIds) {
            $query->whereIn('language_id', $activeLanguageIds)
                  ->where(function ($q) {
                      $q->whereNull('value')->orWhere('value', '');
                  });
        })
        ->whereHas('translations', null, '>=', $activeCount)
        ->count();

        return [
            'total_keys' => $totalKeys,
            'completed_units' => $completedUnits,
            'expected_units' => $expectedUnits,
            'missing_translations' => $missingUnits,
            'coverage_percentage' => $coverage,
            'fully_translated_keys' => $fullyTranslatedKeysCount,
            'active_languages_count' => $activeCount,
            'active_locales' => $activeCodes,
        ];
    }
}
