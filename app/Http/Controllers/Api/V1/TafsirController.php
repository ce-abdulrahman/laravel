<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tafsir;
use App\Models\TafsirBook;
use Illuminate\Http\Request;

class TafsirController extends Controller
{
    public function index(Request $request)
    {
        $query = Tafsir::active()->with('tafsirBook');

        if ($request->has('tafsir_book_id')) {
            $query->where('tafsir_book_id', $request->tafsir_book_id);
        }

        $tafsirs = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $tafsirs
        ]);
    }

    public function tafsirBooks()
    {
        $books = TafsirBook::active()->get();

        return response()->json([
            'status' => 'success',
            'data' => $books
        ]);
    }

    public function ayahTafsirs(Request $request, $ayahId)
    {
        $tafsirs = Tafsir::active()
                        ->with('tafsirBook')
                        ->where('ayah_id', $ayahId)
                        ->when($request->tafsir_book_id, function ($q) use ($request) {
                            return $q->where('tafsir_book_id', $request->tafsir_book_id);
                        })
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $tafsirs
        ]);
    }
}
