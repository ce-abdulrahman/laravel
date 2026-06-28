<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reciter;
use App\Models\Surah;
use App\Models\AyahTiming;
use App\Http\Resources\ReciterResource;
use App\Services\RecitationUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ReciterController extends Controller
{
    protected RecitationUrlService $urlService;

    public function __construct(RecitationUrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * Display a listing of active reciters.
     */
    public function index(Request $request)
    {
        $query = Reciter::active();

        if ($request->has('riwayah')) {
            $query->where('riwayah', $request->riwayah);
        }

        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = 'api:v1:reciters:' . md5(json_encode($request->only(['riwayah', 'language', 'search'])));

        $reciters = Cache::remember($cacheKey, $ttl, function () use ($query) {
            return $query->get();
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => ReciterResource::collection($reciters)
        ]);
    }

    /**
     * Display the specified reciter.
     */
    public function show($id)
    {
        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = "api:v1:reciters:{$id}";

        $reciter = Cache::remember($cacheKey, $ttl, function () use ($id) {
            return Reciter::active()->findOrFail($id);
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => new ReciterResource($reciter)
        ]);
    }

    /**
     * Get playback URL and timings for a specific surah.
     * GET /api/v1/reciters/{id}/surahs/{surah}
     */
    public function showPlayback(Request $request, $id, $surah)
    {
        $reciter = Reciter::active()->findOrFail($id);
        $surahModel = Surah::active()
            ->where('number', $surah)
            ->orWhere('id', $surah)
            ->firstOrFail();

        $quality = $request->query('quality', 'high');

        // Generate dynamic audio URL
        try {
            $audioUrl = $this->urlService->generateUrl($reciter, $surahModel->number, $quality);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        // Retrieve ayah timings
        $ayahTiming = AyahTiming::where('reciter_id', $reciter->id)
            ->where('surah_id', $surahModel->id)
            ->first();

        $timings = [];
        $hasValidFile = false;

        if ($ayahTiming && $ayahTiming->timing_file_path) {
            if (Storage::exists($ayahTiming->timing_file_path)) {
                $fileContent = Storage::get($ayahTiming->timing_file_path);
                $rawTimings = json_decode($fileContent, true);
                if (is_array($rawTimings)) {
                    $hasValidFile = true;
                    // Normalize timings format
                    foreach ($rawTimings as $index => $item) {
                        $ayahNumber = $item['ayah'] ?? $item['ayah_number'] ?? ($index + 1);
                        $start = $item['start'] ?? $item['start_time'] ?? 0.0;
                        $end = $item['end'] ?? $item['end_time'] ?? 0.0;

                        $timings[] = [
                            'ayah' => (int) $ayahNumber,
                            'start' => (double) $start,
                            'end' => (double) $end,
                        ];
                    }
                }
            }
        }

        // Falling back to estimation if timings are missing or invalid
        if (!$hasValidFile) {
            $duration = $ayahTiming?->duration_seconds ?? ($surahModel->ayah_count * 5);
            $durationPerAyah = $duration / $surahModel->ayah_count;

            for ($i = 1; $i <= $surahModel->ayah_count; $i++) {
                $start = ($i - 1) * $durationPerAyah;
                $end = $i * $durationPerAyah;
                $timings[] = [
                    'ayah' => $i,
                    'start' => round($start, 2),
                    'end' => round($end, 2),
                ];
            }
        }

        return response()->json([
            'audio_url' => $audioUrl,
            'timings' => $timings,
        ]);
    }
}
