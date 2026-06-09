<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\File;

class TranslationConsistencyService
{
    /**
     * Audit and detect inconsistent states in localization system.
     */
    public function checkConsistency(): array
    {
        $duplicates = [];
        $inconsistentKeys = [];
        $unusedKeys = [];
        $missingGroups = [];

        $allKeys = TranslationKey::all();
        $languages = Language::all();

        // 1. Detect duplicates (same value for different keys in the same language)
        foreach ($languages as $lang) {
            $translations = UiTranslation::where('language_id', $lang->id)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->whereRaw('length(value) > 4') // ignore short common terms
                ->get()
                ->groupBy('value');

            foreach ($translations as $val => $records) {
                if ($records->count() > 1) {
                    $keysAssociated = [];
                    foreach ($records as $r) {
                        $keyName = $allKeys->where('id', $r->translation_key_id)->first()?->key;
                        if ($keyName) {
                            $keysAssociated[] = $keyName;
                        }
                    }
                    if (count($keysAssociated) > 1) {
                        $duplicates[$lang->code][] = [
                            'value' => $val,
                            'keys' => $keysAssociated,
                        ];
                    }
                }
            }
        }

        // 2. Naming consistency check
        foreach ($allKeys as $k) {
            $keyStr = $k->key;
            if (empty($k->group) || $k->group === 'general') {
                $missingGroups[] = $keyStr;
            }

            // Checks naming structure
            if (preg_match('/[A-Z]/', $keyStr)) {
                $inconsistentKeys[] = [
                    'key' => $keyStr,
                    'reason' => 'Contains uppercase letters (should be lowercase).'
                ];
            } elseif (strpos($keyStr, '.') === false) {
                $inconsistentKeys[] = [
                    'key' => $keyStr,
                    'reason' => 'Missing dot prefix grouping namespace.'
                ];
            }
        }

        // 3. Scan code references to find unused keys
        $codeKeys = $this->scanCodebaseForKeys();
        foreach ($allKeys as $k) {
            if (!in_array($k->key, $codeKeys, true)) {
                $unusedKeys[] = $k->key;
            }
        }

        return [
            'duplicates' => $duplicates,
            'inconsistent_keys' => $inconsistentKeys,
            'unused_keys' => $unusedKeys,
            'missing_groups' => $missingGroups,
        ];
    }

    /**
     * Scan Blade and PHP files for translation keys.
     */
    protected function scanCodebaseForKeys(): array
    {
        $keys = [];
        $patterns = [
            "/t\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",
            "/__\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",
            "/@lang\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",
            "/trans\(\s*['\"]([a-zA-Z0-9_\-\.]+)['\"]/i",
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
