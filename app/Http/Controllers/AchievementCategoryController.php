<?php

namespace App\Http\Controllers;

use App\Models\AchievementCategory;
use App\Models\Language;
use App\Services\AchievementEngine;
use Illuminate\Http\Request;

class AchievementCategoryController extends Controller
{
    public function __construct(private readonly AchievementEngine $engine) {}

    public function index()
    {
        $categories = AchievementCategory::with('translations')
            ->ordered()
            ->paginate(20);

        return view('achievement-categories.index', compact('categories'));
    }

    public function create()
    {
        $languages = Language::activeList();
        return view('achievement-categories.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'icon'         => 'nullable|string|max:50',
            'sort_order'   => 'integer|min:0',
            'is_active'    => 'boolean',
            'translations' => 'required|array',
        ]);

        $category = AchievementCategory::create([
            'icon'       => $request->input('icon', '🏆'),
            'sort_order' => $request->input('sort_order', 0),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        if ($request->filled('translations')) {
            $category->saveTranslationsFromArray($request->input('translations'));
        }

        $this->engine->bustCache();

        return redirect()->route('achievement-categories.index')
            ->with('success', 'Category created.');
    }

    public function edit(AchievementCategory $achievementCategory)
    {
        $category = $achievementCategory;
        $category->load('translations');
        $languages = Language::activeList();
        return view('achievement-categories.edit', compact('category', 'languages'));
    }

    public function update(Request $request, AchievementCategory $achievementCategory)
    {
        $request->validate([
            'icon'         => 'nullable|string|max:50',
            'sort_order'   => 'integer|min:0',
            'is_active'    => 'boolean',
            'translations' => 'required|array',
        ]);

        $achievementCategory->update([
            'icon'       => $request->input('icon', $achievementCategory->icon),
            'sort_order' => $request->input('sort_order', $achievementCategory->sort_order),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        if ($request->filled('translations')) {
            $achievementCategory->saveTranslationsFromArray($request->input('translations'));
        }

        $this->engine->bustCache();

        return redirect()->route('achievement-categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroy(AchievementCategory $achievementCategory)
    {
        $achievementCategory->delete();
        $this->engine->bustCache();

        return redirect()->route('achievement-categories.index')
            ->with('success', 'Category deleted.');
    }
}
