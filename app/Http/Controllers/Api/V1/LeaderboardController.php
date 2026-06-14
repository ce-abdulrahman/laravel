<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LeaderboardScore;
use App\Models\LeaderboardEntry;
use App\Models\UserLeaderboardSetting;
use App\Services\LeaderboardEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    protected $leaderboardEngine;

    public function __construct(LeaderboardEngine $leaderboardEngine)
    {
        $this->leaderboardEngine = $leaderboardEngine;
    }

    /**
     * GET /api/v1/leaderboard
     * Get paginated rankings based on type and filters.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'weekly');
        $country = $request->get('country');
        $province = $request->get('province');
        
        if (!$this->leaderboardEngine->isTypeEnabled($type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leaderboard type is currently disabled.',
            ], 400);
        }

        // Compute/get rankings list
        $rankings = $this->leaderboardEngine->calculateRankings($type, $country, $province);

        // Paginate local array
        $page = (int) $request->get('page', 1);
        $perPage = 25;
        $total = count($rankings);
        $sliced = array_slice($rankings, ($page - 1) * $perPage, $perPage);

        // Get current user's rank status if authenticated
        $currentUserRank = null;
        $user = $request->user('sanctum');
        if ($user) {
            $currentUserRank = $this->getUserRankDetails($user, $type);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'rankings' => $sliced,
                'current_user_rank' => $currentUserRank,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil($total / $perPage),
                ]
            ]
        ]);
    }

    /**
     * GET /api/v1/leaderboard/me
     * Get authenticated user's detailed rank status.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'weekly');

        if (!$this->leaderboardEngine->isTypeEnabled($type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leaderboard type is currently disabled.',
            ], 400);
        }

        $details = $this->getUserRankDetails($user, $type);

        return response()->json([
            'status' => 'success',
            'data' => $details
        ]);
    }

    /**
     * GET /api/v1/leaderboard/top
     * Get top 3 users.
     */
    public function top(Request $request)
    {
        $type = $request->get('type', 'weekly');
        $country = $request->get('country');
        $province = $request->get('province');

        if (!$this->leaderboardEngine->isTypeEnabled($type)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Leaderboard type is currently disabled.',
            ], 400);
        }

        $rankings = $this->leaderboardEngine->calculateRankings($type, $country, $province);
        $topThree = array_slice($rankings, 0, 3);

        return response()->json([
            'status' => 'success',
            'data' => $topThree
        ]);
    }

    /**
     * POST /api/v1/leaderboard/privacy
     * Update user privacy preferences.
     */
    public function updatePrivacy(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'is_public' => 'required|boolean',
            'is_anonymous' => 'required|boolean',
            'is_hidden' => 'required|boolean',
        ]);

        $settings = UserLeaderboardSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'is_public' => $request->input('is_public'),
                'is_anonymous' => $request->input('is_anonymous'),
                'is_hidden' => $request->input('is_hidden'),
            ]
        );

        // Clear rankings cache on privacy change
        app(\App\Services\LeaderboardCacheService::class)->clearCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Privacy settings updated successfully.',
            'data' => $settings
        ]);
    }

    /**
     * Helper: get details of a specific user rank.
     */
    protected function getUserRankDetails(User $user, string $type): array
    {
        $scoreType = $this->leaderboardEngine->getScoreTypeForPeriod($type);
        
        // Ensure user score exists/is calculated
        $scoreObj = $this->leaderboardEngine->updateScoreForUser($user, $scoreType);
        $score = $scoreObj->score_value;

        // Calculate absolute rank using exact same filters as LeaderboardEngine::calculateRankings
        $rank = LeaderboardScore::where('leaderboard_scores.score_type', $scoreType)
            ->join('users', 'leaderboard_scores.user_id', '=', 'users.id')
            ->leftJoin('user_leaderboard_settings', 'users.id', '=', 'user_leaderboard_settings.user_id')
            ->where('users.status', true)
            ->where('users.role', 'user')
            ->where(function ($q) {
                $q->whereNull('user_leaderboard_settings.is_hidden')
                  ->orWhere('user_leaderboard_settings.is_hidden', false);
            })
            ->where('leaderboard_scores.score_value', '>', $score)
            ->count() + 1;

        // Movement
        $movement = LeaderboardEntry::where('user_id', $user->id)
            ->join('leaderboard_periods', 'leaderboard_entries.period_id', '=', 'leaderboard_periods.id')
            ->where('leaderboard_periods.type', $type)
            ->orderBy('leaderboard_entries.created_at', 'desc')
            ->value('movement') ?? 'new';

        // Distance to next rank (gap) using same filters
        $nextUserScore = LeaderboardScore::where('leaderboard_scores.score_type', $scoreType)
            ->join('users', 'leaderboard_scores.user_id', '=', 'users.id')
            ->leftJoin('user_leaderboard_settings', 'users.id', '=', 'user_leaderboard_settings.user_id')
            ->where('users.status', true)
            ->where('users.role', 'user')
            ->where(function ($q) {
                $q->whereNull('user_leaderboard_settings.is_hidden')
                  ->orWhere('user_leaderboard_settings.is_hidden', false);
            })
            ->where('leaderboard_scores.score_value', '>', $score)
            ->orderBy('leaderboard_scores.score_value', 'asc')
            ->first();

        $nextRankGap = $nextUserScore ? ($nextUserScore->score_value - $score) : 0;

        return [
            'rank' => $rank,
            'score' => $score,
            'movement' => $movement,
            'next_rank_gap' => $nextRankGap,
        ];
    }
}
