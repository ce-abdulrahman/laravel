<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Surah;
use Illuminate\Http\Request;

class SurahController extends Controller
{
    public function index(Request $request)
    {
        $query = Surah::active();

        if ($request->has('type')) {
            $query->where('revelation_type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('translations', function ($transQuery) use ($search) {
                    $transQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $surahs = $query->orderBy('number')->get();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $surahs
        ]);
    }

    public function show($id)
    {
        $surah = Surah::active()->withCount('ayahs')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $surah
        ]);
    }
}
