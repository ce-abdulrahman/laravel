<?php

namespace App\Services;

use App\Models\TranslationKey;
use App\Models\UiTranslation;
use App\Models\Language;

class TranslationSearchService
{
    /**
     * Search translations semantically.
     */
    public function search(string $query): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $allKeys = TranslationKey::with('translations.language')->get();
        $queryWords = array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/', '', strtolower($query))));
        
        $results = [];

        foreach ($allKeys as $k) {
            $score = 0;
            
            // 1. Exact matches / segment matching
            if (stripos($k->key, $query) !== false) {
                $score += 2.0;
            }

            // Key tokens matching query
            $keyTokens = explode('.', strtolower($k->key));
            $keyMatches = array_intersect($queryWords, $keyTokens);
            if (!empty($keyMatches)) {
                $score += count($keyMatches) * 1.5;
            }

            // 2. English values matching query
            $englishVal = $k->translations->first(function ($t) {
                return $t->language->code === 'en';
            })?->value ?? '';

            if (!empty($englishVal)) {
                if (stripos($englishVal, $query) !== false) {
                    $score += 3.0; // strong match
                }
                
                $valWords = array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/', '', strtolower($englishVal))));
                $valMatches = array_intersect($queryWords, $valWords);
                if (!empty($valMatches)) {
                    $score += count($valMatches) * 1.0;
                }
            }

            // 3. Other languages translation values matching query
            foreach ($k->translations as $trans) {
                if ($trans->language->code !== 'en' && !empty($trans->value)) {
                    if (stripos($trans->value, $query) !== false) {
                        $score += 1.5;
                    }
                }
            }

            // 4. Boost score if key description matches
            if ($k->description && stripos($k->description, $query) !== false) {
                $score += 1.0;
            }

            // If score is high enough, include
            if ($score > 0) {
                $results[] = [
                    'key' => $k,
                    'score' => round($score, 2)
                ];
            }
        }

        // Sort descending by score
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }
}
