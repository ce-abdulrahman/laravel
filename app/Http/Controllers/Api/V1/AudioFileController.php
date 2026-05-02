<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AudioFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AudioFileController extends Controller
{
    public function index(Request $request)
    {
        $query = AudioFile::active()
                         ->with(['reciter', 'surah', 'ayah']);

        if ($request->has('reciter_id')) {
            $query->where('reciter_id', $request->reciter_id);
        }

        if ($request->has('surah_id')) {
            $query->where('surah_id', $request->surah_id);
        }

        if ($request->has('ayah_id')) {
            $query->where('ayah_id', $request->ayah_id);
        }

        $audioFiles = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $audioFiles
        ]);
    }

    public function show($id)
    {
        $audioFile = AudioFile::active()
                             ->with(['reciter', 'surah', 'ayah'])
                             ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $audioFile
        ]);
    }

    public function stream(Request $request, $id)
    {
        $audioFile = AudioFile::active()->findOrFail($id);

        if (!Storage::exists($audioFile->file_path)) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Audio file not found'
            ], 404);
        }

        $path = Storage::path($audioFile->file_path);
        $stream = fopen($path, 'rb');

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'audio/mpeg',
            'Content-Length' => filesize($path),
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}
