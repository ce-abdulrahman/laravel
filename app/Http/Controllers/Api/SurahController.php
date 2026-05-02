<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\SurahResource;
use App\Models\Surah;
use App\Services\QuranApiCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SurahController extends Controller
{
    use RespondsWithJson;

    public function index(): JsonResponse
    {
        $ttl = config('quran_api.cache_ttl', 3600);

        $data = Cache::remember(QuranApiCache::KEY_SURAHS, $ttl, function () {
            $surahs = Surah::query()
                ->active()
                ->select(['id', 'number', 'name_ar', 'name_en', 'name_ku', 'ayah_count'])
                ->orderBy('number')
                ->get();

            return SurahResource::collection($surahs)->resolve(request());
        });

        return $this->success($data);
    }
}
