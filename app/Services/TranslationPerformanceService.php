<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class TranslationPerformanceService
{
    protected string $cacheKey = 'translation_performance_metrics';

    /**
     * Log lookup duration and fallback telemetry.
     */
    public function logPerformance(float $lookupTimeMs, bool $cacheHit, bool $dbFallback, bool $aiFallback): void
    {
        try {
            $metrics = Cache::get($this->cacheKey, []);
            
            $metrics[] = [
                'time_ms' => $lookupTimeMs,
                'cache_hit' => $cacheHit,
                'db_fallback' => $dbFallback,
                'ai_fallback' => $aiFallback,
            ];

            // Slice to avoid infinite growth (keep last 1000 items)
            if (count($metrics) > 1000) {
                $metrics = array_slice($metrics, -1000);
            }

            Cache::put($this->cacheKey, $metrics, 86400); // cache for 1 day
        } catch (\Exception $e) {
            // Silence exceptions to avoid breaking requests
        }
    }

    /**
     * Compute performance summaries from telemetry cache.
     */
    public function getPerformanceStats(): array
    {
        $metrics = Cache::get($this->cacheKey, []);
        $total = count($metrics);

        if ($total === 0) {
            return [
                'avg_lookup_ms' => 0.0,
                'cache_hit_rate' => 100.0,
                'db_fallback_rate' => 0.0,
                'ai_usage_rate' => 0.0,
            ];
        }

        $sumTime = 0;
        $cacheHits = 0;
        $dbLookups = 0;
        $aiLookups = 0;

        foreach ($metrics as $m) {
            $sumTime += $m['time_ms'];
            if ($m['cache_hit']) $cacheHits++;
            if ($m['db_fallback']) $dbLookups++;
            if ($m['ai_fallback']) $aiLookups++;
        }

        return [
            'avg_lookup_ms' => round($sumTime / $total, 3),
            'cache_hit_rate' => round(($cacheHits / $total) * 100, 2),
            'db_fallback_rate' => round(($dbLookups / $total) * 100, 2),
            'ai_usage_rate' => round(($aiLookups / $total) * 100, 2),
        ];
    }
}
