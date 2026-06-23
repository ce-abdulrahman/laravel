<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\PrayerTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrayerTimesController extends Controller
{
    // Cache TTL: 24 hours
    private const CACHE_TTL = 86400;

    /**
     * GET /api/v1/prayer-times
     *
     * Parameters:
     *   city_id    (required) integer
     *   year       (optional) integer  — defaults to current year
     *   date_from  (optional) Y-m-d
     *   date_to    (optional) Y-m-d
     *
     * Supports ETag / If-None-Match for 304 Not Modified.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'city_id'   => 'required|integer|exists:cities,id',
            'year'      => 'nullable|integer|min:2020|max:2100',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to'   => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        $cityId   = (int) $request->city_id;
        $year     = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $city = City::findOrFail($cityId);

        // Build cache key
        $cacheKey = "prayer_times.city_{$cityId}.year_{$year}";
        if ($dateFrom || $dateTo) {
            $cacheKey .= ".{$dateFrom}_{$dateTo}";
        }

        // Compute version hash based on latest updated_at for this city+year
        $hashKey     = "prayer_times_hash.city_{$cityId}.year_{$year}";
        $versionHash = Cache::remember($hashKey, self::CACHE_TTL, function () use ($cityId, $year) {
            $latestUpdated = PrayerTime::forCity($cityId)
                ->forYear($year)
                ->max('updated_at');

            return hash('sha256', "city_{$cityId}_year_{$year}_" . ($latestUpdated ?? 'empty'));
        });

        // ETag / 304 support
        $clientEtag = $request->header('If-None-Match');
        if ($clientEtag && $clientEtag === $versionHash) {
            return response()->json(null, 304);
        }

        // Fetch data (from cache or DB)
        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($cityId, $year, $dateFrom, $dateTo) {
            $query = PrayerTime::forCity($cityId)->forYear($year)->orderBy('date');

            if ($dateFrom && $dateTo) {
                $query->forDateRange($dateFrom, $dateTo);
            } elseif ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            } elseif ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            return $query->get()->map(fn($pt) => [
                'date'    => $pt->date?->format('Y-m-d') ?? '',
                'fajr'    => $pt->fajr,
                'sunrise' => $pt->sunrise,
                'dhuhr'   => $pt->dhuhr,
                'asr'     => $pt->asr,
                'maghrib' => $pt->maghrib,
                'isha'    => $pt->isha,
                'source'  => $pt->source,
            ])->values()->toArray();
        });

        return response()->json([
            'city'         => $city->name,
            'city_id'      => $cityId,
            'timezone'     => $city->timezone,
            'year'         => $year,
            'date_from'    => $dateFrom,
            'date_to'      => $dateTo,
            'total'        => count($data),
            'data'         => $data,
            'version_hash' => $versionHash,
        ])->header('ETag', $versionHash)
          ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * GET /api/v1/prayer-times/cities
     *
     * Returns all cities that have prayer time data, with available years.
     */
    public function cities(): JsonResponse
    {
        $cacheKey = 'prayer_times_cities_list';

        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return City::whereHas('prayerTimes')
                ->withCount('prayerTimes')
                ->orderBy('name')
                ->get()
                ->map(function ($city) {
                    $years = PrayerTime::forCity($city->id)
                        ->distinct()
                        ->orderBy('year')
                        ->pluck('year')
                        ->toArray();

                    return [
                        'id'            => $city->id,
                        'name'          => $city->name,
                        'lat'           => $city->lat,
                        'lng'           => $city->lng,
                        'timezone'      => $city->timezone,
                        'available_years' => $years,
                        'total_entries' => $city->prayer_times_count,
                    ];
                })->values()->toArray();
        });

        return response()->json([
            'data'  => $result,
            'total' => count($result),
        ]);
    }
}
