<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MemorizationReview;
use App\Models\UserAyahProgress;
use App\Services\SpacedRepetitionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemorizationReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = MemorizationReview::where('user_id', $request->user()->id)
                                    ->with(['ayah.surah'])
                                    ->when($request->date, function ($q) use ($request) {
                                        return $q->whereDate('review_date', $request->date);
                                    })
                                    ->when($request->review_level, function ($q) use ($request) {
                                        return $q->where('review_level', $request->review_level);
                                    })
                                    ->orderBy('review_date', 'desc')
                                    ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }

    public function dueReviews(Request $request)
    {
        $userId = $request->user()->id;
        $today = Carbon::today()->toDateString();
        $nowStr = Carbon::now()->toDateTimeString();

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $nowSql = $driver === 'sqlite' ? "datetime('now')" : "NOW()";

        $dueReviews = UserAyahProgress::where('user_id', $userId)
            ->whereIn('memorize_status', ['memorized', 'mastered'])
            ->where(function($q) use ($today) {
                $q->whereDate('next_review_date', '<=', $today)
                  ->orWhereNull('next_review_date');
            })
            ->with(['ayah.surah'])
            ->orderByRaw("next_review_date < {$nowSql} DESC")
            ->orderBy('next_review_date', 'asc')
            ->orderBy('strength_score', 'asc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $dueReviews
        ]);
    }

    public function weakAyahs(Request $request)
    {
        $userId = $request->user()->id;

        $weakAyahs = UserAyahProgress::where('user_id', $userId)
            ->where('strength_score', '<', 60)
            ->with(['ayah.surah'])
            ->orderBy('strength_score', 'asc')
            ->orderBy('mistakes_count', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $weakAyahs
        ]);
    }

    public function learningAyahs(Request $request)
    {
        $userId = $request->user()->id;

        $learningAyahs = UserAyahProgress::where('user_id', $userId)
            ->where('memorize_status', 'learning')
            ->with(['ayah.surah'])
            ->orderBy('last_reviewed_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 'success',
            'data' => $learningAyahs
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_id' => 'required|exists:ayahs,id',
            'review_date' => 'required|date',
            'review_level' => 'nullable|in:new,learning,reviewing,mastered',
            'result' => 'required|in:perfect,good,fair,needs_work,forgot',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = MemorizationReview::create([
            'user_id' => $request->user()->id,
            'ayah_id' => $request->ayah_id,
            'review_date' => $request->review_date,
            'review_level' => $request->review_level ?? 'new',
            'result' => $request->result,
            'notes' => $request->notes,
        ]);

        // Spaced repetition updates
        $progress = app(SpacedRepetitionService::class)->logReview(
            $request->user()->id,
            $request->ayah_id,
            $request->result,
            $request->review_level ?? 'new'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Review recorded successfully',
            'data' => [
                'review' => $review->load('ayah'),
                'progress' => $progress
            ]
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $review = MemorizationReview::where('user_id', $request->user()->id)
                                   ->with(['ayah.surah'])
                                   ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = MemorizationReview::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'review_level' => 'sometimes|in:new,learning,reviewing,mastered',
            'result' => 'sometimes|in:perfect,good,fair,needs_work,forgot',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->all());

        app(SpacedRepetitionService::class)->invalidateCache($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $review = MemorizationReview::where('user_id', $request->user()->id)->findOrFail($id);
        $review->delete();

        app(SpacedRepetitionService::class)->invalidateCache($request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted successfully'
        ]);
    }
}
