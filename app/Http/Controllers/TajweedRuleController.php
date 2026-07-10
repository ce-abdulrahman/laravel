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
            ->get()
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

        $categories = \App\Models\TajweedRuleCategory::active()->orderBy('order')->get()->pluck('name', 'id');
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
            'color_code'     => 'nullable|string|max:255',
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
            ->back()
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

        $categories = \App\Models\TajweedRuleCategory::active()->orderBy('order')->get()->pluck('name', 'id');
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
            'color_code'     => 'nullable|string|max:255',
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
            ->back()
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
            
            // 20 Gradient presets (No pure black/white, optimized for dark/light modes)
            'linear-gradient(135deg, #FF6B6B, #FF8E53)' => 'شەبەنگی خۆرئاوابوون (Sunset)',
            'linear-gradient(135deg, #4E54C8, #8F94FB)' => 'شینی مۆراوی گەش (Indigo)',
            'linear-gradient(135deg, #11998E, #38EF7D)' => 'سەوزی نیۆن (Neon Green)',
            'linear-gradient(135deg, #FF416C, #FF4B2B)' => 'مەرجانی گەش (Coral Red)',
            'linear-gradient(135deg, #00C6FF, #0072FF)' => 'شینی یاقووتی (Sapphire)',
            'linear-gradient(135deg, #F9D423, #FF4E50)' => 'شەبەنگی گەرمی خۆر (Warm Sun)',
            'linear-gradient(135deg, #f857a6, #ff5858)' => 'پەمەیی و سووری گەش (Rose Pink)',
            'linear-gradient(135deg, #43C6AC, #191654)' => 'شینی دەریایی تاریک (Midnight Sea)',
            'linear-gradient(135deg, #7F00FF, #E100FF)' => 'مۆری کارەبایی (Orchid Violet)',
            'linear-gradient(135deg, #3A1C71, #D76D77, #FFAF7B)' => 'شەبەنگی سێ ڕەنگ (Tri-Blend)',
            'linear-gradient(135deg, #159957, #155799)' => 'سەوز و شینی قووڵ (Deep Forest)',
            'linear-gradient(135deg, #0052D4, #4364F7, #6FB1FC)' => 'تێکەڵەی شینی ئاسمانی (Skyline)',
            'linear-gradient(135deg, #E55D87, #5FC3E4)' => 'پەمەیی مۆر و شین (Plum-Sky)',
            'linear-gradient(135deg, #FF9966, #FF5E62)' => 'پرتەقاڵی خۆڵەمێشی (Peach-Red)',
            'linear-gradient(135deg, #1D976C, #93F9B9)' => 'سەوزی زمڕوتی گەش (Mint Emerald)',
            'linear-gradient(135deg, #8A2387, #E94057, #F27121)' => 'مۆر و پرتەقاڵی ئەفسوناوی (Mystic)',
            'linear-gradient(135deg, #f953c6, #b91d73)' => 'پەمەیی تووتڕکی قووڵ (Raspberry)',
            'linear-gradient(135deg, #4ca2cd, #67B26F)' => 'سەوز و شینی ئارام (Calm Aqua)',
            'linear-gradient(135deg, #D4145A, #FBB03B)' => 'ئاگری سوور و پرتەقاڵی (Velvet Fire)',
            'linear-gradient(135deg, #00dbde, #fc00ff)' => 'نیۆن شین و مۆر (Cyan-Magenta)',
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
            'file' => 'required|file|extensions:json',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $rules = json_decode($json, true);

        if (! is_array($rules)) {
            return back()->with('error', 'Invalid JSON file structure.');
        }

        $imported = 0;
        foreach ($rules as $ruleData) {
            $name = $ruleData['name'] ?? $ruleData['name_en'] ?? null;
            if (!$name) {
                continue;
            }

            $slug = $ruleData['slug'] ?? Str::slug($name);
            // Ensure unique slug if creating a new rule
            $existingRule = TajweedRule::where('slug', $slug)->first();
            if (!$existingRule) {
                $count = 1;
                $originalSlug = $slug;
                while (TajweedRule::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }

            // Find category id if category_slug is provided
            $categoryId = null;
            if (!empty($ruleData['category_slug'])) {
                $category = \App\Models\TajweedRuleCategory::where('slug', $ruleData['category_slug'])->first();
                if ($category) {
                    $categoryId = $category->id;
                }
            } elseif (!empty($ruleData['category'])) {
                // fallback to category field which could be slug or ID
                $category = \App\Models\TajweedRuleCategory::where('slug', $ruleData['category'])
                    ->orWhere('id', $ruleData['category'])
                    ->first();
                if ($category) {
                    $categoryId = $category->id;
                }
            }

            $rule = TajweedRule::updateOrCreate(
                ['slug' => $slug],
                [
                    'tajweed_rule_category_id' => $categoryId,
                    'color_code' => $ruleData['color_code'] ?? null,
                    'example_text' => $ruleData['example_text'] ?? null,
                    'priority' => $ruleData['priority'] ?? 0,
                    'is_active' => filter_var($ruleData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ]
            );

            // Handle translations
            $translations = [];
            foreach (['en', 'ku', 'ar'] as $locale) {
                $tName = $ruleData["name_{$locale}"] ?? ($locale === 'en' ? $ruleData['name'] : ($locale === 'ku' ? $ruleData['name_ku'] : ($locale === 'ar' ? $ruleData['name_ar'] : null)));
                $tDesc = $ruleData["description_{$locale}"] ?? ($locale === 'en' ? $ruleData['description'] : ($locale === 'ku' ? $ruleData['description_ku'] : null));

                if ($tName) {
                    $translations[$locale] = [
                        'name' => $tName,
                        'description' => $tDesc,
                    ];
                }
            }

            if (!empty($translations)) {
                $rule->saveTranslationsFromArray($translations);
            }

            $imported++;
        }

        return redirect()->route('tajweed-rules.index')->with('success', "Imported {$imported} Tajweed Rules successfully.");
    }

    public function export()
    {
        $this->authorizeAdmin();

        $rules = TajweedRule::with(['translations', 'category'])->orderBy('priority', 'desc')->get();

        $data = $rules->map(function (TajweedRule $rule) {
            $row = [
                'slug'          => $rule->slug,
                'color_code'    => $rule->color_code,
                'category_slug' => $rule->category?->slug,
                'example_text'  => $rule->example_text,
                'priority'      => $rule->priority,
                'is_active'     => (bool) $rule->is_active,
            ];
            foreach ($rule->translations as $t) {
                $row["name_{$t->locale}"]        = $t->name;
                $row["description_{$t->locale}"] = $t->description;
            }
            return $row;
        });

        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'tajweed_rules_export_' . now()->format('Ymd_His') . '.json';

        return response($json, 200, [
            'Content-Type'        => 'application/json; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}