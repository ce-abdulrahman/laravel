<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ayah;
use App\Models\AudioFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AudioTimingController extends Controller
{
    /**
     * Get audio timing segments for an audio file (word by word timing)
     */
    public function getTimings(Request $request, $audioFileId)
    {
        $audioFile = AudioFile::active()->with(['reciter', 'surah'])->findOrFail($audioFileId);

        // Check if timing file exists
        $timingPath = $this->getTimingFilePath($audioFile);

        if (!Storage::exists($timingPath)) {
        return response()->json([
            'status' => 'error',
            'success' => false,
            'message' => 'Timing data not available for this audio file',
        ], 404);
        }

        $timings = json_decode(Storage::get($timingPath), true);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'audio_file' => $audioFile,
                'timings' => $timings,
            ],
        ]);
    }

    /**
     * Get per-ayah audio segments/timings
     */
    public function getAyahTimings(Request $request, $audioFileId)
    {
        $audioFile = AudioFile::active()->with(['reciter', 'surah'])->findOrFail($audioFileId);

        $timingPath = $this->getAyahTimingFilePath($audioFile);

        if (!Storage::exists($timingPath)) {
            // Generate basic timings based on ayah count
            $timings = $this->generateBasicAyahTimings($audioFile);
        } else {
            $timings = json_decode(Storage::get($timingPath), true);
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'audio_file' => $audioFile,
                'ayah_timings' => $timings,
            ],
        ]);
    }

    /**
     * Get timing for specific ayah
     */
    public function getAyahTiming(Request $request, $audioFileId, $ayahId)
    {
        $audioFile = AudioFile::active()->with(['reciter', 'surah'])->findOrFail($audioFileId);
        $ayah = Ayah::active()->with('surah')->findOrFail($ayahId);

        $timingPath = $this->getAyahTimingFilePath($audioFile);

        if (!Storage::exists($timingPath)) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Timing data not available',
            ], 404);
        }

        $timings = json_decode(Storage::get($timingPath), true);
        $ayahTiming = $timings[$ayahId] ?? null;

        if (!$ayahTiming) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'message' => 'Timing not found for this ayah',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'ayah' => $ayah,
                'timing' => $ayahTiming,
            ],
        ]);
    }

    /**
     * Get audio segments for a range of ayahs
     */
    public function getRangeTimings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reciter_id' => 'required|exists:reciters,id',
            'surah_id' => 'required|exists:surahs,id',
            'from_ayah' => 'required|integer|min:1',
            'to_ayah' => 'required|integer|min:1|gte:from_ayah',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $audioFile = AudioFile::active()
            ->where('reciter_id', $request->reciter_id)
            ->where('surah_id', $request->surah_id)
            ->first();

        if (!$audioFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Audio file not found for this reciter and surah',
            ], 404);
        }

        $timingPath = $this->getAyahTimingFilePath($audioFile);

        if (!Storage::exists($timingPath)) {
            $timings = $this->generateBasicAyahTimings($audioFile);
        } else {
            $timings = json_decode(Storage::get($timingPath), true);
        }

        // Filter timings for the requested range
        $rangeTimings = [];
        for ($i = $request->from_ayah; $i <= $request->to_ayah; $i++) {
            $ayahId = Ayah::where('surah_id', $request->surah_id)
                         ->where('ayah_number', $i)
                         ->value('id');

            if ($ayahId && isset($timings[$ayahId])) {
                $rangeTimings[$ayahId] = $timings[$ayahId];
            }
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'audio_file' => $audioFile,
                'from_ayah' => $request->from_ayah,
                'to_ayah' => $request->to_ayah,
                'timings' => $rangeTimings,
            ],
        ]);
    }

    /**
     * Get audio playback position by time
     */
    public function getPositionByTime(Request $request, $audioFileId)
    {
        $request->validate([
            'time' => 'required|numeric|min:0', // Time in seconds
        ]);

        $audioFile = AudioFile::active()->findOrFail($audioFileId);

        $timingPath = $this->getAyahTimingFilePath($audioFile);

        if (!Storage::exists($timingPath)) {
            $timings = $this->generateBasicAyahTimings($audioFile);
        } else {
            $timings = json_decode(Storage::get($timingPath), true);
        }

        $currentAyahId = null;
        $currentPosition = [
            'ayah_id' => null,
            'ayah_number' => null,
            'surah_id' => $audioFile->surah_id,
            'time_seconds' => $request->time,
            'ayah_start_time' => 0,
            'ayah_end_time' => 0,
            'progress_in_ayah' => 0,
        ];

        foreach ($timings as $ayahId => $timing) {
            $startTime = $timing['start_time'] ?? 0;
            $endTime = $timing['end_time'] ?? 0;

            if ($request->time >= $startTime && $request->time < $endTime) {
                $ayah = Ayah::find($ayahId);
                $duration = $endTime - $startTime;
                $progressInAyah = $duration > 0 ? (($request->time - $startTime) / $duration) * 100 : 0;

                $currentPosition = [
                    'ayah_id' => $ayahId,
                    'ayah_number' => $ayah->ayah_number ?? null,
                    'surah_id' => $audioFile->surah_id,
                    'time_seconds' => $request->time,
                    'ayah_start_time' => $startTime,
                    'ayah_end_time' => $endTime,
                    'ayah_duration' => $duration,
                    'progress_in_ayah' => round($progressInAyah, 2),
                ];
                break;
            }
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $currentPosition,
        ]);
    }

    /**
     * Get current ayah by audio playback time (simplified)
     */
    public function getCurrentAyah(Request $request, $audioFileId)
    {
        $request->validate([
            'current_time' => 'required|numeric|min:0',
        ]);

        $position = $this->getPositionByTime($request, $audioFileId);
        $positionData = json_decode($position->getContent(), true);

        $ayahId = $positionData['data']['ayah_id'] ?? null;

        if (!$ayahId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not determine current ayah',
            ], 404);
        }

        $ayah = Ayah::active()
            ->with([
                'surah',
                'translations' => function ($q) {
                    $q->where('is_active', true)->where('is_default', true);
                }
            ])
            ->find($ayahId);

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'ayah' => $ayah,
                'position' => $positionData['data'],
            ],
        ]);
    }

    /**
     * Get all audio files for a surah with timings summary
     */
    public function getSurahAudioTimings(Request $request, $surahId)
    {
        $request->validate([
            'reciter_id' => 'nullable|exists:reciters,id',
        ]);

        $query = AudioFile::active()
            ->with(['reciter', 'surah'])
            ->where('surah_id', $surahId);

        if ($request->has('reciter_id')) {
            $query->where('reciter_id', $request->reciter_id);
        }

        $audioFiles = $query->get()->map(function ($audioFile) {
            $timingPath = $this->getAyahTimingFilePath($audioFile);
            $hasTimings = Storage::exists($timingPath);

            return [
                'id' => $audioFile->id,
                'reciter' => [
                    'id' => $audioFile->reciter->id,
                    'name' => $audioFile->reciter->name,
                ],
                'file_path' => $audioFile->file_path,
                'duration_seconds' => $audioFile->duration_seconds,
                'quality' => $audioFile->quality,
                'has_timings' => $hasTimings,
                'ayah_count' => $audioFile->surah->ayah_count ?? 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $audioFiles,
        ]);
    }

    /**
     * Get a single surah audio file + full ayah timings (Reader v2.1).
     * Query:
     * - reciter_id (required)
     * - quality (optional: low|medium|high)
     */
    public function getSurahAudio(Request $request, $surahId)
    {
        $request->validate([
            'reciter_id' => 'required|exists:reciters,id',
            'quality' => 'nullable|in:low,medium,high',
        ]);

        $query = AudioFile::active()
            ->with(['reciter', 'surah'])
            ->where('surah_id', $surahId)
            ->where('reciter_id', $request->reciter_id);

        if ($request->has('quality')) {
            $query->where('quality', $request->quality);
        }

        // Prefer high > medium > low when not explicitly requested.
        $audioFile = $query->orderByRaw("CASE quality WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")->first();

        if (!$audioFile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Audio file not found for this reciter and surah',
            ], 404);
        }

        $timingPath = $this->getAyahTimingFilePath($audioFile);
        if (!Storage::exists($timingPath)) {
            $ayahTimings = $this->generateBasicAyahTimings($audioFile);
        } else {
            $ayahTimings = json_decode(Storage::get($timingPath), true);
        }

        $streamUrl = (str_starts_with($audioFile->file_path, 'http://') || str_starts_with($audioFile->file_path, 'https://'))
            ? $audioFile->file_path
            : url("/api/v1/audio-files/{$audioFile->id}/stream");

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => [
                'audio_file' => [
                    'id' => $audioFile->id,
                    'reciter_id' => $audioFile->reciter_id,
                    'surah_id' => $audioFile->surah_id,
                    'file_path' => $audioFile->file_path,
                    'duration_seconds' => $audioFile->duration_seconds,
                    'quality' => $audioFile->quality,
                    'is_active' => $audioFile->is_active,
                    'reciter' => $audioFile->reciter ? [
                        'id' => $audioFile->reciter->id,
                        'name' => $audioFile->reciter->name,
                        'name_ar' => $audioFile->reciter->name_ar,
                    ] : null,
                    'surah' => $audioFile->surah ? [
                        'id' => $audioFile->surah->id,
                        'number' => $audioFile->surah->number,
                        'name_ar' => $audioFile->surah->name_ar,
                        'name_en' => $audioFile->surah->name_en,
                        'ayah_count' => $audioFile->surah->ayah_count,
                    ] : null,
                    'stream_url' => $streamUrl,
                ],
                'ayah_timings' => $ayahTimings,
            ],
        ]);
    }

    /**
     * Add or update audio timings (Admin only)
     */
    public function saveTimings(Request $request, $audioFileId)
    {
        $validator = Validator::make($request->all(), [
            'timings' => 'required|array',
            'timings.*.start_time' => 'required|numeric|min:0',
            'timings.*.end_time' => 'required|numeric|gt:timings.*.start_time',
            'timings.*.text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $audioFile = AudioFile::active()->findOrFail($audioFileId);

        // Format timings with ayah IDs
        $timings = [];
        $ayahs = Ayah::where('surah_id', $audioFile->surah_id)
                    ->orderBy('ayah_number')
                    ->get();

        foreach ($request->timings as $index => $timing) {
            if (isset($ayahs[$index])) {
                $ayah = $ayahs[$index];
                $timings[$ayah->id] = [
                    'ayah_number' => $ayah->ayah_number,
                    'start_time' => $timing['start_time'],
                    'end_time' => $timing['end_time'],
                    'duration' => $timing['end_time'] - $timing['start_time'],
                ];
            }
        }

        $timingPath = $this->getAyahTimingFilePath($audioFile);
        Storage::put($timingPath, json_encode($timings, JSON_PRETTY_PRINT));

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => 'Audio timings saved successfully',
            'data' => [
                'audio_file_id' => $audioFile->id,
                'timings_count' => count($timings),
            ],
        ]);
    }

    /**
     * Delete audio timings (Admin only)
     */
    public function deleteTimings($audioFileId)
    {
        $audioFile = AudioFile::active()->findOrFail($audioFileId);

        $timingPath = $this->getAyahTimingFilePath($audioFile);

        if (Storage::exists($timingPath)) {
            Storage::delete($timingPath);
        }

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => 'Audio timings deleted successfully',
        ]);
    }

    /**
     * Get audio file info with timing metadata
     */
    public function getAudioInfo(Request $request, $audioFileId)
    {
        $audioFile = AudioFile::active()
            ->with(['reciter', 'surah'])
            ->findOrFail($audioFileId);

        $timingPath = $this->getAyahTimingFilePath($audioFile);
        $hasTimings = Storage::exists($timingPath);

        $info = [
            'id' => $audioFile->id,
            'reciter' => [
                'id' => $audioFile->reciter->id,
                'name' => $audioFile->reciter->name,
                'riwayah' => $audioFile->reciter->riwayah,
            ],
            'surah' => [
                'id' => $audioFile->surah->id,
                'number' => $audioFile->surah->number,
                'name_ar' => $audioFile->surah->name_ar,
                'name_en' => $audioFile->surah->name_en,
                'ayah_count' => $audioFile->surah->ayah_count,
            ],
            'file_path' => $audioFile->file_path,
            'duration_seconds' => $audioFile->duration_seconds,
            'duration_formatted' => $this->formatDuration($audioFile->duration_seconds),
            'quality' => $audioFile->quality,
            'source_type' => $audioFile->source_type,
            'has_timings' => $hasTimings,
            'file_size' => Storage::exists($audioFile->file_path) ?
                $this->formatFileSize(Storage::size($audioFile->file_path)) : null,
        ];

        return response()->json([
            'status' => 'success',
            'success' => true,
            'data' => $info,
        ]);
    }

    /**
     * Helper: Get timing file path for an audio file
     */
    private function getTimingFilePath($audioFile)
    {
        return "timings/audio_{$audioFile->id}.json";
    }

    /**
     * Helper: Get ayah timing file path
     */
    private function getAyahTimingFilePath($audioFile)
    {
        return "timings/ayah_timings_{$audioFile->id}.json";
    }

    /**
     * Helper: Generate basic ayah timings (evenly distributed)
     */
    private function generateBasicAyahTimings($audioFile)
    {
        $ayahs = Ayah::where('surah_id', $audioFile->surah_id)
                    ->orderBy('ayah_number')
                    ->get();

        $totalAyahs = $ayahs->count();
        $totalDuration = $audioFile->duration_seconds ?? ($totalAyahs * 5); // Estimate 5 seconds per ayah

        if ($totalDuration <= 0 || $totalAyahs <= 0) {
            return [];
        }

        $durationPerAyah = $totalDuration / $totalAyahs;
        $timings = [];

        foreach ($ayahs as $index => $ayah) {
            $startTime = $index * $durationPerAyah;
            $endTime = $startTime + $durationPerAyah;

            $timings[$ayah->id] = [
                'ayah_number' => $ayah->ayah_number,
                'start_time' => round($startTime, 2),
                'end_time' => round($endTime, 2),
                'duration' => round($durationPerAyah, 2),
                'estimated' => true,
            ];
        }

        // Save generated timings
        $timingPath = $this->getAyahTimingFilePath($audioFile);
        Storage::put($timingPath, json_encode($timings, JSON_PRETTY_PRINT));

        return $timings;
    }

    /**
     * Helper: Format duration in seconds to HH:MM:SS
     */
    private function formatDuration($seconds)
    {
        if (!$seconds) {
            return '00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Helper: Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
