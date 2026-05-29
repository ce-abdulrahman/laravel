<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReadingHistory;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LastReadController extends Controller
{
    /**
     * Get user's reading history
     */
    public function index(Request $request)
    {
        $histories = ReadingHistory::where('user_id', $request->user()->id)
            ->with(['ayah.surah', 'ayah.translations' => function ($q) {
                $q->where('is_active', true)->where('is_default', true);
            }])
            ->orderBy('last_read_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $histories,
        ]);
    }

    /**
     * Get last read position (for "Continue Reading" feature)
     */
    public function getLastRead(Request $request)
    {
        $lastRead = ReadingHistory::where('user_id', $request->user()->id)
            ->with([
                'ayah.surah',
                'ayah.translations' => function ($q) {
                    $q->where('is_active', true)->where('is_default', true);
                }
            ])
            ->orderBy('last_read_at', 'desc')
            ->first();

        if (!$lastRead) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'No reading history found',
            ]);
        }

        // Get next ayah to read
        $nextAyah = Ayah::active()
            ->with('surah')
            ->where('surah_id', $lastRead->ayah->surah_id)
            ->where('ayah_number', '>', $lastRead->ayah->ayah_number)
            ->orderBy('ayah_number')
            ->first();

        if (!$nextAyah) {
            // Get next surah if current surah is finished
            $nextSurah = Surah::active()
                ->where('number', '>', $lastRead->ayah->surah->number)
                ->orderBy('number')
                ->first();

            if ($nextSurah) {
                $nextAyah = Ayah::active()
                    ->with('surah')
                    ->where('surah_id', $nextSurah->id)
                    ->orderBy('ayah_number')
                    ->first();
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'last_read' => [
                    'id' => $lastRead->id,
                    'ayah' => $lastRead->ayah,
                    'last_read_at' => $lastRead->last_read_at,
                    'seconds_spent' => $lastRead->seconds_spent,
                ],
                'next_ayah' => $nextAyah,
                'surah_progress' => $this->getSurahProgress($request->user()->id, $lastRead->ayah->surah_id),
            ],
        ]);
    }

    /**
     * Save or update reading position
     */
    public function saveLastRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_id' => 'required|exists:ayahs,id',
            'seconds_spent' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $readingHistory = ReadingHistory::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'ayah_id' => $request->ayah_id,
            ],
            [
                'last_read_at'  => now(),
                'seconds_spent' => $request->seconds_spent ?? 0,
            ]
        );

        // Award points + update streak
        $request->user()->recordReading();

        return response()->json([
            'status'  => 'success',
            'message' => 'Reading position saved successfully',
            'data'    => $readingHistory->load('ayah.surah'),
        ]);
    }

    /**
     * Update reading time for an ayah
     */
    public function updateReadingTime(Request $request, $ayahId)
    {
        $validator = Validator::make($request->all(), [
            'seconds_spent' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $readingHistory = ReadingHistory::where('user_id', $request->user()->id)
            ->where('ayah_id', $ayahId)
            ->first();

        if ($readingHistory) {
            $readingHistory->update([
                'seconds_spent' => $readingHistory->seconds_spent + $request->seconds_spent,
                'last_read_at' => now(),
            ]);
        } else {
            $readingHistory = ReadingHistory::create([
                'user_id' => $request->user()->id,
                'ayah_id' => $ayahId,
                'seconds_spent' => $request->seconds_spent,
                'last_read_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reading time updated',
            'data' => $readingHistory,
        ]);
    }

    /**
     * Get reading progress for a specific surah
     */
    public function getSurahReadingProgress(Request $request, $surahId)
    {
        $progress = $this->getSurahProgress($request->user()->id, $surahId);

        return response()->json([
            'status' => 'success',
            'data' => $progress,
        ]);
    }

    /**
     * Get overall reading progress
     */
    public function getOverallProgress(Request $request)
    {
        $userId = $request->user()->id;

        $totalAyahs = Ayah::active()->count();
        $readAyahs = ReadingHistory::where('user_id', $userId)->count();

        $totalSeconds = ReadingHistory::where('user_id', $userId)->sum('seconds_spent');

        $surahsStarted = ReadingHistory::where('user_id', $userId)
            ->with('ayah')
            ->get()
            ->pluck('ayah.surah_id')
            ->unique()
            ->count();

        $totalSurahs = Surah::active()->count();

        // Get recently read surahs
        $recentSurahs = ReadingHistory::where('user_id', $userId)
            ->with('ayah.surah')
            ->orderBy('last_read_at', 'desc')
            ->get()
            ->groupBy(function ($history) {
                return $history->ayah->surah_id;
            })
            ->take(5)
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'surah_id' => $first->ayah->surah_id,
                    'surah_name' => $first->ayah->surah->name_en ?? $first->ayah->surah->name_ar,
                    'surah_name_ar' => $first->ayah->surah->name_ar,
                    'last_read_at' => $first->last_read_at,
                    'ayahs_read' => $group->count(),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_ayahs' => $totalAyahs,
                'read_ayahs' => $readAyahs,
                'read_percentage' => $totalAyahs > 0 ? round(($readAyahs / $totalAyahs) * 100, 2) : 0,
                'total_time_spent_seconds' => $totalSeconds,
                'total_time_spent_formatted' => $this->formatTime($totalSeconds),
                'surahs_started' => $surahsStarted,
                'total_surahs' => $totalSurahs,
                'surahs_percentage' => $totalSurahs > 0 ? round(($surahsStarted / $totalSurahs) * 100, 2) : 0,
                'recent_surahs' => $recentSurahs,
            ],
        ]);
    }

    /**
     * Delete reading history
     */
    public function clearHistory(Request $request)
    {
        ReadingHistory::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reading history cleared successfully',
        ]);
    }

    /**
     * Delete specific reading history entry
     */
    public function deleteEntry(Request $request, $id)
    {
        $history = ReadingHistory::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $history->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reading history entry deleted',
        ]);
    }

    /**
     * Get reading streaks
     */
    public function getReadingStreaks(Request $request)
    {
        $userId = $request->user()->id;

        // Get all distinct reading dates
        $readingDates = ReadingHistory::where('user_id', $userId)
            ->selectRaw('DATE(last_read_at) as date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->get()
            ->pluck('date');

        $streaks = [];
        $currentStreak = 0;
        $longestStreak = 0;
        $today = now()->startOfDay();
        $yesterday = $today->copy()->subDay();

        // Calculate current streak
        foreach ($readingDates as $date) {
            $dateObj = \Carbon\Carbon::parse($date)->startOfDay();

            if ($currentStreak === 0) {
                // Check if streak starts from today or yesterday
                if ($dateObj->equalTo($today) || $dateObj->equalTo($yesterday)) {
                    $currentStreak = 1;
                    continue;
                } else {
                    break; // Streak is broken
                }
            }

            $expectedDate = $yesterday->copy()->subDays($currentStreak - 1);

            if ($dateObj->equalTo($expectedDate)) {
                $currentStreak++;
            } else {
                break;
            }
        }

        // Calculate longest streak
        $tempStreak = 0;
        $previousDate = null;

        foreach ($readingDates->reverse() as $date) {
            $dateObj = \Carbon\Carbon::parse($date)->startOfDay();

            if ($previousDate === null) {
                $tempStreak = 1;
            } else {
                $expectedDate = \Carbon\Carbon::parse($previousDate)->addDay()->startOfDay();

                if ($dateObj->equalTo($expectedDate)) {
                    $tempStreak++;
                } else {
                    $longestStreak = max($longestStreak, $tempStreak);
                    $tempStreak = 1;
                }
            }

            $previousDate = $date;
        }

        $longestStreak = max($longestStreak, $tempStreak);

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
                'today_read' => $readingDates->contains($today->toDateString()),
            ],
        ]);
    }

    /**
     * Helper: Get surah reading progress
     */
    private function getSurahProgress($userId, $surahId)
    {
        $surah = Surah::active()->findOrFail($surahId);

        $totalAyahs = $surah->ayah_count;
        $readAyahs = ReadingHistory::where('user_id', $userId)
            ->whereHas('ayah', function ($q) use ($surahId) {
                $q->where('surah_id', $surahId);
            })
            ->count();

        // Get the last read ayah in this surah
        $lastReadInSurah = ReadingHistory::where('user_id', $userId)
            ->with('ayah')
            ->whereHas('ayah', function ($q) use ($surahId) {
                $q->where('surah_id', $surahId);
            })
            ->orderBy('last_read_at', 'desc')
            ->first();

        return [
            'surah_id' => $surah->id,
            'surah_name' => $surah->name_en ?? $surah->name_ar,
            'surah_name_ar' => $surah->name_ar,
            'total_ayahs' => $totalAyahs,
            'read_ayahs' => $readAyahs,
            'progress_percentage' => $totalAyahs > 0 ? round(($readAyahs / $totalAyahs) * 100, 2) : 0,
            'last_read_ayah' => $lastReadInSurah ? [
                'id' => $lastReadInSurah->ayah->id,
                'ayah_number' => $lastReadInSurah->ayah->ayah_number,
                'last_read_at' => $lastReadInSurah->last_read_at,
            ] : null,
            'is_completed' => $readAyahs >= $totalAyahs,
        ];
    }

    /**
     * Helper: Format seconds to human readable time
     */
    private function formatTime($totalSeconds)
    {
        if ($totalSeconds < 60) {
            return "{$totalSeconds}s";
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
