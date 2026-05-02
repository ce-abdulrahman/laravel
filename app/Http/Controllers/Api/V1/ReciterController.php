<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reciter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReciterController extends Controller
{
    public function index(Request $request)
    {
        $query = Reciter::active()->withCount('audioFiles');

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
            'data' => $reciters
        ]);
    }

    public function show($id)
    {
        $ttl = config('quran_api.cache_ttl', 3600);
        $cacheKey = "api:v1:reciters:{$id}";

        $reciter = Cache::remember($cacheKey, $ttl, function () use ($id) {
            return Reciter::active()
                         ->with(['audioFiles' => function ($q) {
                             $q->active()->with('surah');
                         }])
                         ->withCount('audioFiles')
                         ->findOrFail($id);
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $reciter
        ]);
    }
}
