<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\AyahResource;
use App\Http\Resources\SurahResource;
use App\Models\Surah;
use App\Services\QuranApiCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AyahController extends Controller
{
    use RespondsWithJson;

    public function bySurah(int $id): JsonResponse
    {
        if ($id < 1) {
            return $this->error('Invalid surah id.', 422);
        }

        $surahExists = Surah::query()->active()->where('number', $id)->exists();
        if (! $surahExists) {
            return $this->error('Surah not found.', 404);
        }

        $ttl = config('quran_api.cache_ttl', 3600);
        $locale = \App\Helpers\LanguageHelper::resolveLocale(request());
        app()->setLocale($locale);

        $cacheKey = QuranApiCache::getAyahsKey($id, $locale);

        $data = Cache::remember($cacheKey, $ttl, function () use ($id) {
            $surah = Surah::query()
                ->active()
                ->select(['id', 'number', 'revelation_type', 'ayah_count'])
                ->where('number', $id)
                ->firstOrFail();

            $activeCodes = \App\Models\Language::activeCodes();

            $ayahs = $surah->ayahs()
                ->active()
                ->select(['id', 'surah_id', 'ayah_number', 'text_uthmani'])
                ->with([
                    'translations' => function ($query) use ($activeCodes) {
                        $query->whereIn('language_code', $activeCodes)->where('is_active', true);
                    },
                    'tajweedSegments.tajweedRule' => function ($query) {
                        $query->where('is_active', true);
                    }
                ])
                ->orderBy('ayah_number')
                ->get();

            return [
                'surah' => (new SurahResource($surah))->resolve(request()),
                'ayahs' => AyahResource::collection($ayahs)->resolve(request()),
            ];
        });

        if (request()->has('fields')) {
            $fields = explode(',', request()->query('fields'));
            if (isset($data['ayahs']) && is_array($data['ayahs'])) {
                $data['ayahs'] = collect($data['ayahs'])->map(function ($item) use ($fields) {
                    return array_intersect_key((array)$item, array_flip($fields));
                })->all();
            }
        }

        return $this->success($data);
    }
}
