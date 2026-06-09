<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use App\Models\UiTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationSemanticService
{
    /**
     * Parse a translation key into structured components.
     */
    public function parseKey(string $key): array
    {
        $parts = explode('.', $key);
        if (count($parts) === 1) {
            return [
                'module' => 'general',
                'context' => 'general',
                'element' => $parts[0]
            ];
        }
        
        if (count($parts) === 2) {
            return [
                'module' => $parts[0],
                'context' => $parts[0],
                'element' => $parts[1]
            ];
        }
        
        return [
            'module' => $parts[0],
            'context' => implode('.', array_slice($parts, 1, -1)),
            'element' => end($parts)
        ];
    }

    /**
     * Find similar translation keys.
     */
    public function findSimilarKeys(string $key, float $threshold = 0.4): array
    {
        $allKeys = TranslationKey::all();
        $targetRecord = $allKeys->where('key', $key)->first();
        $targetEnglish = '';

        if ($targetRecord) {
            $defaultLang = Language::where('is_default', true)->first() ?? Language::first();
            if ($defaultLang) {
                $targetEnglish = UiTranslation::where('translation_key_id', $targetRecord->id)
                    ->where('language_id', $defaultLang->id)
                    ->value('value') ?? '';
            }
        }

        $similar = [];

        foreach ($allKeys as $item) {
            if ($item->key === $key) {
                continue;
            }

            $score = $this->calculateSimilarityScore($key, $item->key, $targetEnglish, $item->id);

            if ($score >= $threshold) {
                $similar[] = [
                    'id' => $item->id,
                    'key' => $item->key,
                    'score' => round($score, 2),
                ];
            }
        }

        // Sort descending by score
        usort($similar, fn($a, $b) => $b['score'] <=> $a['score']);

        return $similar;
    }

    /**
     * Calculate score between two keys.
     */
    protected function calculateSimilarityScore(string $key1, string $key2, string $val1, int $keyId2): float
    {
        // 1. Jaccard on Key Tokens
        $tokens1 = explode('.', strtolower($key1));
        $tokens2 = explode('.', strtolower($key2));
        
        $intersection = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));
        
        $keyJaccard = count($union) > 0 ? count($intersection) / count($union) : 0;

        // 2. Levenshtein on Keys
        $maxLength = max(strlen($key1), strlen($key2));
        $levDistance = levenshtein(strtolower($key1), strtolower($key2));
        $levScore = $maxLength > 0 ? 1 - ($levDistance / $maxLength) : 0;

        // 3. Jaccard on English Values
        $valJaccard = 0;
        if (!empty($val1)) {
            $defaultLang = Language::where('is_default', true)->first() ?? Language::first();
            if ($defaultLang) {
                $val2 = UiTranslation::where('translation_key_id', $keyId2)
                    ->where('language_id', $defaultLang->id)
                    ->value('value') ?? '';

                if (!empty($val2)) {
                    $words1 = array_filter(explode(' ', preg_replace('/[^a-z0-9]/', '', strtolower($val1))));
                    $words2 = array_filter(explode(' ', preg_replace('/[^a-z0-9]/', '', strtolower($val2))));
                    
                    $valIntersection = array_intersect($words1, $words2);
                    $valUnion = array_unique(array_merge($words1, $words2));
                    
                    $valJaccard = count($valUnion) > 0 ? count($valIntersection) / count($valUnion) : 0;
                }
            }
        }

        // Combine weights
        return ($keyJaccard * 0.4) + ($levScore * 0.3) + ($valJaccard * 0.3);
    }
}
