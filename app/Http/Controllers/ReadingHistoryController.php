<?php

namespace App\Http\Controllers;

use App\Models\ReadingHistory;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReadingHistoryController extends Controller
{
    /**
     * Display a listing of the user's reading history.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = ReadingHistory::where('user_id', $user->id)
            ->with(['ayah.surah']);

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // فلتەر بەپێی ڕێکەوت
        if ($request->filled('date_from')) {
            $query->whereDate('last_read_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('last_read_at', '<=', $request->date_to);
        }

        $histories = $query->orderBy('last_read_at', 'desc')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();

        // ئامارەکان
        $stats = [
            'total_reads' => ReadingHistory::where('user_id', $user->id)->count(),
            'unique_ayahs_read' => ReadingHistory::where('user_id', $user->id)
                ->distinct('ayah_id')
                ->count('ayah_id'),
            'total_time_spent' => ReadingHistory::where('user_id', $user->id)->sum('seconds_spent'),
            'today_reads' => ReadingHistory::where('user_id', $user->id)
                ->whereDate('last_read_at', today())
                ->count(),
            'week_reads' => ReadingHistory::where('user_id', $user->id)
                ->where('last_read_at', '>=', now()->subDays(7))
                ->count(),
        ];

        // زۆرترین ئایەتە خوێندراوەکان
        $mostRead = ReadingHistory::where('user_id', $user->id)
            ->selectRaw('ayah_id, COUNT(*) as read_count, SUM(seconds_spent) as total_time')
            ->groupBy('ayah_id')
            ->orderBy('read_count', 'desc')
            ->limit(5)
            ->with('ayah.surah')
            ->get();

        return view('reading-history.index', compact(
            'histories', 'surahs', 'stats', 'mostRead'
        ));
    }

    /**
     * Track reading activity.
     */
    public function track(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'seconds_spent' => 'nullable|integer|min:0|max:86400',
        ]);

        $existing = ReadingHistory::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'last_read_at' => now(),
                'seconds_spent' => $existing->seconds_spent + ($validated['seconds_spent'] ?? 0),
            ]);
        } else {
            ReadingHistory::create([
                'user_id' => $user->id,
                'ayah_id' => $validated['ayah_id'],
                'last_read_at' => now(),
                'seconds_spent' => $validated['seconds_spent'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('reading_history.messages.tracked'),
        ]);
    }

    /**
     * Track reading session (batch).
     */
    public function trackBatch(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayahs' => 'required|array',
            'ayahs.*.ayah_id' => 'required|exists:ayahs,id',
            'ayahs.*.seconds_spent' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['ayahs'] as $item) {
            $existing = ReadingHistory::where('user_id', $user->id)
                ->where('ayah_id', $item['ayah_id'])
                ->first();

            if ($existing) {
                $existing->update([
                    'last_read_at' => now(),
                    'seconds_spent' => $existing->seconds_spent + ($item['seconds_spent'] ?? 0),
                ]);
            } else {
                ReadingHistory::create([
                    'user_id' => $user->id,
                    'ayah_id' => $item['ayah_id'],
                    'last_read_at' => now(),
                    'seconds_spent' => $item['seconds_spent'] ?? 0,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('reading_history.messages.tracked_batch'),
        ]);
    }

    /**
     * Get reading statistics.
     */
    public function stats()
    {
        $user = Auth::user();

        $stats = [
            'daily' => $this->getDailyStats($user->id),
            'weekly' => $this->getWeeklyStats($user->id),
            'monthly' => $this->getMonthlyStats($user->id),
            'by_surah' => $this->getStatsBySurah($user->id),
            'summary' => [
                'total_reads' => ReadingHistory::where('user_id', $user->id)->count(),
                'total_time_formatted' => $this->formatTime(
                    ReadingHistory::where('user_id', $user->id)->sum('seconds_spent')
                ),
                'current_streak' => $this->calculateStreak($user->id),
                'longest_streak' => $this->calculateLongestStreak($user->id),
            ],
        ];

        return view('reading-history.stats', compact('stats'));
    }

    /**
     * Get reading streak.
     */
    private function calculateStreak($userId): int
    {
        $streak = 0;
        $date = now();

        while (true) {
            $hasRead = ReadingHistory::where('user_id', $userId)
                ->whereDate('last_read_at', $date->toDateString())
                ->exists();

            if (!$hasRead && $date->isToday()) {
                $date->subDay();
                continue;
            }

            if (!$hasRead) {
                break;
            }

            $streak++;
            $date->subDay();
        }

        return $streak;
    }

    /**
     * Calculate longest streak.
     */
    private function calculateLongestStreak($userId): int
    {
        $dates = ReadingHistory::where('user_id', $userId)
            ->selectRaw('DATE(last_read_at) as read_date')
            ->distinct()
            ->orderBy('read_date')
            ->pluck('read_date')
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $longest = 1;
        $current = 1;

        for ($i = 1; $i < count($dates); $i++) {
            $diff = Carbon::parse($dates[$i])->diffInDays(Carbon::parse($dates[$i - 1]));
            if ($diff == 1) {
                $current++;
                $longest = max($longest, $current);
            } else {
                $current = 1;
            }
        }

        return $longest;
    }

    /**
     * Get daily stats.
     */
    private function getDailyStats($userId): array
    {
        return ReadingHistory::where('user_id', $userId)
            ->whereDate('last_read_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(last_read_at) as date, COUNT(*) as count, SUM(seconds_spent) as time')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get weekly stats.
     */
    private function getWeeklyStats($userId): array
    {
        return ReadingHistory::where('user_id', $userId)
            ->where('last_read_at', '>=', now()->subWeeks(4))
            ->selectRaw('YEARWEEK(last_read_at) as week, COUNT(*) as count, SUM(seconds_spent) as time')
            ->groupBy('week')
            ->orderBy('week', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get monthly stats.
     */
    private function getMonthlyStats($userId): array
    {
        return ReadingHistory::where('user_id', $userId)
            ->where('last_read_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(last_read_at, "%Y-%m") as month, COUNT(*) as count, SUM(seconds_spent) as time')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get stats by surah.
     */
    private function getStatsBySurah($userId): array
    {
        return ReadingHistory::where('user_id', $userId)
            ->join('ayahs', 'reading_histories.ayah_id', '=', 'ayahs.id')
            ->join('surahs', 'ayahs.surah_id', '=', 'surahs.id')
            ->selectRaw('surahs.id, surahs.name_ar, COUNT(*) as count, SUM(seconds_spent) as time')
            ->groupBy('surahs.id', 'surahs.name_ar')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Format time in seconds to readable format.
     */
    private function formatTime($seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Clear reading history.
     */
    public function clear(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        ReadingHistory::where('user_id', $user->id)->delete();

        return redirect()
            ->route('reading-history.index')
            ->with('success', __('reading_history.messages.cleared'));
    }

    /**
     * Get continue reading suggestion.
     */
    public function continueReading()
    {
        $user = Auth::user();

        $lastRead = ReadingHistory::where('user_id', $user->id)
            ->with(['ayah.surah'])
            ->orderBy('last_read_at', 'desc')
            ->first();

        if (!$lastRead) {
            return response()->json([
                'has_suggestion' => false,
            ]);
        }

        // پێشنیاری ئایەتی داهاتوو
        $nextAyah = Ayah::where('surah_id', $lastRead->ayah->surah_id)
            ->where('ayah_number', '>', $lastRead->ayah->ayah_number)
            ->orderBy('ayah_number')
            ->first();

        if (!$nextAyah && $lastRead->ayah->surah_id < 114) {
            $nextAyah = Ayah::where('surah_id', $lastRead->ayah->surah_id + 1)
                ->orderBy('ayah_number')
                ->first();
        }

        return response()->json([
            'has_suggestion' => true,
            'last_read' => [
                'surah_id' => $lastRead->ayah->surah_id,
                'surah_name' => $lastRead->ayah->surah->name_ar,
                'ayah_number' => $lastRead->ayah->ayah_number,
            ],
            'suggestion' => $nextAyah ? [
                'ayah_id' => $nextAyah->id,
                'surah_id' => $nextAyah->surah_id,
                'surah_name' => $nextAyah->surah->name_ar,
                'ayah_number' => $nextAyah->ayah_number,
            ] : null,
        ]);
    }
}