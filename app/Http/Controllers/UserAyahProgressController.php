<?php

namespace App\Http\Controllers;

use App\Models\UserAyahProgress;
use App\Models\Ayah;
use App\Models\Surah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAyahProgressController extends Controller
{
    /**
     * Display a listing of the user's ayah progress.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = UserAyahProgress::where('user_id', $user->id)
            ->with(['ayah.surah']);

        // فلتەر بەپێی دۆخی لەبەرکردن
        if ($request->filled('memorize_status')) {
            $query->where('memorize_status', $request->memorize_status);
        }

        // فلتەر بەپێی سورەت
        if ($request->filled('surah_id')) {
            $query->whereHas('ayah', function ($q) use ($request) {
                $q->where('surah_id', $request->surah_id);
            });
        }

        // ڕیزکردن بەپێی هێز
        if ($request->filled('sort')) {
            if ($request->sort === 'strength_asc') {
                $query->orderBy('strength_score', 'asc');
            } elseif ($request->sort === 'strength_desc') {
                $query->orderBy('strength_score', 'desc');
            }
        } else {
            $query->orderBy('ayah_id');
        }

        $progresses = $query->paginate($request->per_page ?? 20)
            ->withQueryString();

        $surahs = Surah::orderBy('id')->get();
        $statuses = $this->getMemorizeStatuses();

        // ئامارەکان
        $stats = $this->getUserStats($user->id);

        return view('user-ayah-progress.index', compact(
            'progresses', 'surahs', 'statuses', 'stats'
        ));
    }

    /**
     * Show the form for creating a new progress entry.
     */
    public function create(Request $request)
    {
        $surahs = Surah::orderBy('id')->get();
        $statuses = $this->getMemorizeStatuses();

        $selectedAyah = null;
        if ($request->filled('ayah_id')) {
            $selectedAyah = Ayah::with('surah')->find($request->ayah_id);
        }

        return view('user-ayah-progress.create', compact('surahs', 'statuses', 'selectedAyah'));
    }

    /**
     * Store a newly created progress entry in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'memorize_status' => 'required|in:not_started,memorizing,memorized,mastered,needs_review',
            'strength_score' => 'nullable|integer|min:0|max:100',
            'mistakes_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // پشکنینی ئایا پێشتر تۆمار کراوە
        $exists = UserAyahProgress::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['ayah_id' => __('user_ayah_progress.validation.already_exists')]);
        }

        $validated['user_id'] = $user->id;
        $validated['strength_score'] = $validated['strength_score'] ?? 0;
        $validated['mistakes_count'] = $validated['mistakes_count'] ?? 0;

        if (in_array($validated['memorize_status'], ['memorized', 'mastered'])) {
            $validated['last_memorized_at'] = now();
        }

        $progress = UserAyahProgress::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('user_ayah_progress.messages.created'),
                'progress' => $progress,
            ]);
        }

        return redirect()
            ->route('user-ayah-progress.show', $progress)
            ->with('success', __('user_ayah_progress.messages.created'));
    }

    /**
     * Store or update progress (upsert).
     */
    public function upsert(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'memorize_status' => 'nullable|in:not_started,memorizing,memorized,mastered,needs_review',
            'strength_score' => 'nullable|integer|min:0|max:100',
            'mistakes_count' => 'nullable|integer|min:0',
            'increment_mistakes' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $progress = UserAyahProgress::where('user_id', $user->id)
            ->where('ayah_id', $validated['ayah_id'])
            ->first();

        $data = [
            'user_id' => $user->id,
            'ayah_id' => $validated['ayah_id'],
        ];

        if (isset($validated['memorize_status'])) {
            $data['memorize_status'] = $validated['memorize_status'];
            if (in_array($validated['memorize_status'], ['memorized', 'mastered'])) {
                $data['last_memorized_at'] = now();
            }
        }

        if (isset($validated['strength_score'])) {
            $data['strength_score'] = $validated['strength_score'];
        }

        if (isset($validated['notes'])) {
            $data['notes'] = $validated['notes'];
        }

        if ($progress) {
            if ($request->boolean('increment_mistakes')) {
                $data['mistakes_count'] = $progress->mistakes_count + 1;
            } elseif (isset($validated['mistakes_count'])) {
                $data['mistakes_count'] = $validated['mistakes_count'];
            }
            
            $progress->update($data);
        } else {
            $data['strength_score'] = $data['strength_score'] ?? 0;
            $data['mistakes_count'] = $data['mistakes_count'] ?? 0;
            $progress = UserAyahProgress::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => __('user_ayah_progress.messages.updated'),
            'progress' => $progress,
        ]);
    }

    /**
     * Display the specified progress.
     */
    public function show(UserAyahProgress $userAyahProgress)
    {
        $this->authorize('view', $userAyahProgress);

        $userAyahProgress->load(['ayah.surah', 'user']);

        $nextAyah = Ayah::where('surah_id', $userAyahProgress->ayah->surah_id)
            ->where('ayah_number', '>', $userAyahProgress->ayah->ayah_number)
            ->orderBy('ayah_number')
            ->first();

        $prevAyah = Ayah::where('surah_id', $userAyahProgress->ayah->surah_id)
            ->where('ayah_number', '<', $userAyahProgress->ayah->ayah_number)
            ->orderBy('ayah_number', 'desc')
            ->first();

        return view('user-ayah-progress.show', compact('userAyahProgress', 'nextAyah', 'prevAyah'));
    }

    /**
     * Show the form for editing the specified progress.
     */
    public function edit(UserAyahProgress $userAyahProgress)
    {
        $this->authorize('update', $userAyahProgress);

        $userAyahProgress->load(['ayah.surah']);
        $statuses = $this->getMemorizeStatuses();

        return view('user-ayah-progress.edit', compact('userAyahProgress', 'statuses'));
    }

    /**
     * Update the specified progress in storage.
     */
    public function update(Request $request, UserAyahProgress $userAyahProgress)
    {
        $this->authorize('update', $userAyahProgress);

        $validated = $request->validate([
            'memorize_status' => 'required|in:not_started,memorizing,memorized,mastered,needs_review',
            'strength_score' => 'nullable|integer|min:0|max:100',
            'mistakes_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if (in_array($validated['memorize_status'], ['memorized', 'mastered']) &&
            $userAyahProgress->memorize_status !== $validated['memorize_status']) {
            $validated['last_memorized_at'] = now();
        }

        $userAyahProgress->update($validated);

        return redirect()
            ->route('user-ayah-progress.show', $userAyahProgress)
            ->with('success', __('user_ayah_progress.messages.updated'));
    }

    /**
     * Remove the specified progress from storage.
     */
    public function destroy(UserAyahProgress $userAyahProgress)
    {
        $this->authorize('delete', $userAyahProgress);

        $userAyahProgress->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('user_ayah_progress.messages.deleted'),
            ]);
        }

        return redirect()
            ->route('user-ayah-progress.index')
            ->with('success', __('user_ayah_progress.messages.deleted'));
    }

    /**
     * Get statistics dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $stats = $this->getUserStats($user->id);
        $statuses = $this->getMemorizeStatuses();
        
        // دۆخی لەبەرکردن بەپێی سورەت
        $bySurah = UserAyahProgress::where('user_id', $user->id)
            ->join('ayahs', 'user_ayah_progress.ayah_id', '=', 'ayahs.id')
            ->join('surahs', 'ayahs.surah_id', '=', 'surahs.id')
            ->leftJoin('surah_translations as st', function ($join) {
                $join->on('st.surah_id', '=', 'surahs.id')
                     ->where('st.locale', '=', 'ar');
            })
            ->selectRaw("surahs.id, st.name as name_ar, surahs.number, 
                        COUNT(*) as total,
                        SUM(CASE WHEN memorize_status = 'mastered' THEN 1 ELSE 0 END) as mastered,
                        SUM(CASE WHEN memorize_status = 'memorized' THEN 1 ELSE 0 END) as memorized,
                        SUM(CASE WHEN memorize_status = 'memorizing' THEN 1 ELSE 0 END) as memorizing,
                        SUM(CASE WHEN memorize_status = 'needs_review' THEN 1 ELSE 0 END) as needs_review")
            ->groupBy('surahs.id', 'st.name', 'surahs.number')
            ->orderBy('surahs.number')
            ->get();

        // ئایەتەکانی پێویستیان بە پێداچوونەوە هەیە
        $needsReview = UserAyahProgress::where('user_id', $user->id)
            ->where('memorize_status', 'needs_review')
            ->with('ayah.surah')
            ->orderBy('last_reviewed_at', 'asc')
            ->limit(10)
            ->get();

        // ئایەتەکانی بەهێزترین
        $strongest = UserAyahProgress::where('user_id', $user->id)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->with('ayah.surah')
            ->orderBy('strength_score', 'desc')
            ->limit(5)
            ->get();

        // ئایەتەکانی لاوازترین
        $weakest = UserAyahProgress::where('user_id', $user->id)
            ->whereIn('memorize_status', ['memorized', 'mastered', 'memorizing'])
            ->with('ayah.surah')
            ->orderBy('strength_score', 'asc')
            ->limit(5)
            ->get();

        // ڕێژەی تەواوکردن بەپێی جوز
        $byJuz = $this->getProgressByJuz($user->id);

        return view('user-ayah-progress.dashboard', compact(
            'stats', 'statuses', 'bySurah', 'needsReview', 'strongest', 'weakest', 'byJuz'
        ));
    }

    /**
     * Get user statistics.
     */
    private function getUserStats($userId): array
    {
        $totalAyahs = Ayah::count();
        
        $progress = UserAyahProgress::where('user_id', $userId)->get();
        
        return [
            'total_ayahs' => $totalAyahs,
            'memorized_count' => $progress->whereIn('memorize_status', ['memorized', 'mastered'])->count(),
            'mastered_count' => $progress->where('memorize_status', 'mastered')->count(),
            'memorizing_count' => $progress->where('memorize_status', 'memorizing')->count(),
            'needs_review_count' => $progress->where('memorize_status', 'needs_review')->count(),
            'avg_strength' => $progress->avg('strength_score') ?? 0,
            'total_mistakes' => $progress->sum('mistakes_count'),
            'completion_percentage' => $totalAyahs > 0 
                ? round(($progress->count() / $totalAyahs) * 100, 2) 
                : 0,
            'memorized_percentage' => $totalAyahs > 0 
                ? round(($progress->whereIn('memorize_status', ['memorized', 'mastered'])->count() / $totalAyahs) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get progress by juz.
     */
    private function getProgressByJuz($userId): array
    {
        $result = [];
        for ($juz = 1; $juz <= 30; $juz++) {
            $totalInJuz = Ayah::where('juz_number', $juz)->count();
            $memorized = UserAyahProgress::where('user_id', $userId)
                ->whereHas('ayah', function ($q) use ($juz) {
                    $q->where('juz_number', $juz);
                })
                ->whereIn('memorize_status', ['memorized', 'mastered'])
                ->count();
            
            $result[$juz] = [
                'total' => $totalInJuz,
                'memorized' => $memorized,
                'percentage' => $totalInJuz > 0 ? round(($memorized / $totalInJuz) * 100) : 0,
            ];
        }
        return $result;
    }

    /**
     * Get memorize statuses.
     */
    private function getMemorizeStatuses(): array
    {
        return [
            'not_started' => __('user_ayah_progress.statuses.not_started'),
            'memorizing' => __('user_ayah_progress.statuses.memorizing'),
            'memorized' => __('user_ayah_progress.statuses.memorized'),
            'mastered' => __('user_ayah_progress.statuses.mastered'),
            'needs_review' => __('user_ayah_progress.statuses.needs_review'),
        ];
    }

    /**
     * Get ayahs by surah for AJAX.
     */
    public function getSurahAyahs($surahId)
    {
        $user = Auth::user();
        
        $ayahs = Ayah::where('surah_id', $surahId)
            ->orderBy('ayah_number')
            ->get(['id', 'ayah_number', 'text_uthmani']);
            
        // زیادکردنی دۆخی لەبەرکردن بۆ هەر ئایەتێک
        $progresses = UserAyahProgress::where('user_id', $user->id)
            ->whereIn('ayah_id', $ayahs->pluck('id'))
            ->get()
            ->keyBy('ayah_id');

        $ayahs = $ayahs->map(function ($ayah) use ($progresses) {
            $progress = $progresses->get($ayah->id);
            return [
                'id' => $ayah->id,
                'ayah_number' => $ayah->ayah_number,
                'text' => $ayah->text_uthmani,
                'memorize_status' => $progress ? $progress->memorize_status : 'not_started',
                'strength_score' => $progress ? $progress->strength_score : 0,
            ];
        });

        return response()->json($ayahs);
    }
}