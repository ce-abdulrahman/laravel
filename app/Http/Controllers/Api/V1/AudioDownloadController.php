<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AudioDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AudioDownloadController extends Controller
{
    public function index(Request $request)
    {
        $downloads = AudioDownload::where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $downloads
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reciter_id' => 'required|integer|exists:reciters,id',
            'surah_id' => 'required|integer|exists:surahs,id',
            'status' => 'required|string|in:downloading,completed,failed',
            'progress' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $progress = $request->input('progress', 0.00);
        if ($request->status === 'completed') {
            $progress = 100.00;
        }

        $download = AudioDownload::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'reciter_id' => $request->reciter_id,
                'surah_id' => $request->surah_id,
            ],
            [
                'status' => $request->status,
                'progress' => $progress,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Download status updated successfully',
            'data' => $download
        ]);
    }
}
