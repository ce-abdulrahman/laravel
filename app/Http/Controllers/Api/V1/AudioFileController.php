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

        if (str_starts_with($audioFile->file_path, 'http://') || str_starts_with($audioFile->file_path, 'https://')) {
            return redirect()->away($audioFile->file_path);
        }

        if (!Storage::disk('public')->exists($audioFile->file_path)) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Audio file not found'
            ], 404);
        }

        $path = Storage::disk('public')->path($audioFile->file_path);
        $size = filesize($path);
        $mime = mime_content_type($path);

        $stream = fopen($path, 'rb');
        $start = 0;
        $end = $size - 1;

        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = (int) $matches[1];
                if (!empty($matches[2])) {
                    $end = (int) $matches[2];
                }
            }
        }

        $length = $end - $start + 1;

        return response()->stream(function () use ($stream, $start, $length) {
            fseek($stream, $start);
            echo fread($stream, $length);
            fclose($stream);
        }, 206, [
            'Content-Type' => $mime,
            'Content-Length' => $length,
            'Content-Range' => "bytes $start-$end/$size",
            'Accept-Ranges' => 'bytes',
        ]);
    }
}
