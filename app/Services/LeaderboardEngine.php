<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaderboardPeriod;
use App\Models\LeaderboardEntry;
use App\Models\LeaderboardScore;
use App\Models\UserLeaderboardSetting;
use App\Models\SettingEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaderboardEngine
{
    protected $validationService;
    protected $cacheService;

    public function __construct(
        LeaderboardValidationService $validationService,
        LeaderboardCacheService $cacheService
    ) {
        $this->validationService = $validationService;
        $this->cacheService = $cacheService;
    }

    /**
     * Get configured weights for CUSTOM_SCORING.
     */
    public function getWeights(): array
    {
        return [
            'dhikr' => (int) (SettingEntry::where('key', 'leaderboard_weight_dhikr')->value('value') ?? 1),
            'daily_goal' => (int) (SettingEntry::where('key', 'leaderboard_weight_daily_goal')->value('value') ?? 10),
            'achievement' => (int) (SettingEntry::where('key', 'leaderboard_weight_achievement')->value('value') ?? 25),
            'streak' => (int) (SettingEntry::where('key', 'leaderboard_weight_streak')->value('value') ?? 5),
        ];
    }

    /**
     * Determine if a leaderboard type is active.
     */
    public function isTypeEnabled(string $type): bool
    {
        $val = SettingEntry::where('key', "leaderboard_type_enabled_{$type}")->value('value');
        if ($val === null) {
            return true; // default enabled
        }
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Recompute user scores and update leaderboard_scores.
     */
    public function updateScoreForUser(User $user, string $scoreType): LeaderboardScore
    {
        $scoreValue = 0;
        
        if ($scoreType === 'CUSTOM_SCORING') {
            $weights = $this->getWeights();
            $totalDhikr = $this->validationService->validateAndRecompute($user, 'TOTAL_DHIKR');
            $goals = $this->validationService->validateAndRecompute($user, 'GOALS_COMPLETED');
            $achievements = $this->validationService->validateAndRecompute($user, 'ACHIEVEMENTS_EARNED');
            $streak = $this->validationService->validateAndRecompute($user, 'CURRENT_STREAK');

            $scoreValue = ($totalDhikr * $weights['dhikr']) +
                           ($goals * $weights['daily_goal']) +
                           ($achievements * $weights['achievement']) +
                           ($streak * $weights['streak']);
        } else {
            $scoreValue = $this->validationService->validateAndRecompute($user, $scoreType);
        }

        // Store or update score in database
        return LeaderboardScore::updateOrCreate(
            [
                'user_id' => $user->id,
                'score_type' => $scoreType,
            ],
            [
                'score_value' => $scoreValue,
                'score_version' => 1,
                'calculated_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Calculate and cache rankings for a period type (daily, weekly, monthly, alltime, achievement, streak)
     */
    public function calculateRankings(string $type, ?string $country = null, ?string $province = null): array
    {
        $filter = 'global';
        if ($country && $province) {
            $filter = "geo:{$country}:{$province}";
        } elseif ($country) {
            $filter = "geo:{$country}";
        } elseif ($province) {
            $filter = "geo:province:{$province}";
        }

        $cached = $this->cacheService->getCachedRankings($type, $filter);
        if ($cached !== null) {
            return $cached;
        }

        $scoreType = $this->getScoreTypeForPeriod($type);

        // Fetch users who participate (exclude hidden users)
        $query = User::query()
            ->join('leaderboard_scores', 'users.id', '=', 'leaderboard_scores.user_id')
            ->leftJoin('user_leaderboard_settings', 'users.id', '=', 'user_leaderboard_settings.user_id')
            ->where('leaderboard_scores.score_type', $scoreType)
            ->where(function ($q) {
                $q->whereNull('user_leaderboard_settings.is_hidden')
                  ->orWhere('user_leaderboard_settings.is_hidden', false);
            })
            ->where('users.status', true)
            ->where('users.role', 'user');

        // Apply filters
        // Note: For country/province filter, we assume users have country and province fields or config.
        // Let's check if users table has country/province. If not, we fall back gracefully or assume they do.
        if ($country) {
            $query->where('users.country', $country);
        }
        if ($province) {
            $query->where('users.province', $province);
        }

        $users = $query->orderByDesc('leaderboard_scores.score_value')
            ->orderBy('leaderboard_scores.calculated_at')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'leaderboard_scores.score_value as score',
                'user_leaderboard_settings.is_anonymous',
                'user_leaderboard_settings.is_public'
            )
            ->get();

        // Calculate positions and movement
        $rankings = [];
        $previousPeriod = $this->getPreviousPeriod($type);

        foreach ($users as $index => $u) {
            $currentRank = $index + 1;
            
            // Resolve movement against previous period snapshot (leaderboard_entries)
            $movement = 'new';
            if ($previousPeriod) {
                $prevEntry = LeaderboardEntry::where('period_id', $previousPeriod->id)
                    ->where('user_id', $u->id)
                    ->first();
                if ($prevEntry) {
                    $prevRank = $prevEntry->rank_position;
                    if ($currentRank < $prevRank) {
                        $movement = 'up';
                    } elseif ($currentRank > $prevRank) {
                        $movement = 'down';
                    } else {
                        $movement = 'none';
                    }
                }
            }

            // Apply privacy controls
            $displayName = $u->name;
            if ($u->is_anonymous) {
                $displayName = 'User #' . substr(md5($u->id), 0, 4);
            }

            $rankings[] = [
                'rank' => $currentRank,
                'user_id' => $u->id,
                'name' => $displayName,
                'score' => (int) $u->score,
                'movement' => $movement,
                'is_anonymous' => (bool) $u->is_anonymous,
            ];
        }

        // Cache the calculations
        $this->cacheService->setCachedRankings($type, $filter, $rankings);

        return $rankings;
    }

    /**
     * Compute a snapshot of rankings and store it in database
     */
    public function generateSnapshot(string $type): LeaderboardPeriod
    {
        // 1. Create period record
        $startDate = Carbon::now();
        $endDate = Carbon::now();

        if ($type === 'daily') {
            $startDate = Carbon::today();
            $endDate = Carbon::today()->endOfDay();
        } elseif ($type === 'weekly') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($type === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Close any active periods of this type
        LeaderboardPeriod::where('type', $type)
            ->where('status', 'active')
            ->update(['status' => 'completed']);

        $period = LeaderboardPeriod::create([
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        // Recompute scores for all users first
        $users = User::where('role', 'user')->where('status', true)->get();
        $scoreType = $this->getScoreTypeForPeriod($type);

        foreach ($users as $user) {
            $this->updateScoreForUser($user, $scoreType);
        }

        // Calculate rankings
        $rankings = $this->calculateRankings($type);

        // Store entries
        foreach ($rankings as $rank) {
            LeaderboardEntry::create([
                'period_id' => $period->id,
                'user_id' => $rank['user_id'],
                'rank_position' => $rank['rank'],
                'score' => $rank['score'],
                'movement' => $rank['movement'],
            ]);
        }

        // Cache global rankings
        $this->cacheService->setCachedRankings($type, 'global', $rankings);

        return $period;
    }

    /**
     * Resolve Period string to Score Type.
     */
    public function getScoreTypeForPeriod(string $periodType): string
    {
        return match ($periodType) {
            'daily' => 'DAILY_DHIKR',
            'weekly' => 'WEEKLY_DHIKR',
            'monthly' => 'MONTHLY_DHIKR',
            'alltime' => 'ALL_TIME_DHIKR',
            'achievement' => 'ACHIEVEMENT_POINTS',
            'streak' => 'CURRENT_STREAK',
            default => 'CUSTOM_SCORING',
        };
    }

    /**
     * Fetch previous completed period for movement calculation.
     */
    protected function getPreviousPeriod(string $type): ?LeaderboardPeriod
    {
        return LeaderboardPeriod::where('type', $type)
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->first();
    }
}
