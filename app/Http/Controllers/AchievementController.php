<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\Language;
use App\Services\AchievementEngine;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(private readonly AchievementEngine $engine) {}

    public function index(Request $request)
    {
        $query = Achievement::with(['category.translations', 'translations'])->ordered();

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('key', 'like', "%{$term}%")
                  ->orWhereHas('translations', fn($t) => $t->where('name', 'like', "%{$term}%"));
            });
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === '1');
        }

        $achievements   = $query->paginate(25)->withQueryString();
        $categories     = AchievementCategory::active()->ordered()->with('translations')->get();
        $conditionTypes = $this->conditionTypes();
        $rewardTypes    = $this->rewardTypes();

        return view('achievements.index', compact('achievements', 'categories', 'conditionTypes', 'rewardTypes'));
    }

    public function create()
    {
        $categories   = AchievementCategory::active()->ordered()->with('translations')->get();
        $languages    = Language::activeList();
        $conditionTypes = $this->conditionTypes();
        $rewardTypes    = $this->rewardTypes();

        return view('achievements.create', compact('categories', 'languages', 'conditionTypes', 'rewardTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key'             => 'required|string|max:100|unique:achievements,key',
            'category_id'     => 'nullable|exists:achievement_categories,id',
            'icon'            => 'nullable|string|max:50',
            'badge_image'     => 'nullable|string|max:255',
            'condition_type'  => 'required|string|max:50',
            'condition_value' => 'required|integer|min:1',
            'condition_meta'  => 'nullable|json',
            'reward_type'     => 'required|string|max:50',
            'reward_points'   => 'required|integer|min:0',
            'reward_value'    => 'nullable|string|max:255',
            'version'         => 'required|integer|min:1',
            'is_hidden'       => 'boolean',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer|min:0',
            'translations'    => 'nullable|array',
        ]);

        $achievement = Achievement::create([
            'key'             => $data['key'],
            'category_id'     => $data['category_id'] ?? null,
            'icon'            => $data['icon'] ?? '🏆',
            'badge_image'     => $data['badge_image'] ?? null,
            'condition_type'  => $data['condition_type'],
            'condition_value' => $data['condition_value'],
            'condition_meta'  => $data['condition_meta'] ? json_decode($data['condition_meta'], true) : null,
            'reward_type'     => $data['reward_type'],
            'reward_points'   => $data['reward_points'],
            'reward_value'    => $data['reward_value'] ?? null,
            'version'         => $data['version'] ?? 1,
            'is_hidden'       => $request->boolean('is_hidden'),
            'is_active'       => $request->boolean('is_active', true),
            'sort_order'      => $data['sort_order'] ?? 0,
        ]);

        if (!empty($data['translations'])) {
            $achievement->saveTranslationsFromArray($data['translations']);
        }

        $this->engine->bustCache();

        return redirect()->route('achievements.index')
            ->with('success', __('achievements.messages.created'));
    }

    public function edit(Achievement $achievement)
    {
        $achievement->load(['translations', 'category.translations']);
        $categories     = AchievementCategory::active()->ordered()->with('translations')->get();
        $languages      = Language::activeList();
        $conditionTypes = $this->conditionTypes();
        $rewardTypes    = $this->rewardTypes();

        return view('achievements.edit', compact('achievement', 'categories', 'languages', 'conditionTypes', 'rewardTypes'));
    }

    public function update(Request $request, Achievement $achievement)
    {
        $data = $request->validate([
            'key'             => 'required|string|max:100|unique:achievements,key,' . $achievement->id,
            'category_id'     => 'nullable|exists:achievement_categories,id',
            'icon'            => 'nullable|string|max:50',
            'badge_image'     => 'nullable|string|max:255',
            'condition_type'  => 'required|string|max:50',
            'condition_value' => 'required|integer|min:1',
            'condition_meta'  => 'nullable|json',
            'reward_type'     => 'required|string|max:50',
            'reward_points'   => 'required|integer|min:0',
            'reward_value'    => 'nullable|string|max:255',
            'version'         => 'required|integer|min:1',
            'is_hidden'       => 'boolean',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer|min:0',
            'translations'    => 'nullable|array',
        ]);

        $achievement->update([
            'key'             => $data['key'],
            'category_id'     => $data['category_id'] ?? null,
            'icon'            => $data['icon'] ?? $achievement->icon,
            'badge_image'     => $data['badge_image'] ?? null,
            'condition_type'  => $data['condition_type'],
            'condition_value' => $data['condition_value'],
            'condition_meta'  => $data['condition_meta'] ? json_decode($data['condition_meta'], true) : null,
            'reward_type'     => $data['reward_type'],
            'reward_points'   => $data['reward_points'],
            'reward_value'    => $data['reward_value'] ?? null,
            'version'         => $data['version'],
            'is_hidden'       => $request->boolean('is_hidden'),
            'is_active'       => $request->boolean('is_active', true),
            'sort_order'      => $data['sort_order'] ?? $achievement->sort_order,
        ]);

        if (!empty($data['translations'])) {
            $achievement->saveTranslationsFromArray($data['translations']);
        }

        $this->engine->bustCache();

        return redirect()->route('achievements.index')
            ->with('success', __('achievements.messages.updated'));
    }

    public function destroy(Achievement $achievement)
    {
        $achievement->delete();
        $this->engine->bustCache();

        return redirect()->route('achievements.index')
            ->with('success', __('achievements.messages.deleted'));
    }

    private function conditionTypes(): array
    {
        $val = __('achievements.condition_types');
        if (is_array($val)) {
            return $val;
        }

        $locale = app()->getLocale();
        $file = resource_path("lang/{$locale}/achievements.php");
        if (!file_exists($file)) {
            $file = resource_path("lang/en/achievements.php");
        }

        if (file_exists($file)) {
            $translations = include $file;
            if (is_array($translations) && isset($translations['condition_types']) && is_array($translations['condition_types'])) {
                return $translations['condition_types'];
            }
        }

        return [
            'TOTAL_DHIKR'         => 'Total Dhikr Count',
            'CURRENT_STREAK'      => 'Current Streak (Days)',
            'LONGEST_STREAK'      => 'Longest Streak (Days)',
            'GOALS_COMPLETED'     => 'Goals Completed',
            'SESSION_DHIKR_COUNT' => 'Dhikr in One Session',
            'CONSECUTIVE_DAYS'    => 'Consecutive Active Days',
            'SPECIAL_EVENT'       => 'Special Event (Time-based)',
            'CUSTOM_RULE'         => 'Custom Rule',
        ];
    }

    private function rewardTypes(): array
    {
        $val = __('achievements.reward_types');
        if (is_array($val)) {
            return $val;
        }

        $locale = app()->getLocale();
        $file = resource_path("lang/{$locale}/achievements.php");
        if (!file_exists($file)) {
            $file = resource_path("lang/en/achievements.php");
        }

        if (file_exists($file)) {
            $translations = include $file;
            if (is_array($translations) && isset($translations['reward_types']) && is_array($translations['reward_types'])) {
                return $translations['reward_types'];
            }
        }

        return [
            'POINTS'        => 'Points',
            'BADGE'         => 'Badge',
            'TITLE'         => 'Title',
            'SPECIAL_THEME' => 'Special Theme',
            'FUTURE_REWARD' => 'Future Reward',
        ];
    }
}
