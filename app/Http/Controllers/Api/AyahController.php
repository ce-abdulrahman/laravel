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

        $surahExists = Surah::query()->active()->whereKey($id)->exists();
        if (! $surahExists) {
            return $this->error('Surah not found.', 404);
        }

        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = QuranApiCache::keyAyahsForSurah($id);

        $data = Cache::remember($cacheKey, $ttl, function () use ($id) {
            $surah = Surah::query()
                ->active()
                ->select(['id', 'number', 'name_ar', 'name_en', 'name_ku', 'ayah_count'])
                ->findOrFail($id);

            $ayahs = $surah->ayahs()
                ->active()
                ->select(['id', 'surah_id', 'ayah_number', 'text_uthmani'])
                ->orderBy('ayah_number')
                ->get();

            return [
                'surah' => (new SurahResource($surah))->resolve(request()),
                'ayahs' => AyahResource::collection($ayahs)->resolve(request()),
            ];
        });

        return $this->success($data);
    }
}
