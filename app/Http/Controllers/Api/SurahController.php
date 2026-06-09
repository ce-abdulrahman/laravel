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
        $locale = \App\Helpers\LanguageHelper::resolveLocale(request());
        app()->setLocale($locale);

        $cacheKey = QuranApiCache::getSurahsKey($locale);

        $data = Cache::remember($cacheKey, $ttl, function () {
            $surahs = Surah::query()
                ->active()
                ->orderBy('number')
                ->get();

            return SurahResource::collection($surahs)->resolve(request());
        });

        if (request()->has('fields')) {
            $fields = explode(',', request()->query('fields'));
            $data = collect($data)->map(function ($item) use ($fields) {
                return array_intersect_key((array)$item, array_flip($fields));
            })->all();
        }

        return $this->success($data);
    }
}
