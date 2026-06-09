<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MissingTranslationAnalyticsService
{
    /**
     * Fetch and rank frequently missing translations.
     */
    public function getMissingAnalysis(): array
    {
        $missing = DB::table('translation_analytics')
            ->select('key_name', DB::raw('SUM(hit_count) as total_hits'), DB::raw('MAX(page) as sample_page'))
            ->where('is_missing', true)
            ->groupBy('key_name')
            ->orderBy('total_hits', 'desc')
            ->get();

        return $missing->map(function ($item) {
            return [
                'key' => $item->key_name,
                'hits' => $item->total_hits,
                'page' => $item->sample_page ?? 'N/A',
                'priority_score' => $this->getPriorityScore($item->total_hits)
            ];
        })->toArray();
    }

    /**
     * Priority scoring calculation.
     */
    public function getPriorityScore(int $hits): int
    {
        return (int) round($hits * 1.5);
    }
}
