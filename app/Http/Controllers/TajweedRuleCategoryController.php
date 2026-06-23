<?php

namespace App\Http\Controllers;

use App\Models\TajweedRuleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TajweedRuleCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of all Tajweed rule categories.
     */
    public function index(Request $request)
    {
        $query = TajweedRuleCategory::with(['translations'])->withCount('tajweedRules');

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $query->whereTranslationLikeAny('name', $request->search);
        }

        $categories = $query
            ->orderBy('order')
            ->orderByTranslation('name', 'asc')
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

        $rules = [
            'order'          => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'translations'   => ['required', 'array'],
        ];

        $customAttributes = [
            'order' => __('tajweed_categories.fields.order'),
            'is_active' => __('tajweed_categories.fields.is_active'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $isFallback = $lang->code === config('app.fallback_locale', 'en');
            $rules["translations.{$lang->code}.name"] = [
                $isFallback ? 'required' : 'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('tajweed_rule_category_translations', 'name')
                    ->where('locale', $lang->code)
            ];
            $rules["translations.{$lang->code}.description"] = ['nullable', 'string'];
            $customAttributes["translations.{$lang->code}.name"] = __('tajweed_categories.fields.name_' . $lang->code) ?? "Name ({$lang->name})";
            $customAttributes["translations.{$lang->code}.description"] = __('tajweed_categories.fields.description_' . $lang->code) ?? "Description ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $fallbackLocale = config('app.fallback_locale', 'en');
        $slugSource = $validated['translations'][$fallbackLocale]['name'] ?? reset($validated['translations'])['name'] ?? 'category';
        $validated['slug'] = $this->generateUniqueSlug($slugSource);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = TajweedRuleCategory::create($validated);
        if (isset($validated['translations'])) {
            $category->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('tajweed-rule-categories.show', $category)
            ->with('success', __('tajweed_categories.messages.created'));
    }

    /**
     * Display the specified category with its rules.
     */
    public function show(TajweedRuleCategory $tajweedRuleCategory)
    {
        $tajweedRuleCategory->loadMissing('translations');
        $activeLanguages = \App\Models\Language::activeList();

        $rules = $tajweedRuleCategory->tajweedRules()
            ->with(['translations'])
            ->withCount('ayahTajweedSegments')
            ->orderBy('priority', 'desc')
            ->orderByTranslation('name')
            ->paginate(20);

        return view('tajweed-rule-categories.show', [
            'tajweedRuleCategory' => $tajweedRuleCategory,
            'rules' => $rules,
            'activeLanguages' => $activeLanguages,
        ]);
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

        $rules = [
            'order'          => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'translations'   => ['required', 'array'],
        ];

        $customAttributes = [
            'order' => __('tajweed_categories.fields.order'),
            'is_active' => __('tajweed_categories.fields.is_active'),
        ];

        foreach (\App\Models\Language::activeList() as $lang) {
            $isFallback = $lang->code === config('app.fallback_locale', 'en');
            $rules["translations.{$lang->code}.name"] = [
                $isFallback ? 'required' : 'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('tajweed_rule_category_translations', 'name')
                    ->where('locale', $lang->code)
                    ->ignore($tajweedRuleCategory->id, 'tajweed_rule_category_id')
            ];
            $rules["translations.{$lang->code}.description"] = ['nullable', 'string'];
            $customAttributes["translations.{$lang->code}.name"] = __('tajweed_categories.fields.name_' . $lang->code) ?? "Name ({$lang->name})";
            $customAttributes["translations.{$lang->code}.description"] = __('tajweed_categories.fields.description_' . $lang->code) ?? "Description ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $fallbackLocale = config('app.fallback_locale', 'en');
        $slugSource = $validated['translations'][$fallbackLocale]['name'] ?? reset($validated['translations'])['name'] ?? 'category';
        $validated['slug'] = $this->generateUniqueSlug($slugSource, $tajweedRuleCategory->id);
        $validated['is_active'] = $request->boolean('is_active', true);

        $tajweedRuleCategory->update($validated);
        if (isset($validated['translations'])) {
            $tajweedRuleCategory->saveTranslationsFromArray($validated['translations']);
        }

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

    public function import(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate(['file' => 'required|file|extensions:json']);

        $data = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

        if (! is_array($data)) {
            return back()->with('error', 'Invalid JSON structure.');
        }

        $imported = 0;
        foreach ($data as $row) {
            $slug = $row['slug'] ?? Str::slug($row['name_en'] ?? $row['name'] ?? 'category-' . $imported);
            if (empty($slug)) continue;

            $category = TajweedRuleCategory::firstOrNew(['slug' => $slug]);
            $category->order     = $row['order'] ?? 0;
            $category->is_active = filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $category->save();

            $translations = [];
            foreach (['en', 'ku', 'ar'] as $locale) {
                $name = $row["name_{$locale}"] ?? $row['name'] ?? null;
                $desc = $row["description_{$locale}"] ?? $row['description'] ?? null;
                if ($name) $translations[$locale] = ['name' => $name, 'description' => $desc];
            }
            if (! empty($translations)) $category->saveTranslationsFromArray($translations);

            $imported++;
        }

        return redirect()->route('tajweed-rule-categories.index')
            ->with('success', "Imported {$imported} categories successfully.");
    }

    public function export()
    {
        $this->authorizeAdmin();

        $categories = TajweedRuleCategory::with('translations')->orderBy('order')->get();

        $data = $categories->map(function (TajweedRuleCategory $cat) {
            $row = [
                'slug'      => $cat->slug,
                'order'     => $cat->order,
                'is_active' => (bool) $cat->is_active,
            ];
            foreach ($cat->translations as $t) {
                $row["name_{$t->locale}"]        = $t->name;
                $row["description_{$t->locale}"] = $t->description;
            }
            return $row;
        });

        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'tajweed_categories_export_' . now()->format('Ymd_His') . '.json';

        return response($json, 200, [
            'Content-Type'        => 'application/json; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
