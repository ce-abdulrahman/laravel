<?php

namespace App\Services;

use App\Models\Ayah;
use App\Models\AyahTajweedSegment;
use App\Models\TajweedRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TajweedSegmentService
{
    /**
     * Import Tajweed segments from a JSON or CSV file content.
     *
     * @param string $content
     * @param string $format 'json'|'csv'
     * @return array{imported: int, skipped: int, errors: array<string>}
     */
    public function import(string $content, string $format): array
    {
        Log::info("Starting Tajweed Segments import. Format: {$format}");
        $rows = [];

        if (strtolower($format) === 'json') {
            $decoded = json_decode($content, true);
            if (!is_array($decoded)) {
                return [
                    'imported' => 0,
                    'skipped' => 0,
                    'errors' => ['Invalid JSON file format.'],
                ];
            }

            // Support multiple JSON shapes:
            // 1. Flat array of rows: [{ayah_id, tajweed_rule_id, matched_text, ...}, ...]
            // 2. Single nested object: {surah_id, matches: [{ayah_number, rule_slug, ...}, ...]}
            // 3. Array of nested objects: [{surah_id, matches: [...]}, ...]
            $rows = [];

            // Detect flat array of rows (first element is a row, not a nested object with 'matches')
            if (isset($decoded[0]) && !isset($decoded[0]['matches'])) {
                // Shape 1: flat array
                $rows = $decoded;
            } elseif (isset($decoded['matches'])) {
                // Shape 2: single nested object
                $rows = $decoded['matches'];
            } elseif (isset($decoded[0]['matches'])) {
                // Shape 3: array of nested objects
                foreach ($decoded as $group) {
                    if (isset($group['matches']) && is_array($group['matches'])) {
                        $rows = array_merge($rows, $group['matches']);
                    }
                }
            } else {
                // Fallback: treat as flat array
                $rows = $decoded;
            }
        } elseif (strtolower($format) === 'csv') {
            $rows = $this->parseCsv($content);
            if (empty($rows)) {
                return [
                    'imported' => 0,
                    'skipped' => 0,
                    'errors' => ['Invalid or empty CSV file.'],
                ];
            }
        } else {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ["Unsupported format: {$format}"],
            ];
        }

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        // Cache existing segments to prevent duplicate checks from performing query loops
        // Composite key: surah_id-ayah_id-tajweed_rule_id-start_index-end_index
        $existingKeys = AyahTajweedSegment::select('surah_id', 'ayah_id', 'tajweed_rule_id', 'start_index', 'end_index')
            ->get()
            ->mapWithKeys(function ($seg) {
                $key = "{$seg->surah_id}-{$seg->ayah_id}-{$seg->tajweed_rule_id}-{$seg->start_index}-{$seg->end_index}";
                return [$key => true];
            })->toArray();

        // Pre-fetch rules and ayahs to avoid N+1 queries during validation
        $validRuleIds = TajweedRule::pluck('id', 'id')->toArray();
        // slug → id map for nested JSON (e.g. rule_slug: "idhhar_halqi")
        $ruleSlugMap = TajweedRule::pluck('id', 'slug')->toArray();
        $ayahSurahMap = Ayah::pluck('surah_id', 'id')->toArray();
        // surah_id+ayah_number → ayah_id for nested JSON (e.g. ayah_number: 7 in surah 1)
        $ayahNumberMap = Ayah::select('id', 'surah_id', 'ayah_number')
            ->get()
            ->mapWithKeys(fn($a) => ["{$a->surah_id}-{$a->ayah_number}" => $a->id])
            ->toArray();

        DB::beginTransaction();
        try {
            foreach (array_values($rows) as $index => $row) {
                $rowNum = $index + 1; // always an int now

                // Normalize fields — support:
                // - Flat format:  ayah_id, tajweed_rule_id
                // - Nested format: ayah_number + surah_id, rule_slug
                $ayahId   = isset($row['ayah_id']) ? (int) $row['ayah_id'] : null;
                $ruleId   = isset($row['tajweed_rule_id']) ? (int) $row['tajweed_rule_id'] : null;

                // Resolve ayah_number + surah_id → ayah_id (nested JSON format)
                if (!$ayahId && isset($row['ayah_number'], $row['surah_id'])) {
                    $mapKey = (int)$row['surah_id'] . '-' . (int)$row['ayah_number'];
                    $ayahId = $ayahNumberMap[$mapKey] ?? null;
                }

                // Resolve rule_slug → tajweed_rule_id (nested JSON format)
                // Normalize slug: "idhhar_halqi" → "idhhar-halqi"
                if (!$ruleId && isset($row['rule_slug'])) {
                    $normalizedSlug = strtolower(str_replace('_', '-', $row['rule_slug']));
                    $ruleId = $ruleSlugMap[$normalizedSlug] ?? $ruleSlugMap[$row['rule_slug']] ?? null;
                }

                $matchedText = $row['matched_text'] ?? $row['text_segment'] ?? null;
                $startIndex  = isset($row['start_index']) ? (int) $row['start_index'] : null;
                $endIndex    = isset($row['end_index'])   ? (int) $row['end_index']   : null;
                $note        = $row['note'] ?? null;
                
                // Parse metadata
                $metadata = null;
                if (isset($row['metadata'])) {
                    if (is_array($row['metadata'])) {
                        $metadata = $row['metadata'];
                    } elseif (is_string($row['metadata']) && !empty($row['metadata'])) {
                        $metadata = json_decode($row['metadata'], true);
                    }
                }

                // Row validation
                if (!$ayahId || !$ruleId || !$matchedText) {
                    $errors[] = "Row {$rowNum}: Missing required fields (ayah_id, tajweed_rule_id, or matched_text).";
                    continue;
                }

                if (!isset($validRuleIds[$ruleId])) {
                    $errors[] = "Row {$rowNum}: Tajweed Rule ID {$ruleId} does not exist.";
                    continue;
                }

                if (!isset($ayahSurahMap[$ayahId])) {
                    $errors[] = "Row {$rowNum}: Ayah ID {$ayahId} does not exist.";
                    continue;
                }

                $resolvedSurahId = $ayahSurahMap[$ayahId];

                // Deduplication check
                $duplicateKey = "{$resolvedSurahId}-{$ayahId}-{$ruleId}-{$startIndex}-{$endIndex}";
                if (isset($existingKeys[$duplicateKey])) {
                    $skippedCount++;
                    continue;
                }

                // Create record
                AyahTajweedSegment::create([
                    'surah_id' => $resolvedSurahId,
                    'ayah_id' => $ayahId,
                    'tajweed_rule_id' => $ruleId,
                    'matched_text' => $matchedText,
                    'start_index' => $startIndex,
                    'end_index' => $endIndex,
                    'metadata' => $metadata,
                    'note' => $note,
                ]);

                // Track in our local cache to prevent duplicates in the same import file
                $existingKeys[$duplicateKey] = true;
                $importedCount++;
            }

            DB::commit();
            Log::info("Tajweed Segments import completed successfully. Imported: {$importedCount}, Skipped: {$skippedCount}, Errors: " . count($errors));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to import Tajweed segments: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Database transaction failed: ' . $e->getMessage()],
            ];
        }

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Export segments based on query filters.
     *
     * @param array<string, mixed> $filters
     * @param string $format 'json'|'csv'
     * @return string
     */
    public function export(array $filters, string $format): string
    {
        Log::info("Exporting Tajweed Segments. Format: {$format}", $filters);
        
        $query = AyahTajweedSegment::query();

        if (!empty($filters['surah_id'])) {
            $query->where('surah_id', $filters['surah_id']);
        }

        if (!empty($filters['tajweed_rule_id'])) {
            $query->where('tajweed_rule_id', $filters['tajweed_rule_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('tajweedRule', function ($q) use ($filters) {
                $q->where('tajweed_rule_category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['search'])) {
            $query->where('matched_text', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['ayah_number'])) {
            $query->whereHas('ayah', function ($q) use ($filters) {
                $q->where('ayah_number', $filters['ayah_number']);
            });
        }

        $segments = $query->orderBy('surah_id')
            ->orderBy('ayah_id')
            ->orderBy('start_index')
            ->get();

        if (strtolower($format) === 'csv') {
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, ['surah_id', 'ayah_id', 'tajweed_rule_id', 'matched_text', 'start_index', 'end_index', 'metadata', 'note']);

            foreach ($segments as $segment) {
                fputcsv($handle, [
                    $segment->surah_id,
                    $segment->ayah_id,
                    $segment->tajweed_rule_id,
                    $segment->matched_text,
                    $segment->start_index,
                    $segment->end_index,
                    $segment->metadata ? json_encode($segment->metadata, JSON_UNESCAPED_UNICODE) : '',
                    $segment->note,
                ]);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);
            return $csv;
        }

        // Default: JSON export
        $exportData = $segments->map(function ($segment) {
            return [
                'surah_id' => $segment->surah_id,
                'ayah_id' => $segment->ayah_id,
                'tajweed_rule_id' => $segment->tajweed_rule_id,
                'matched_text' => $segment->matched_text,
                'start_index' => $segment->start_index,
                'end_index' => $segment->end_index,
                'metadata' => $segment->metadata ?? (object)[],
                'note' => $segment->note,
            ];
        })->toArray();

        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Rebuild segments: Deletes all existing segments and imports the fresh set.
     *
     * @param string $content
     * @param string $format 'json'|'csv'
     * @return array{imported: int, skipped: int, errors: array<string>}
     */
    public function rebuild(string $content, string $format): array
    {
        Log::warning("Rebuilding Tajweed Segments. Deleting all existing database rows.");
        
        return DB::transaction(function () use ($content, $format) {
            AyahTajweedSegment::query()->delete();
            return $this->import($content, $format);
        });
    }

    /**
     * Parse CSV file content into an associative array.
     */
    protected function parseCsv(string $content): array
    {
        $lines = explode("\n", str_replace("\r", "", $content));
        if (empty($lines)) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        // Clean headers (remove BOM or white spaces)
        $headers = array_map(function ($h) {
            return trim($h, "\xEF\xBB\xBF ");
        }, $headers);

        $data = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $rowValues = str_getcsv($line);
            if (count($rowValues) !== count($headers)) {
                // If column lengths differ, fill or truncate
                $rowValues = array_slice(array_pad($rowValues, count($headers), null), 0, count($headers));
            }

            $data[] = array_combine($headers, $rowValues);
        }

        return $data;
    }
}
