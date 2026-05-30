<?php

namespace App\Http\Controllers;

use App\Models\AudioFile;
use App\Models\Reciter;
use App\Models\Surah;
use App\Models\Ayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use getID3;

class AudioFileController extends Controller
{
    /**
     * Display a listing of the audio files.
     */
    public function index(Request $request)
    {
        $query = AudioFile::with(['reciter', 'surah', 'ayah'])->active();

        // فلتەر بەپێی قورئان خوێن
        if ($request->filled('reciter_id')) {
            $query->where('reciter_id', $request->reciter_id);
        }

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->where('surah_id', $request->surah_id);
        }

        // فلتەر بەپێی جۆر
        if ($request->filled('type')) {
            if ($request->type === 'full') {
                $query->whereNotNull('surah_id')->whereNull('ayah_id');
            } elseif ($request->type === 'ayah') {
                $query->whereNotNull('ayah_id');
            }
        }

        // گەڕان
        if ($request->filled('search')) {
            $query->whereHas('reciter', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $audioFiles = $query->orderBy('reciter_id')
            ->orderBy('surah_id')
            ->orderBy('ayah_id')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $reciters = Reciter::active()->orderBy('name')->get();
        $surahs = Surah::orderBy('id')->get();

        $stats = [
            'total_files' => AudioFile::count(),
            'total_duration' => AudioFile::sum('duration_seconds'),
            'total_reciters_with_audio' => AudioFile::distinct('reciter_id')->count('reciter_id'),
            'full_surahs' => AudioFile::whereNotNull('surah_id')->whereNull('ayah_id')->count(),
        ];

        return view('audio-files.index', compact('audioFiles', 'reciters', 'surahs', 'stats'));
    }

    /**
     * Show the form for creating a new audio file.
     */
    public function create(Request $request)
    {
        $this->authorizeAdmin();

        $reciters = Reciter::active()->orderBy('name')->get();
        $surahs = Surah::orderBy('id')->get();
        $qualities = $this->getQualities();
        $sourceTypes = $this->getSourceTypes();

        $selectedReciter = null;
        if ($request->filled('reciter_id')) {
            $selectedReciter = Reciter::find($request->reciter_id);
        }

        return view('audio-files.create', compact(
            'reciters', 'surahs', 'qualities', 'sourceTypes', 'selectedReciter'
        ));
    }

    /**
     * Get ayahs by surah for AJAX.
     */
    public function getAyahs($surahId)
    {
        $ayahs = Ayah::where('surah_id', $surahId)
            ->orderBy('ayah_number')
            ->get(['id', 'ayah_number', 'text_uthmani']);

        return response()->json($ayahs);
    }

    /**
     * Store a newly created audio file in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $rules = [
            'reciter_id' => 'required|exists:reciters,id',
            'surah_id' => 'nullable|exists:surahs,id',
            'ayah_id' => 'nullable|exists:ayahs,id',
            'duration_seconds' => 'nullable|integer|min:1',
            'quality' => 'nullable|string|max:50',
            'source_type' => 'required|in:upload,url',
            'is_active' => 'boolean',
        ];

        if ($request->source_type === 'upload') {
            $rules['audio_file'] = 'required|file|mimes:mp3,wav,ogg|max:102400';
        } else {
            $rules['file_path'] = 'required|url|max:500';
        }

        $validated = $request->validate($rules);

        if ($request->source_type === 'upload' && $request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $path = $file->store('audio/' . $request->reciter_id, 'public');
            $validated['file_path'] = $path;

            // هەوڵدان بۆ دۆزینەوەی ماوەی فایلەکە
            if (!$request->filled('duration_seconds')) {
                try {
                    $getID3 = new getID3();
                    $fileInfo = $getID3->analyze($file->getPathname());
                    $validated['duration_seconds'] = (int) ($fileInfo['playtime_seconds'] ?? 0);
                } catch (\Exception $e) {
                    // ناتوانرێت ماوەکە بدۆزرێتەوە
                }
            }
        }

        $validated['surah_id'] = $validated['surah_id'] ?? null;
        $validated['ayah_id'] = $validated['ayah_id'] ?? null;

        // پشکنینی دووبارە نەبوون
        $exists = AudioFile::where('reciter_id', $validated['reciter_id'])
            ->where('surah_id', $validated['surah_id'])
            ->where('ayah_id', $validated['ayah_id'])
            ->exists(); 

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['reciter_id' => __('audio_files.validation.audio_exists')]);
        }

        $audioFile = AudioFile::create($validated);

        return redirect()
            ->route('audio-files.show', $audioFile)
            ->with('success', __('audio_files.messages.created'));
    }

    /**
     * Upload audio file via AJAX.
     */
    public function upload(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,wav,ogg|max:102400',
        ]);

        $file = $request->file('audio_file');
        $reciterId = $request->reciter_id ?? 'temp';
        $path = $file->store('audio/' . $reciterId, 'public');

        // هەوڵدان بۆ دۆزینەوەی ماوەی فایلەکە
        $duration = 0;
        try {
            $getID3 = new getID3();
            $fileInfo = $getID3->analyze($file->getPathname());
            $duration = (int) ($fileInfo['playtime_seconds'] ?? 0);
        } catch (\Exception $e) {
            // ناتوانرێت ماوەکە بدۆزرێتەوە
        }

        return response()->json([
            'success' => true,
            'file_path' => $path,
            'duration' => $duration,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $this->formatFileSize($file->getSize()),
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * Display the specified audio file.
     */
    public function show(AudioFile $audioFile)
    {
        $audioFile->load(['reciter', 'surah', 'ayah']);

        $relatedFiles = AudioFile::where('reciter_id', $audioFile->reciter_id)
            ->where('id', '!=', $audioFile->id)
            ->with('surah')
            ->orderBy('surah_id')
            ->limit(10)
            ->get();

        return view('audio-files.show', compact('audioFile', 'relatedFiles'));
    }

    /**
     * Stream audio file.
     */
    public function stream(AudioFile $audioFile)
    {
        if (str_starts_with($audioFile->file_path, 'http://') || str_starts_with($audioFile->file_path, 'https://')) {
            return redirect()->away($audioFile->file_path);
        }

        if (!Storage::disk('public')->exists($audioFile->file_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($audioFile->file_path);
        $size = filesize($path);
        $mime = mime_content_type($path);

        $stream = fopen($path, 'rb');
        $start = 0;
        $end = $size - 1;

        if (request()->hasHeader('Range')) {
            $range = request()->header('Range');
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

    /**
     * Show the form for editing the specified audio file.
     */
    public function edit(AudioFile $audioFile)
    {
        $this->authorizeAdmin();

        $reciters = Reciter::orderBy('name')->get();
        $surahs = Surah::orderBy('id')->get();
        $qualities = $this->getQualities();
        $sourceTypes = $this->getSourceTypes();

        $ayahs = [];
        if ($audioFile->surah_id) {
            $ayahs = Ayah::where('surah_id', $audioFile->surah_id)
                ->orderBy('ayah_number')
                ->get(['id', 'ayah_number']);
        }

        return view('audio-files.edit', compact(
            'audioFile', 'reciters', 'surahs', 'ayahs', 'qualities', 'sourceTypes'
        ));
    }

    /**
     * Update the specified audio file in storage.
     */
    public function update(Request $request, AudioFile $audioFile)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'reciter_id' => 'required|exists:reciters,id',
            'surah_id' => 'nullable|exists:surahs,id',
            'ayah_id' => 'nullable|exists:ayahs,id',
            'duration_seconds' => 'nullable|integer|min:1',
            'quality' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['surah_id'] = $validated['surah_id'] ?? null;
        $validated['ayah_id'] = $validated['ayah_id'] ?? null;

        // پشکنینی دووبارە نەبوون
        $exists = AudioFile::where('reciter_id', $validated['reciter_id'])
            ->where('surah_id', $validated['surah_id'])
            ->where('ayah_id', $validated['ayah_id'])
            ->where('id', '!=', $audioFile->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['reciter_id' => __('audio_files.validation.audio_exists')]);
        }

        $audioFile->update($validated);

        return redirect()
            ->route('audio-files.show', $audioFile)
            ->with('success', __('audio_files.messages.updated'));
    }

    /**
     * Remove the specified audio file from storage.
     */
    public function destroy(AudioFile $audioFile)
    {
        $this->authorizeAdmin();

        // سڕینەوەی فایلی دەنگی
        if (Storage::disk('public')->exists($audioFile->file_path)) {
            Storage::disk('public')->delete($audioFile->file_path);
        }

        $audioFile->delete();

        return redirect()
            ->route('audio-files.index')
            ->with('success', __('audio_files.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(AudioFile $audioFile)
    {
        $this->authorizeAdmin();

        $audioFile->update(['is_active' => !$audioFile->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $audioFile->is_active,
            'message' => $audioFile->is_active 
                ? __('audio_files.messages.activated') 
                : __('audio_files.messages.deactivated'),
        ]);
    }

    /**
     * Get quality options.
     */
    private function getQualities(): array
    {
        return [
            '64' => '64 kbps',
            '128' => '128 kbps',
            '192' => '192 kbps',
            '320' => '320 kbps',
        ];
    }

    /**
     * Get source type options.
     */
    private function getSourceTypes(): array
    {
        return [
            'upload' => __('audio_files.source_types.upload'),
            'url' => __('audio_files.source_types.url'),
        ];
    }

    /**
     * Format file size.
     */
    private function formatFileSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Authorize admin access.
     */
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }
    }
}