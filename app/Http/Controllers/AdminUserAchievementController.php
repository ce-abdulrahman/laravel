<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\User;
use App\Models\UserAchievement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminUserAchievementController extends Controller
{
    public function index(Request $request)
    {
        $query = UserAchievement::with(['user', 'achievement.translations', 'achievement.category.translations'])
            ->orderByDesc('completed_at');

        if ($request->filled('q')) {
            $query->whereHas('user', fn($u) => $u
                ->where('name', 'like', "%{$request->q}%")
                ->orWhere('email', 'like', "%{$request->q}%")
            );
        }
        if ($request->filled('achievement_id')) {
            $query->where('achievement_id', $request->achievement_id);
        }
        if ($request->filled('status')) {
            $query->where('is_completed', $request->status === 'completed');
        }

        $userAchievements = $query->paginate(30)->withQueryString();
        $achievements     = Achievement::active()->with('translations')->ordered()->get();

        // Stats for header cards
        $totalUnlocks  = UserAchievement::where('is_completed', true)->count();
        $activeUsers   = UserAchievement::where('is_completed', true)->distinct('user_id')->count('user_id');
        $todayUnlocks  = UserAchievement::where('is_completed', true)
            ->whereDate('completed_at', Carbon::today('UTC'))->count();
        $totalAch      = Achievement::count();
        $totalUsers    = User::count();
        $avgCompletion = ($totalUsers > 0 && $totalAch > 0)
            ? round(UserAchievement::where('is_completed', true)->count() / ($totalUsers * $totalAch) * 100, 1)
            : 0;

        return view('user-achievements.index', compact(
            'userAchievements', 'achievements',
            'totalUnlocks', 'activeUsers', 'todayUnlocks', 'avgCompletion'
        ));
    }

    public function analytics()
    {
        $totalAchievements = Achievement::count();
        $totalUnlocks      = UserAchievement::where('is_completed', true)->count();

        // Average completion percentage
        $totalUsers    = User::count();
        $avgCompletion = ($totalUsers > 0 && $totalAchievements > 0)
            ? round(UserAchievement::where('is_completed', true)->count() / ($totalUsers * $totalAchievements) * 100, 1)
            : 0;

        // Top achievements by unlock count
        $topAchievements = Achievement::withCount(['userAchievements as user_achievements_count' => fn($q) => $q->where('is_completed', true)])
            ->with('translations', 'category.translations')
            ->orderByDesc('user_achievements_count')
            ->limit(8)
            ->get();

        // Top 10 users by achievement count
        $topUsers = UserAchievement::where('is_completed', true)
            ->selectRaw('user_id, count(*) as total')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Per-category stats
        $categoryStats = AchievementCategory::with('translations')
            ->withCount('achievements')
            ->get()
            ->map(function ($cat) {
                $cat->total_unlocks = UserAchievement::where('is_completed', true)
                    ->whereHas('achievement', fn($q) => $q->where('category_id', $cat->id))
                    ->count();
                return $cat;
            })
            ->sortByDesc('total_unlocks');

        // Unlock trend (last 30 days)
        $trend = UserAchievement::where('is_completed', true)
            ->where('completed_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(completed_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('user-achievements.analytics', compact(
            'totalAchievements', 'totalUnlocks', 'avgCompletion',
            'topAchievements', 'topUsers', 'categoryStats', 'trend'
        ));
    }

    public function grant(Request $request, User $user)
    {
        $request->validate([
            'achievement_id' => 'required|exists:achievements,id',
        ]);

        $achievement = Achievement::findOrFail($request->achievement_id);

        UserAchievement::updateOrCreate(
            ['user_id' => $user->id, 'achievement_id' => $achievement->id],
            [
                'progress_value'   => $achievement->condition_value,
                'is_completed'     => true,
                'completed_at'     => Carbon::now('UTC'),
                'unlocked_version' => $achievement->version,
            ]
        );

        return back()->with('success', "دەستکەوتە بەموفەقیەت بە {$user->name} دراوە.");
    }

    public function revoke(UserAchievement $userAchievement)
    {
        $userAchievement->delete();
        return back()->with('success', 'دەستکەوتەکە لادرا.');
    }

    public function reset(UserAchievement $userAchievement)
    {
        $userAchievement->update([
            'progress_value'   => 0,
            'is_completed'     => false,
            'completed_at'     => null,
            'unlocked_version' => 1,
        ]);

        return back()->with('success', 'پێشکەوتنی دەستکەوتەکە ڕیسێت کرایەوە.');
    }
}
