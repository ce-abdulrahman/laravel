<?php

namespace App\Services;

use App\Models\TranslationKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TranslationAnalyticsService
{
    protected string $cacheKey = 'translation_analytics_buffer';

    /**
     * Log a translation hit in memory.
     */
    public function logHit(string $key, string $locale, bool $isMissing = false): void
    {
        try {
            $buffer = Cache::get($this->cacheKey, []);
            
            $buffer[] = [
                'key_name' => $key,
                'locale' => $locale,
                'user_id' => auth()->id(),
                'page' => request()->isMethod('get') ? request()->path() : null,
                'is_missing' => $isMissing,
                'timestamp' => time(),
            ];

            Cache::put($this->cacheKey, $buffer, 3600);
        } catch (\Exception $e) {
            Log::warning("Failed to log translation hit: " . $e->getMessage());
        }
    }

    /**
     * Flush memory cache hits buffer to the database with pre-aggregation.
     */
    public function flush(): void
    {
        try {
            $buffer = Cache::get($this->cacheKey, []);
            if (empty($buffer)) {
                return;
            }

            // Clear cache buffer first to avoid double processing
            Cache::forget($this->cacheKey);

            // Group/aggregate hits to reduce database row insertions
            $aggregated = [];
            foreach ($buffer as $hit) {
                $uniqueStr = "{$hit['key_name']}|{$hit['locale']}|{$hit['page']}|{$hit['user_id']}|{$hit['is_missing']}";
                if (!isset($aggregated[$uniqueStr])) {
                    $aggregated[$uniqueStr] = [
                        'key_name' => $hit['key_name'],
                        'locale' => $hit['locale'],
                        'page' => $hit['page'],
                        'user_id' => $hit['user_id'],
                        'is_missing' => $hit['is_missing'],
                        'hit_count' => 0,
                    ];
                }
                $aggregated[$uniqueStr]['hit_count']++;
            }

            // Resolve keys matching in database
            $keysList = TranslationKey::whereIn('key', array_column($aggregated, 'key_name'))
                ->pluck('id', 'key')
                ->toArray();

            $today = date('Y-m-d');

            DB::transaction(function () use ($aggregated, $keysList, $today) {
                foreach ($aggregated as $agg) {
                    $keyId = $keysList[$agg['key_name']] ?? null;

                    // Query if an identical record was created today
                    $existing = DB::table('translation_analytics')
                        ->where('key_name', $agg['key_name'])
                        ->where('locale', $agg['locale'])
                        ->where('is_missing', $agg['is_missing'])
                        ->where(function($q) use ($agg) {
                            if ($agg['page'] === null) {
                                $q->whereNull('page');
                            } else {
                                $q->where('page', $agg['page']);
                            }
                        })
                        ->where(function($q) use ($agg) {
                            if ($agg['user_id'] === null) {
                                $q->whereNull('user_id');
                            } else {
                                $q->where('user_id', $agg['user_id']);
                            }
                        })
                        ->whereDate('created_at', $today)
                        ->first();

                    if ($existing) {
                        DB::table('translation_analytics')
                            ->where('id', $existing->id)
                            ->increment('hit_count', $agg['hit_count'], ['updated_at' => now()]);
                    } else {
                        DB::table('translation_analytics')->insert([
                            'translation_key_id' => $keyId,
                            'key_name' => $agg['key_name'],
                            'locale' => $agg['locale'],
                            'user_id' => $agg['user_id'],
                            'page' => $agg['page'],
                            'hit_count' => $agg['hit_count'],
                            'is_missing' => $agg['is_missing'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to flush translation analytics buffer: " . $e->getMessage());
        }
    }
}
