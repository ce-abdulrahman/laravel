<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTasbihStreak;
use App\Services\AchievementEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function __construct(
        private readonly AchievementEngine $engine
    ) {}

    // ─────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/achievements
    // Returns all achievements with current user progress.
    // ─────────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user() ?? $this->resolveGuestUser($request);
        $locale = $request->header('Accept-Language', app()->getLocale());

        if ($user) {
            $achievements = $this->engine->getUserAchievements($user, $locale);
        } else {
            // Guest: return all achievements with 0 progress, hidden items masked
            $achievements = $this->engine->getUserAchievements($this->buildGuestUser(), $locale);
        }

        // Statistics summary
        $total    = count($achievements);
        $earned   = count(array_filter($achievements, fn($a) => $a['is_completed']));
        $rare     = count(array_filter($achievements, fn($a) => $a['is_completed'] && $a['is_hidden']));
        $pct      = $total > 0 ? round(($earned / $total) * 100, 1) : 0;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'summary' => [
                    'total_available' => $total,
                    'total_earned'    => $earned,
                    'completion_pct'  => $pct,
                    'rare_earned'     => $rare,
                ],
                'achievements' => $achievements,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/achievements/{id}
    // ─────────────────────────────────────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user   = Auth::user() ?? $this->resolveGuestUser($request);
        $locale = $request->header('Accept-Language', app()->getLocale());

        $all = $user
            ? $this->engine->getUserAchievements($user, $locale)
            : $this->engine->getUserAchievements($this->buildGuestUser(), $locale);

        $achievement = collect($all)->firstWhere('id', $id);

        if (!$achievement) {
            return response()->json(['status' => 'error', 'message' => 'Achievement not found.'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $achievement]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // POST /api/v1/achievements/sync
    // Unified payload evaluation — called after dhikr tap, streak update, goal done.
    // ─────────────────────────────────────────────────────────────────────────────

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'total_dhikr_count'   => 'nullable|integer|min:0',
            'current_streak'      => 'nullable|integer|min:0',
            'goals_completed'     => 'nullable|integer|min:0',
            'session_dhikr_count' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user() ?? $this->resolveGuestUser($request);

        if (!$user) {
            return response()->json([
                'status'         => 'success',
                'newly_unlocked' => [],
                'message'        => 'Guest mode — achievements require login.',
            ]);
        }

        $payload = $request->only([
            'total_dhikr_count',
            'current_streak',
            'goals_completed',
            'session_dhikr_count',
        ]);

        // Also sync longest_streak from the user's tasbih streak record
        $streakRecord = UserTasbihStreak::where('user_id', $user->id)->first();
        if ($streakRecord) {
            $payload['longest_streak'] = $streakRecord->longest_streak;
        }

        $newlyUnlocked = $this->engine->evaluate($user, $payload);

        $formattedUnlocked = collect($newlyUnlocked)->map(function ($ua) {
            return [
                'id'            => $ua->id,
                'achievement_id'=> $ua->achievement_id,
                'name'          => $ua->achievement->getTranslation('name'),
                'description'   => $ua->achievement->getTranslation('description'),
                'icon'          => $ua->achievement->icon,
                'badge_image'   => $ua->achievement->badge_image,
                'reward_points' => $ua->achievement->reward_points,
                'reward_type'   => $ua->achievement->reward_type,
                'completed_at'  => $ua->completed_at?->toIso8601String(),
            ];
        })->values()->toArray();

        return response()->json([
            'status'         => 'success',
            'newly_unlocked' => $formattedUnlocked,
            'count'          => count($formattedUnlocked),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // GET /api/v1/achievements/unlocked
    // Returns recently unlocked achievements (for poll-based mobile check).
    // ─────────────────────────────────────────────────────────────────────────────

    public function unlocked(Request $request): JsonResponse
    {
        $user = Auth::user() ?? $this->resolveGuestUser($request);

        if (!$user) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $since = $request->query('since'); // ISO8601 string
        $newlyUnlocked = $this->engine->getNewlyUnlocked($user, $since);

        return response()->json([
            'status' => 'success',
            'data'   => $newlyUnlocked,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────────

    private function resolveGuestUser(Request $request): ?User
    {
        $userId = $request->input('user_id');
        return $userId ? User::find($userId) : null;
    }

    /**
     * Build a throwaway guest-like user for listing achievements with 0 progress.
     */
    private function buildGuestUser(): User
    {
        $guest = new User();
        $guest->id = 0;
        return $guest;
    }
}
