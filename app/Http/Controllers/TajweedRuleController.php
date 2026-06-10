<?php
// app/Http/Controllers/TajweedRuleController.php

namespace App\Http\Controllers;

use App\Models\TajweedRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TajweedRuleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin', except: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of the tajweed rules.
     */
    public function index(Request $request)
    {
        $query = TajweedRule::with(['translations', 'category.translations'])->withCount('ayahTajweedSegments');

        // فلتەر بەپێی کەتێگۆری
        if ($request->filled('category')) {
            $query->where('tajweed_rule_category_id', $request->category);
        }

        // فلتەر بەپێی دۆخی چالاکی
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by name or description across all active languages simultaneously
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereTranslationLikeAny('name', $search)
                  ->orWhereTranslationLikeAny('description', $search);
            });
        }

        $tajweedRules = $query
            ->orderByCategoryTranslation('asc')
            ->orderBy('priority', 'desc')
            ->orderByTranslation('name', 'asc')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $categories = \App\Models\TajweedRuleCategory::active()
            ->orderBy('order')
            ->pluck('name', 'id');

        $stats = [
            'total_rules'      => TajweedRule::count(),
            'active_rules'     => TajweedRule::where('is_active', true)->count(),
            'total_segments'   => \App\Models\AyahTajweedSegment::count(),
            'categories_count' => \App\Models\TajweedRuleCategory::count(),
        ];

        return view('tajweed-rules.index', compact('tajweedRules', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new tajweed rule.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $categories = \App\Models\TajweedRuleCategory::active()->orderBy('order')->pluck('name', 'id');
        $colorPalette = $this->getColorPalette();

        return view('tajweed-rules.create', compact('categories', 'colorPalette'));
    }

    /**
     * Store a newly created tajweed rule in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $rules = [
            'tajweed_rule_category_id' => 'nullable|exists:tajweed_rule_categories,id',
            'color_code'     => 'nullable|string|max:20',
            'example_text'   => 'nullable|string',
            'priority'       => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'translations'   => ['required', 'array'],
        ];

        $customAttributes = [
            'tajweed_rule_category_id' => __('tajweed_rules.fields.category'),
            'color_code' => __('tajweed_rules.fields.color_code'),
            'example_text' => __('tajweed_rules.fields.example_text'),
            'priority' => __('tajweed_rules.fields.priority'),
            'is_active' => __('tajweed_rules.fields.is_active'),
        ];

        $defaultCode = \App\Models\Language::default()?->code;
        foreach (\App\Models\Language::activeList() as $lang) {
            $isRequired = $lang->code === $defaultCode;
            $rules["translations.{$lang->code}.name"] = [
                $isRequired ? 'required' : 'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('tajweed_rule_translations', 'name')
                    ->where('locale', $lang->code)
            ];
            $rules["translations.{$lang->code}.description"] = [
                $isRequired ? 'required' : 'nullable',
                'string'
            ];
            $customAttributes["translations.{$lang->code}.name"] = "Name ({$lang->name})";
            $customAttributes["translations.{$lang->code}.description"] = "Description ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $fallbackLocale = config('app.fallback_locale', 'en');
        $slugSource = $validated['translations'][$fallbackLocale]['name'] ?? reset($validated['translations'])['name'] ?? 'rule';
        $validated['slug'] = Str::slug($slugSource);

        // پشکنینی دووبارە نەبوونی slug
        $count = 1;
        $originalSlug = $validated['slug'];
        while (TajweedRule::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $tajweedRule = TajweedRule::create($validated);
        if (isset($validated['translations'])) {
            $tajweedRule->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('tajweed-rules.show', $tajweedRule)
            ->with('success', __('tajweed_rules.messages.created'));
    }

    /**
     * Display the specified tajweed rule.
     */
    public function show(TajweedRule $tajweedRule)
    {
        $tajweedRule->loadMissing(['translations', 'category.translations', 'ayahTajweedSegments.ayah.surah.translations']);
        $activeLanguages = \App\Models\Language::activeList();

        $segments = $tajweedRule->ayahTajweedSegments()
            ->with(['ayah.surah'])
            ->orderBy('ayah_id')
            ->paginate(20);

        return view('tajweed-rules.show', [
            'tajweedRule' => $tajweedRule,
            'segments' => $segments,
            'activeLanguages' => $activeLanguages,
        ]);
    }

    /**
     * Show the form for editing the specified tajweed rule.
     */
    public function edit(TajweedRule $tajweedRule)
    {
        $this->authorizeAdmin();

        $categories = \App\Models\TajweedRuleCategory::active()->orderBy('order')->pluck('name', 'id');
        $colorPalette = $this->getColorPalette();

        return view('tajweed-rules.edit', compact('tajweedRule', 'categories', 'colorPalette'));
    }

    /**
     * Update the specified tajweed rule in storage.
     */
    public function update(Request $request, TajweedRule $tajweedRule)
    {
        $this->authorizeAdmin();

        $rules = [
            'tajweed_rule_category_id' => 'nullable|exists:tajweed_rule_categories,id',
            'color_code'     => 'nullable|string|max:20',
            'example_text'   => 'nullable|string',
            'priority'       => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'translations'   => ['required', 'array'],
        ];

        $customAttributes = [
            'tajweed_rule_category_id' => __('tajweed_rules.fields.category'),
            'color_code' => __('tajweed_rules.fields.color_code'),
            'example_text' => __('tajweed_rules.fields.example_text'),
            'priority' => __('tajweed_rules.fields.priority'),
            'is_active' => __('tajweed_rules.fields.is_active'),
        ];

        $defaultCode = \App\Models\Language::default()?->code;
        foreach (\App\Models\Language::activeList() as $lang) {
            $isRequired = $lang->code === $defaultCode;
            $rules["translations.{$lang->code}.name"] = [
                $isRequired ? 'required' : 'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('tajweed_rule_translations', 'name')
                    ->where('locale', $lang->code)
                    ->ignore($tajweedRule->id, 'tajweed_rule_id')
            ];
            $rules["translations.{$lang->code}.description"] = [
                $isRequired ? 'required' : 'nullable',
                'string'
            ];
            $customAttributes["translations.{$lang->code}.name"] = "Name ({$lang->name})";
            $customAttributes["translations.{$lang->code}.description"] = "Description ({$lang->name})";
        }

        $validated = $request->validate($rules, [], $customAttributes);

        $fallbackLocale = config('app.fallback_locale', 'en');
        $slugSource = $validated['translations'][$fallbackLocale]['name'] ?? reset($validated['translations'])['name'] ?? 'rule';
        $validated['slug'] = Str::slug($slugSource);

        // پشکنینی دووبارە نەبوونی slug
        $count = 1;
        $originalSlug = $validated['slug'];
        while (TajweedRule::where('slug', $validated['slug'])->where('id', '!=', $tajweedRule->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $tajweedRule->update($validated);
        if (isset($validated['translations'])) {
            $tajweedRule->saveTranslationsFromArray($validated['translations']);
        }

        return redirect()
            ->route('tajweed-rules.show', $tajweedRule)
            ->with('success', __('tajweed_rules.messages.updated'));
    }

    /**
     * Remove the specified tajweed rule from storage.
     */
    public function destroy(TajweedRule $tajweedRule)
    {
        $this->authorizeAdmin();

        // پشکنینی ئایا سێگمێنتی هەیە
        if ($tajweedRule->ayahTajweedSegments()->count() > 0) {
            return back()->with('error', __('tajweed_rules.messages.has_segments'));
        }

        $tajweedRule->delete();

        return redirect()
            ->route('tajweed-rules.index')
            ->with('success', __('tajweed_rules.messages.deleted'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(TajweedRule $tajweedRule)
    {
        $this->authorizeAdmin();

        $tajweedRule->update(['is_active' => !$tajweedRule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $tajweedRule->is_active,
            'message' => $tajweedRule->is_active 
                ? __('tajweed_rules.messages.activated') 
                : __('tajweed_rules.messages.deactivated'),
        ]);
    }

    /**
     * Get tajweed categories.
     */
    private function getTajweedCategories(): array
    {
        return [
            'noon_sakinah' => 'أحكام النون الساكنة والتنوين',
            'meem_sakinah' => 'أحكام الميم الساكنة',
            'madd' => 'المدود',
            'qalqalah' => 'القلقلة',
            'heavy_letters' => 'حروف التفخيم',
            'light_letters' => 'حروف الترقيق',
            'merging' => 'الإدغام',
            'clear' => 'الإظهار',
            'change' => 'الإقلاب',
            'hide' => 'الإخفاء',
            'pause' => 'الوقف',
            'prostration' => 'سجود التلاوة',
            'other' => 'أحكام أخرى',
        ];
    }

    /**
     * Get color palette for tajweed rules.
     */
    private function getColorPalette(): array
    {
        return [
            '#FF0000' => 'سوور',
            '#FF6B6B' => 'سووری کاڵ',
            '#FF8C00' => 'پرتەقاڵی تاریک',
            '#FFA500' => 'پرتەقاڵی',
            '#FFD700' => 'زێڕ',
            '#FFFF00' => 'زەرد',
            '#9ACD32' => 'زەردی سەوز',
            '#00FF00' => 'سەوز',
            '#008000' => 'سەوزی تاریک',
            '#1B7340' => 'سەوزی ئیسلامی',
            '#00CED1' => 'شینی سەوز',
            '#00BFFF' => 'شینی ئاسمانی',
            '#0000FF' => 'شین',
            '#4B0082' => 'نیلی',
            '#8B00FF' => 'مۆر',
            '#800080' => 'مۆری تاریک',
            '#FF00FF' => 'پەمەیی',
            '#FF69B4' => 'پەمەیی کاڵ',
            '#8B4513' => 'قاوەیی',
            '#808080' => 'خۆڵەمێشی',
        ];
    }

    /**
     * Authorize admin access.
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

        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $rules = json_decode($json, true);

        if (! is_array($rules)) {
            return back()->with('error', 'Invalid JSON file structure.');
        }

        $imported = 0;
        foreach ($rules as $ruleData) {
            if (empty($ruleData['name'])) {
                continue;
            }

            $slug = $ruleData['slug'] ?? Str::slug($ruleData['name']);
            // Ensure unique slug
            $count = 1;
            $originalSlug = $slug;
            while (TajweedRule::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            TajweedRule::updateOrCreate(
                ['name' => $ruleData['name']],
                [
                    'name_ku' => $ruleData['name_ku'] ?? $ruleData['name'],
                    'name_ar' => $ruleData['name_ar'] ?? null,
                    'slug' => $slug,
                    'category' => $ruleData['category'] ?? null,
                    'color_code' => $ruleData['color_code'] ?? null,
                    'description' => $ruleData['description'] ?? '',
                    'description_ku' => $ruleData['description_ku'] ?? '',
                    'example_text' => $ruleData['example_text'] ?? null,
                    'priority' => $ruleData['priority'] ?? 0,
                    'is_active' => filter_var($ruleData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );
            $imported++;
        }

        return redirect()->route('tajweed-rules.index')->with('success', "Imported {$imported} Tajweed Rules successfully.");
    }
}