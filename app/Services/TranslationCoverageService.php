<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\DB;

class TranslationCoverageService
{
    protected MissingTranslationReportService $reportService;

    public function __construct(MissingTranslationReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get detailed coverage breakdown.
     */
    public function getCoverageData(): array
    {
        $languages = Language::active()->get();
        $totalKeys = TranslationKey::count();

        // 1. Coverage per Language
        $languageCoverage = [];
        foreach ($languages as $lang) {
            $completed = UiTranslation::where('language_id', $lang->id)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->count();
            
            $pct = $totalKeys > 0 ? round(($completed / $totalKeys) * 100, 2) : 100.00;
            $languageCoverage[] = [
                'code' => $lang->code,
                'name' => $lang->name,
                'completed' => $completed,
                'total' => $totalKeys,
                'percentage' => $pct,
            ];
        }

        // 2. Coverage per Group (translation_keys.group)
        $groups = TranslationKey::select('group')->distinct()->pluck('group')->toArray();
        $groupCoverage = [];
        $activeLanguageIds = $languages->pluck('id')->toArray();
        $activeLanguagesCount = count($activeLanguageIds);

        foreach ($groups as $group) {
            $groupKeysCount = TranslationKey::where('group', $group)->count();
            $expected = $groupKeysCount * $activeLanguagesCount;

            $completed = UiTranslation::whereIn('language_id', $activeLanguageIds)
                ->whereHas('key', function ($q) use ($group) {
                    $q->where('group', $group);
                })
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->count();

            $pct = $expected > 0 ? round(($completed / $expected) * 100, 2) : 100.00;
            $groupCoverage[] = [
                'group' => $group,
                'keys_count' => $groupKeysCount,
                'completed_units' => $completed,
                'expected_units' => $expected,
                'percentage' => $pct,
            ];
        }

        // 3. Coverage per Module (Database dynamic translatable models)
        $moduleCoverage = $this->getModuleCoverage($languages);

        return [
            'languages' => $languageCoverage,
            'groups' => $groupCoverage,
            'modules' => $moduleCoverage,
            'missing_keys' => $this->reportService->getMissingTranslations(),
            'unused_keys' => $this->reportService->getUnusedKeys(),
            'orphan_translations' => $this->reportService->getOrphanTranslations(),
            'stats' => $this->reportService->getReportStats(),
            'dynamic_warnings' => \App\Models\DynamicTranslationWarning::orderBy('file_path')->orderBy('line_number')->get(),
        ];
    }

    /**
     * Compute coverage for database modules.
     */
    protected function getModuleCoverage($languages): array
    {
        $activeCodes = $languages->pluck('code')->toArray();
        $activeLangIds = $languages->pluck('id')->toArray();
        $activeCount = count($activeCodes);

        $modules = [
            'Surah' => [
                'model' => \App\Models\Surah::class,
                'trans_model' => \App\Models\SurahTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'name',
            ],
            'HadithCategory' => [
                'model' => \App\Models\HadithCategory::class,
                'trans_model' => \App\Models\HadithCategoryTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'name',
            ],
            'Hadith' => [
                'model' => \App\Models\Hadith::class,
                'trans_model' => \App\Models\HadithTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'translation',
            ],
            'AdhkarCategory' => [
                'model' => \App\Models\AdhkarCategory::class,
                'trans_model' => \App\Models\AdhkarCategoryTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'name',
            ],
            'Adhkar' => [
                'model' => \App\Models\Adhkar::class,
                'trans_model' => \App\Models\AdhkarTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'translation',
            ],
            'TajweedRuleCategory' => [
                'model' => \App\Models\TajweedRuleCategory::class,
                'trans_model' => \App\Models\TajweedRuleCategoryTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'name',
            ],
            'TajweedRule' => [
                'model' => \App\Models\TajweedRule::class,
                'trans_model' => \App\Models\TajweedRuleTranslation::class,
                'locale_col' => 'locale',
                'check_col' => 'name',
            ],
            'Ayah' => [
                'model' => \App\Models\Ayah::class,
                'trans_model' => \App\Models\Translation::class,
                'locale_col' => 'language_code',
                'check_col' => 'content',
            ],
            'UI (General)' => [
                'model' => \App\Models\TranslationKey::class,
                'trans_model' => \App\Models\UiTranslation::class,
                'locale_col' => 'language_id',
                'check_col' => 'value',
            ]
        ];

        $coverage = [];

        foreach ($modules as $name => $conf) {
            try {
                $baseCount = $conf['model']::count();
                $expected = $baseCount * $activeCount;

                if ($name === 'UI (General)') {
                    $completed = $conf['trans_model']::whereIn('language_id', $activeLangIds)
                        ->whereNotNull('value')
                        ->where('value', '!=', '')
                        ->count();
                } else {
                    $localeCol = $conf['locale_col'];
                    $checkCol = $conf['check_col'];
                    
                    if ($localeCol === 'language_code') {
                        $completed = $conf['trans_model']::whereIn('language_code', $activeCodes)
                            ->whereNotNull($checkCol)
                            ->where($checkCol, '!=', '')
                            ->count();
                    } else {
                        $completed = $conf['trans_model']::whereIn('locale', $activeCodes)
                            ->whereNotNull($checkCol)
                            ->where($checkCol, '!=', '')
                            ->count();
                    }
                }

                $pct = $expected > 0 ? round(($completed / $expected) * 100, 2) : 100.00;

                $coverage[] = [
                    'name' => $name,
                    'items_count' => $baseCount,
                    'completed_units' => $completed,
                    'expected_units' => $expected,
                    'percentage' => $pct,
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $coverage;
    }
}
