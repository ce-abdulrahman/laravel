<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAyahProgress;
use Illuminate\Http\Request;

class UserAyahProgressController extends Controller
{
    public function index(Request $request)
    {
        $progress = UserAyahProgress::where('user_id', $request->user()->id)
                                   ->with(['ayah.surah'])
                                   ->when($request->memorize_status, function ($q) use ($request) {
                                       return $q->where('memorize_status', $request->memorize_status);
                                   })
                                   ->orderBy('last_reviewed_at', 'desc')
                                   ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $progress
        ]);
    }

    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        $totalMemorized = UserAyahProgress::where('user_id', $userId)
                                         ->whereIn('memorize_status', ['memorized', 'mastered'])
                                         ->count();

        $totalLearning = UserAyahProgress::where('user_id', $userId)
                                        ->where('memorize_status', 'learning')
                                        ->count();

        $totalReviews = \DB::table('memorization_reviews')
                          ->where('user_id', $userId)
                          ->count();

        $todayReviews = \DB::table('memorization_reviews')
                          ->where('user_id', $userId)
                          ->whereDate('review_date', today())
                          ->count();

        // Calculate streak
        $streak = $this->calculateStreak($userId);

        // Get recent activity
        $recentReviews = \DB::table('memorization_reviews')
                           ->where('user_id', $userId)
                           ->orderBy('created_at', 'desc')
                           ->take(5)
                           ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_memorized' => $totalMemorized,
                'total_learning' => $totalLearning,
                'total_reviews' => $totalReviews,
                'today_reviews' => $todayReviews,
                'streak_days' => $streak,
                'recent_reviews' => $recentReviews,
            ]
        ]);
    }

    private function calculateStreak($userId)
    {
        $streak = 0;
        $date = today();

        while (true) {
            $hasActivity = \DB::table('memorization_reviews')
                             ->where('user_id', $userId)
                             ->whereDate('review_date', $date)
                             ->exists();

            if (!$hasActivity) {
                break;
            }

            $streak++;
            $date = $date->subDay();
        }

        return $streak;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ayah_id' => 'required|exists:ayahs,id',
            'memorize_status' => 'required|in:not_started,learning,memorized,mastered',
            'notes' => 'nullable|string',
        ]);

        $progress = UserAyahProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'ayah_id' => $request->ayah_id,
            ],
            [
                'memorize_status' => $validated['memorize_status'],
                'last_memorized_at' => in_array($validated['memorize_status'], ['memorized', 'mastered']) ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Progress saved successfully',
            'data' => $progress->load('ayah')
        ]);
    }

    public function update(Request $request, $id)
    {
        $progress = UserAyahProgress::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'memorize_status' => 'sometimes|in:not_started,learning,memorized,mastered',
            'notes' => 'nullable|string',
        ]);

        $progress->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Progress updated successfully',
            'data' => $progress
        ]);
    }
}
