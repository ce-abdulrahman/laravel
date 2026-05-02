<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tafsir;
use App\Models\TafsirBook;
use Illuminate\Http\Request;

class TafsirController extends Controller
{
    public function books()
    {
        $books = TafsirBook::active()->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $books,
        ]);
    }

    public function byAyah(Request $request, int $ayahId)
    {
        $rows = Tafsir::active()
            ->with('tafsirBook')
            ->where('ayah_id', $ayahId)
            ->when($request->query('tafsir_book_id'), function ($q) use ($request) {
                $q->where('tafsir_book_id', (int) $request->query('tafsir_book_id'));
            })
            ->orderBy('tafsir_book_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rows,
        ]);
    }

    public function bySurah(Request $request, int $surahId)
    {
        $rows = Tafsir::active()
            ->with('tafsirBook')
            ->whereHas('ayah', function ($q) use ($surahId) {
                $q->where('surah_id', $surahId);
            })
            ->when($request->query('tafsir_book_id'), function ($q) use ($request) {
                $q->where('tafsir_book_id', (int) $request->query('tafsir_book_id'));
            })
            ->orderBy('ayah_id')
            ->orderBy('tafsir_book_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $rows,
        ]);
    }
}
