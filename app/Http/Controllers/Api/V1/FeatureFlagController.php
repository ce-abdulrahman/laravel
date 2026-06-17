<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FeatureFlagController extends Controller
{
    /**
     * Return all active feature flags for the requesting client.
     *
     * Supports ETag-based caching: if the client sends a matching
     * If-None-Match header, returns HTTP 304 with no body.
     *
     * Guest users receive flags based on rollout_percentage only.
     * Authenticated users additionally receive per-user overrides.
     */
    public function index(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        $userId  = $request->user()?->id;
        $platform = $request->header('X-App-Platform', 'all');      // 'android' | 'ios'
        $appVersion = $request->header('X-App-Version', '1.0.0');   // semver string

        // Cache flags for 5 minutes per user context
        $cacheKey = "feature_flags_{$userId}_{$platform}";
        $flags    = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $platform, $appVersion) {
            return $this->resolveFlags($userId, $platform, $appVersion);
        });

        // ETag based on flag values hash
        $etag = md5(json_encode($flags));

        if ($request->header('If-None-Match') === $etag) {
            return response()->noContent(304);
        }

        return response()->json([
            'flags'        => $flags,
            'etag'         => $etag,
            'cached_until' => now()->addMinutes(5)->toIso8601String(),
        ])->header('ETag', $etag)
          ->header('Cache-Control', 'private, max-age=300');
    }

    /**
     * Resolve which flags are enabled for a given user/platform/version.
     */
    private function resolveFlags(?int $userId, string $platform, string $appVersion): array
    {
        $flags = FeatureFlag::query()
            ->where('is_enabled', true)
            ->where(function ($q) use ($platform) {
                $q->where('platform', 'all')
                  ->orWhere('platform', $platform);
            })
            ->get();

        $result = [];

        foreach ($flags as $flag) {
            // Version constraint check
            if ($flag->min_app_version && version_compare($appVersion, $flag->min_app_version, '<')) {
                $result[$flag->key] = false;
                continue;
            }
            if ($flag->max_app_version && version_compare($appVersion, $flag->max_app_version, '>')) {
                $result[$flag->key] = false;
                continue;
            }

            // Gradual rollout: deterministic per user/device
            if ($flag->rollout_percentage < 100) {
                $seed   = $userId ? ($userId % 100) : (crc32(request()->ip()) % 100);
                $enabled = $seed < $flag->rollout_percentage;
            } else {
                $enabled = true;
            }

            $result[$flag->key] = $enabled;
        }

        // Apply per-user overrides (authenticated users only)
        if ($userId) {
            $overrides = \App\Models\UserFeatureOverride::where('user_id', $userId)
                ->get()
                ->keyBy('flag_key');

            foreach ($overrides as $key => $override) {
                $result[$key] = $override->is_enabled;
            }
        }

        // Return all known flags (disabled ones default to true for graceful degradation)
        $allKnownKeys = [
            'memorization_module', 'tasbih_leaderboard', 'audio_download',
            'tajweed_module', 'hadith_module', 'statistics_module',
            'khatm_tracker', 'fingerprint_counter', 'offline_packages',
            'achievements_module',
        ];

        foreach ($allKnownKeys as $key) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = true; // default to enabled if not in DB
            }
        }

        return $result;
    }
}
