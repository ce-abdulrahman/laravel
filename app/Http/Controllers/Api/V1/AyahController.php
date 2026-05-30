<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;

class AyahController extends Controller
{
    public function index(Request $request)
    {
        $query = Ayah::active()->with(['surah', 'translations', 'tafsirs']);

        if ($request->has('surah_id')) {
            $query->whereHas('surah', function ($q) use ($request) {
                $q->where('number', $request->surah_id);
            });
        }

        if ($request->has('juz')) {
            $query->where('juz_number', $request->juz);
        }

        if ($request->has('page')) {
            $query->where('page_number', $request->page);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('text_uthmani', 'like', "%{$search}%")
                  ->orWhere('text_simple', 'like', "%{$search}%");
            });
        }

        $ayahs = $query->orderBy('surah_id')
                       ->orderBy('ayah_number')
                       ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $ayahs
        ]);
    }

    public function show($id)
    {
        $ayah = Ayah::active()
                    ->with([
                        'surah',
                        'translations' => function ($q) {
                            $q->where('is_active', true);
                        },
                        'tafsirs' => function ($q) {
                            $q->active()->with('tafsirBook');
                        },
                        'tajweedSegments.tajweedRule'
                    ])
                    ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $ayah
        ]);
    }

    public function ayahsBySurah(Request $request, $surahId)
    {
        $surah = Surah::query()->active()->where('number', $surahId)->first();
        if (!$surah) {
            return response()->json([
                'status' => 'error',
                'message' => 'Surah not found.'
            ], 404);
        }

        $ayahs = Ayah::active()
                     ->with(['translations' => function ($q) {
                          $q->where('is_active', true);
                      }])
                     ->where('surah_id', $surah->id)
                     ->orderBy('ayah_number')
                     ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $ayahs
        ]);
    }

    public function daily()
    {
        $totalAyahs = Ayah::active()->count();

        if ($totalAyahs === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active Ayahs found.'
            ], 404);
        }

        $today = now()->toDateString();
        $seed = crc32($today);
        $offset = abs($seed) % $totalAyahs;

        $ayah = Ayah::active()
            ->with([
                'surah',
                'translations' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->skip($offset)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $ayah
        ]);
    }
}
