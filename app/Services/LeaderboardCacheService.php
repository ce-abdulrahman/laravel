<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class LeaderboardCacheService
{
    /**
     * Get cached leaderboard ranking for a given period type.
     */
    public function getCachedRankings(string $type, string $filter = 'global'): ?array
    {
        $cacheKey = "leaderboard:rankings:{$type}:{$filter}";
        return Cache::get($cacheKey);
    }

    /**
     * Set cached leaderboard rankings.
     */
    public function setCachedRankings(string $type, string $filter, array $data, int $ttlSeconds = 600): void
    {
        $cacheKey = "leaderboard:rankings:{$type}:{$filter}";
        Cache::put($cacheKey, $data, $ttlSeconds);

        // Track cache key for clearing later
        $keys = Cache::get('leaderboard:cache_keys', []);
        if (!is_array($keys)) {
            $keys = [];
        }
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put('leaderboard:cache_keys', $keys, 86400); // Keep tracking list for 24 hours
        }
    }

    /**
     * Clear the leaderboard cache for a specific period type.
     */
    public function clearCache(string $type = null): void
    {
        $keys = Cache::get('leaderboard:cache_keys', []);
        if (!is_array($keys)) {
            $keys = [];
        }

        $remainingKeys = [];

        foreach ($keys as $key) {
            if ($type === null || str_contains($key, "leaderboard:rankings:{$type}:")) {
                Cache::forget($key);
            } else {
                $remainingKeys[] = $key;
            }
        }

        // Also clear standard/fallback keys just in case
        if ($type) {
            foreach (['global', 'country', 'province'] as $filter) {
                Cache::forget("leaderboard:rankings:{$type}:{$filter}");
            }
        } else {
            foreach (['daily', 'weekly', 'monthly', 'alltime', 'achievement', 'streak'] as $t) {
                foreach (['global', 'country', 'province'] as $filter) {
                    Cache::forget("leaderboard:rankings:{$t}:{$filter}");
                }
            }
        }

        Cache::put('leaderboard:cache_keys', $remainingKeys, 86400);
    }
}

