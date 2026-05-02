<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Qiraat;
use App\Models\QiraatText;
use Illuminate\Http\Request;

class QiraatController extends Controller
{
    public function index(Request $request)
    {
        $query = Qiraat::active()->withCount('texts');

        if ($request->has('riwayah')) {
            $query->where('riwayah', $request->riwayah);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $qiraats = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $qiraats
        ]);
    }

    public function show($id)
    {
        $qiraat = Qiraat::active()
                        ->withCount('texts')
                        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $qiraat
        ]);
    }

    public function qiraatTexts(Request $request)
    {
        $query = QiraatText::with(['qiraat', 'ayah.surah']);

        if ($request->has('qiraat_id')) {
            $query->where('qiraah_id', $request->qiraat_id);
        }

        if ($request->has('ayah_id')) {
            $query->where('ayah_id', $request->ayah_id);
        }

        if ($request->has('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        $texts = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $texts
        ]);
    }
}
