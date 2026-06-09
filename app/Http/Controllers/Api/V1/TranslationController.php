<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $query = Translation::where('is_active', true);

        $languageCode = $request->input('language_code', app()->getLocale());
        $query->where('language_code', $languageCode);

        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = 'api:v1:translations:' . md5(json_encode(['language_code' => $languageCode]));

        $translations = Cache::remember($cacheKey, $ttl, function () use ($query) {
            return $query->get();
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $translations
        ]);
    }

    public function ayahTranslations(Request $request, $ayahId)
    {
        $languageCode = $request->input('language_code', app()->getLocale());
        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = 'api:v1:ayah_translations:' . md5(json_encode([$ayahId, $languageCode]));

        $translations = Cache::remember($cacheKey, $ttl, function () use ($languageCode, $ayahId) {
            return Translation::where('ayah_id', $ayahId)
                ->where('is_active', true)
                ->where('language_code', $languageCode)
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $translations
        ]);
    }

    /**
     * Batch translations for a surah (offline cache).
     * Query:
     * - language_code (optional)
     * - translator_name (optional)
     */
    public function surahTranslations(Request $request, $surahId)
    {
        $request->validate([
            'language_code' => 'nullable|string|max:10',
            'translator_name' => 'nullable|string|max:190',
        ]);

        $languageCode = $request->input('language_code', app()->getLocale());
        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = 'api:v1:surah_translations:' . md5(json_encode([
            'surah_id' => (int) $surahId,
            'language_code' => $languageCode,
            'translator_name' => $request->translator_name,
        ]));

        $rows = Cache::remember($cacheKey, $ttl, function () use ($request, $surahId, $languageCode) {
            $q = DB::table('translations')
                ->join('ayahs', 'translations.ayah_id', '=', 'ayahs.id')
                ->where('translations.is_active', true)
                ->where('ayahs.surah_id', (int) $surahId)
                ->select([
                    'translations.id',
                    'translations.ayah_id',
                    'translations.language_code',
                    'translations.translator_name',
                    'translations.content',
                    'translations.is_default',
                    'translations.is_active',
                ])
                ->orderBy('ayahs.ayah_number');

            $q->where('translations.language_code', $languageCode);

            if ($request->has('translator_name')) {
                $q->where('translations.translator_name', $request->translator_name);
            }

            return $q->get();
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $rows,
        ]);
    }
}
