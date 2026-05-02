<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MemorizationReview;
use App\Models\UserAyahProgress;
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ayah_id' => 'required|exists:ayahs,id',
            'review_date' => 'required|date',
            'review_level' => 'nullable|in:new,learning,reviewing,mastered',
            'result' => 'nullable|in:perfect,good,fair,needs_work,forgot',
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

        // Update or create progress
        UserAyahProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'ayah_id' => $request->ayah_id,
            ],
            [
                'memorize_status' => $this->mapReviewLevelToStatus($request->review_level),
                'last_reviewed_at' => $request->review_date,
                'strength_score' => $this->calculateStrengthScore($request->result),
                'mistakes_count' => \DB::raw('mistakes_count + ' . ($request->result === 'forgot' ? 1 : 0)),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Review recorded successfully',
            'data' => $review->load('ayah')
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

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted successfully'
        ]);
    }

    private function mapReviewLevelToStatus($level)
    {
        return match ($level) {
            'mastered' => 'mastered',
            'reviewing' => 'memorized',
            'learning' => 'learning',
            default => 'not_started',
        };
    }

    private function calculateStrengthScore($result)
    {
        return match ($result) {
            'perfect' => 100,
            'good' => 80,
            'fair' => 60,
            'needs_work' => 40,
            'forgot' => 20,
            default => 50,
        };
    }
}
