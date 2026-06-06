<?php

namespace App\Http\Controllers;

use App\Models\TajweedRuleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TajweedRuleCategoryController extends Controller
{
    /**
     * Display a listing of all Tajweed rule categories.
     */
    public function index(Request $request)
    {
        $query = TajweedRuleCategory::withCount('tajweedRules');

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('name_ku', 'like', '%' . $request->search . '%')
                  ->orWhere('name_ar', 'like', '%' . $request->search . '%');
            });
        }

        $categories = $query
            ->orderBy('order')
            ->orderBy('name')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $stats = [
            'total'    => TajweedRuleCategory::count(),
            'active'   => TajweedRuleCategory::where('is_active', true)->count(),
            'inactive' => TajweedRuleCategory::where('is_active', false)->count(),
        ];

        return view('tajweed-rule-categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $category = new TajweedRuleCategory();

        return view('tajweed-rule-categories.create', compact('category'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:tajweed_rule_categories,name',
            'name_ku'        => 'nullable|string|max:255',
            'name_ar'        => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'description_ku' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'order'          => 'integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);

        $category = TajweedRuleCategory::create($validated);

        return redirect()
            ->route('tajweed-rule-categories.show', $category)
            ->with('success', __('tajweed_categories.messages.created'));
    }

    /**
     * Display the specified category with its rules.
     */
    public function show(TajweedRuleCategory $tajweedRuleCategory)
    {
        $rules = $tajweedRuleCategory->tajweedRules()
            ->withCount('ayahTajweedSegments')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('tajweed-rule-categories.show', compact('tajweedRuleCategory', 'rules'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(TajweedRuleCategory $tajweedRuleCategory)
    {
        $this->authorizeAdmin();

        $category = $tajweedRuleCategory;

        return view('tajweed-rule-categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, TajweedRuleCategory $tajweedRuleCategory)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:tajweed_rule_categories,name,' . $tajweedRuleCategory->id,
            'name_ku'        => 'nullable|string|max:255',
            'name_ar'        => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'description_ku' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'order'          => 'integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name'], $tajweedRuleCategory->id);

        $tajweedRuleCategory->update($validated);

        return redirect()
            ->route('tajweed-rule-categories.show', $tajweedRuleCategory)
            ->with('success', __('tajweed_categories.messages.updated'));
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(TajweedRuleCategory $tajweedRuleCategory)
    {
        $this->authorizeAdmin();

        if ($tajweedRuleCategory->tajweedRules()->count() > 0) {
            return back()->with('error', __('tajweed_categories.messages.has_rules'));
        }

        $tajweedRuleCategory->delete();

        return redirect()
            ->route('tajweed-rule-categories.index')
            ->with('success', __('tajweed_categories.messages.deleted'));
    }

    /**
     * Generate a unique slug for the category.
     */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (
            TajweedRuleCategory::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Authorize admin-only access.
     */
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, __('common.unauthorized'));
        }
    }
}
