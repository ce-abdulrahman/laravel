<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TranslationIntegrityService
{
    /**
     * Run full audit and returns statistics and alerts.
     */
    public function runFullAudit(): array
    {
        $languages = Language::all();
        $totalKeysCount = TranslationKey::count();
        
        $coverage = [];
        $missing = [];
        $empty = [];
        $duplicates = [];
        $orphans = [];

        // 1. Calculate coverage and find empty/missing translations per language
        foreach ($languages as $lang) {
            $translatedCount = UiTranslation::where('language_id', $lang->id)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->count();
                
            $coveragePct = $totalKeysCount > 0 ? round(($translatedCount / $totalKeysCount) * 100, 2) : 100;
            
            $coverage[$lang->code] = [
                'name' => $lang->name,
                'total_keys' => $totalKeysCount,
                'translated' => $translatedCount,
                'coverage_percentage' => $coveragePct,
            ];

            // Empty values (records exist but value is blank)
            $emptyTranslations = UiTranslation::with('key')
                ->where('language_id', $lang->id)
                ->where(function($query) {
                    $query->whereNull('value')->orWhere('value', '');
                })
                ->get()
                ->map(fn($t) => $t->key->key ?? 'unknown')
                ->toArray();

            if (!empty($emptyTranslations)) {
                $empty[$lang->code] = $emptyTranslations;
            }

            // Missing values (no record exists at all)
            $existingKeyIds = UiTranslation::where('language_id', $lang->id)
                ->pluck('translation_key_id')
                ->toArray();
                
            $missingKeys = TranslationKey::whereNotIn('id', $existingKeyIds)
                ->pluck('key')
                ->toArray();
                
            if (!empty($missingKeys)) {
                $missing[$lang->code] = $missingKeys;
            }

            // Find duplicate values (same translation value for different keys)
            $dupValues = DB::table('ui_translations')
                ->select('value', DB::raw('count(*) as count'))
                ->where('language_id', $lang->id)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->whereRaw('length(value) > 4') // ignore short words like 'Yes', 'No', 'Save'
                ->groupBy('value')
                ->having('count', '>', 1)
                ->pluck('value')
                ->toArray();

            foreach ($dupValues as $val) {
                $keys = UiTranslation::join('translation_keys', 'ui_translations.translation_key_id', '=', 'translation_keys.id')
                    ->where('ui_translations.language_id', $lang->id)
                    ->where('ui_translations.value', $val)
                    ->pluck('translation_keys.key')
                    ->toArray();

                $duplicates[$lang->code][] = [
                    'value' => $val,
                    'keys' => $keys,
                ];
            }
        }

        // 2. Scan code files to find unused/orphan database keys (keys in DB but never used in code)
        // and missing keys (keys in code but not in DB)
        $codeKeys = $this->scanCodebaseForKeys();
        
        $dbKeys = TranslationKey::pluck('key')->toArray();
        
        // Orphans: in database, but not found in code scan
        $orphanKeys = array_diff($dbKeys, $codeKeys);
        // Clean out empty keys
        $orphans = array_values(array_filter($orphanKeys));

        // Missing from DB: found in code scan, but not in database
        $missingFromDb = array_diff($codeKeys, $dbKeys);
        $missingFromDb = array_values(array_filter($missingFromDb));

        return [
            'coverage' => $coverage,
            'missing' => $missing,
            'empty' => $empty,
            'duplicates' => $duplicates,
            'orphans' => $orphans, // keys in DB but not in code
            'missing_from_db' => $missingFromDb, // keys in code but not in DB
        ];
    }

    /**
     * Scan Blade and PHP files for helper calls like t('key.subkey')
     */
    protected function scanCodebaseForKeys(): array
    {
        $keys = [];
        $patterns = [
            "/t\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",        // t('some.key')
            "/__\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",       // __('some.key')
            "/@lang\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",     // @lang('some.key')
            "/trans\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",    // trans('some.key')
        ];

        $directories = [
            app_path(),
            resource_path('views'),
        ];

        foreach ($directories as $dir) {
            if (!File::isDirectory($dir)) {
                continue;
            }

            $files = File::allFiles($dir);
            foreach ($files as $file) {
                $ext = $file->getExtension();
                if ($ext !== 'php' && $ext !== 'blade.php') {
                    continue;
                }

                $content = File::get($file->getPathname());
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $match) {
                            $keys[] = $match;
                        }
                    }
                }
            }
        }

        return array_unique($keys);
    }
}
