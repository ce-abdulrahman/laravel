<?php

namespace App\Services;

use App\Models\Language;
use App\Models\TranslationKey;
use Illuminate\Support\Facades\DB;

class TranslationMetricsService
{
    /**
     * Compute daily translation summaries.
     */
    public function computeDailyMetrics(): void
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $summary = DB::table('translation_analytics')
            ->select(
                'locale',
                DB::raw('SUM(hit_count) as total_requests'),
                DB::raw('SUM(CASE WHEN is_missing = 1 THEN hit_count ELSE 0 END) as missing_count')
            )
            ->whereDate('created_at', $yesterday)
            ->groupBy('locale')
            ->get();

        foreach ($summary as $sum) {
            // Find top key requested yesterday for this locale
            $topKey = DB::table('translation_analytics')
                ->select('translation_key_id', DB::raw('SUM(hit_count) as hits'))
                ->whereDate('created_at', $yesterday)
                ->where('locale', $sum->locale)
                ->whereNotNull('translation_key_id')
                ->groupBy('translation_key_id')
                ->orderBy('hits', 'desc')
                ->first();

            DB::table('translation_usage_summary')->insert([
                'date' => $yesterday,
                'locale' => $sum->locale,
                'total_requests' => $sum->total_requests,
                'missing_keys_count' => $sum->missing_count,
                'top_key_id' => $topKey?->translation_key_id ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Generate metrics data for the dashboard charts/heatmaps.
     */
    public function generateHeatmapData(): array
    {
        // Top 10 keys
        $topKeys = DB::table('translation_analytics')
            ->select('key_name', DB::raw('SUM(hit_count) as count'))
            ->groupBy('key_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // Top 5 modules (prefix segments before first dot)
        $modules = [];
        $rawHits = DB::table('translation_analytics')
            ->select('key_name', 'hit_count')
            ->get();

        foreach ($rawHits as $row) {
            $parts = explode('.', $row->key_name);
            $mod = count($parts) > 1 ? $parts[0] : 'general';
            if (!isset($modules[$mod])) {
                $modules[$mod] = 0;
            }
            $modules[$mod] += $row->hit_count;
        }

        arsort($modules);
        $topModules = [];
        foreach (array_slice($modules, 0, 5, true) as $name => $count) {
            $topModules[] = ['module' => $name, 'count' => $count];
        }

        return [
            'top_keys' => $topKeys,
            'top_modules' => $topModules,
        ];
    }

    /**
     * Fetch percentage share of language queries.
     */
    public function languageDistribution(): array
    {
        $distribution = DB::table('translation_analytics')
            ->select('locale', DB::raw('SUM(hit_count) as count'))
            ->groupBy('locale')
            ->get();

        $total = $distribution->sum('count');
        
        return $distribution->map(function ($item) use ($total) {
            return [
                'locale' => $item->locale,
                'count' => $item->count,
                'percentage' => $total > 0 ? round(($item->count / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }
}
