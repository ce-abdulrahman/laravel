<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\DB;

class TranslationImportExportService
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Export all translations for a given locale to JSON array.
     */
    public function exportToJson(string $locale): array
    {
        $language = Language::where('code', $locale)->first();
        if (!$language) {
            return [];
        }

        return DB::table('translation_keys')
            ->join('ui_translations', 'ui_translations.translation_key_id', '=', 'translation_keys.id')
            ->where('ui_translations.language_id', $language->id)
            ->whereNotNull('ui_translations.value')
            ->pluck('ui_translations.value', 'translation_keys.key')
            ->toArray();
    }

    /**
     * Export all translations for a given locale to CSV.
     */
    public function exportToCsv(string $locale): string
    {
        $language = Language::where('code', $locale)->first();
        if (!$language) {
            return "key,value,group\n";
        }

        $records = DB::table('translation_keys')
            ->leftJoin('ui_translations', function ($join) use ($language) {
                $join->on('ui_translations.translation_key_id', '=', 'translation_keys.id')
                     ->where('ui_translations.language_id', '=', $language->id);
            })
            ->select('translation_keys.key', 'ui_translations.value', 'translation_keys.group')
            ->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['key', 'value', 'group']);

        foreach ($records as $record) {
            fputcsv($output, [$record->key, $record->value ?? '', $record->group]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Import translations from a JSON string.
     */
    public function importFromJson(string $jsonContents, string $locale, bool $createKeys = false): array
    {
        $language = Language::where('code', $locale)->first();
        if (!$language) {
            throw new \InvalidArgumentException("Language locale '{$locale}' not found.");
        }

        $data = json_decode($jsonContents, true);
        if (!is_array($data)) {
            throw new \InvalidArgumentException("Invalid JSON format.");
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        UiTranslation::$currentChangeSource = 'import';

        // Chunk data to process in separate transactions
        $chunks = array_chunk(array_keys($data), 100);
        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $data, $language, $createKeys, &$imported, &$updated, &$skipped) {
                foreach ($chunk as $key) {
                    $value = $data[$key];

                    $keyRecord = TranslationKey::where('key', $key)->first();
                    if (!$keyRecord) {
                        if ($createKeys) {
                            $parts = explode('.', $key);
                            $group = count($parts) > 1 ? $parts[0] : 'general';
                            $keyRecord = TranslationKey::create([
                                'key' => $key,
                                'group' => $group,
                            ]);
                            $imported++;
                        } else {
                            $skipped++;
                            continue;
                        }
                    }

                    $translation = UiTranslation::where('translation_key_id', $keyRecord->id)
                        ->where('language_id', $language->id)
                        ->first();

                    if ($translation) {
                        if ($translation->value !== $value) {
                            $translation->update([
                                'value' => $value !== '' ? $value : null,
                                'is_auto_generated' => false,
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        UiTranslation::create([
                            'translation_key_id' => $keyRecord->id,
                            'language_id' => $language->id,
                            'value' => $value !== '' ? $value : null,
                            'is_auto_generated' => false,
                        ]);
                        $imported++;
                    }
                }
            });
        }

        UiTranslation::$currentChangeSource = 'manual';
        $this->translationService->clearCache($locale);

        return ['imported' => $imported, 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * Import translations from a CSV string.
     */
    public function importFromCsv(string $csvContents, string $locale, bool $createKeys = false): array
    {
        $language = Language::where('code', $locale)->first();
        if (!$language) {
            throw new \InvalidArgumentException("Language locale '{$locale}' not found.");
        }

        $lines = explode("\n", $csvContents);
        if (empty($lines)) {
            return ['imported' => 0, 'updated' => 0, 'skipped' => 0];
        }

        // Parse header
        $header = str_getcsv(array_shift($lines));
        $keyIndex = array_search('key', $header);
        $valueIndex = array_search('value', $header);
        $groupIndex = array_search('group', $header);

        if ($keyIndex === false || $valueIndex === false) {
            throw new \InvalidArgumentException("CSV header must contain 'key' and 'value' columns.");
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        UiTranslation::$currentChangeSource = 'import';

        $chunks = array_chunk($lines, 100);
        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk, $keyIndex, $valueIndex, $groupIndex, $language, $createKeys, &$imported, &$updated, &$skipped) {
                foreach ($chunk as $line) {
                    if (empty(trim($line))) {
                        continue;
                    }
                    $row = str_getcsv($line);
                    if (count($row) <= max($keyIndex, $valueIndex)) {
                        $skipped++;
                        continue;
                    }

                    $key = $row[$keyIndex];
                    $value = $row[$valueIndex];
                    $group = $groupIndex !== false ? ($row[$groupIndex] ?? 'general') : 'general';

                    if (empty($key)) {
                        $skipped++;
                        continue;
                    }

                    $keyRecord = TranslationKey::where('key', $key)->first();
                    if (!$keyRecord) {
                        if ($createKeys) {
                            $keyRecord = TranslationKey::create([
                                'key' => $key,
                                'group' => $group ?: 'general',
                            ]);
                            $imported++;
                        } else {
                            $skipped++;
                            continue;
                        }
                    }

                    $translation = UiTranslation::where('translation_key_id', $keyRecord->id)
                        ->where('language_id', $language->id)
                        ->first();

                    if ($translation) {
                        if ($translation->value !== $value) {
                            $translation->update([
                                'value' => $value !== '' ? $value : null,
                                'is_auto_generated' => false,
                            ]);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        UiTranslation::create([
                            'translation_key_id' => $keyRecord->id,
                            'language_id' => $language->id,
                            'value' => $value !== '' ? $value : null,
                            'is_auto_generated' => false,
                        ]);
                        $imported++;
                    }
                }
            });
        }

        UiTranslation::$currentChangeSource = 'manual';
        $this->translationService->clearCache($locale);

        return ['imported' => $imported, 'updated' => $updated, 'skipped' => $skipped];
    }
}
