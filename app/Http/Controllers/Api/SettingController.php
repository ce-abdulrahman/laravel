<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Models\SettingEntry;
use App\Services\QuranApiCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    use RespondsWithJson;

    /**
     * Flat key => value map for the mobile app (defaults from DB or empty object).
     */
    public function index(): JsonResponse
    {
        $ttl = config('quran_api.cache_ttl', 3600);

        $data = Cache::remember(QuranApiCache::KEY_SETTINGS, $ttl, function () {
            return SettingEntry::query()
                ->select(['key', 'value'])
                ->orderBy('key')
                ->pluck('value', 'key')
                ->all();
        });

        return $this->success($data);
    }
}
