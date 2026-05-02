<?php
// app/Http/Controllers/TajweedRuleController.php

namespace App\Http\Controllers;

use App\Models\TajweedRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TajweedRuleController extends Controller
{
    /**
     * Display a listing of the tajweed rules.
     */
    public function index(Request $request)
    {
        $query = TajweedRule::withCount('ayahTajweedSegments');

        // فلتەر بەپێی کەتێگۆری
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // فلتەر بەپێی دۆخی چالاکی
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // گەڕان بەپێی ناو یان وەسف
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $tajweedRules = $query->orderBy('category')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $categories = TajweedRule::distinct()
            ->whereNotNull('category')
            ->pluck('category');

        $stats = [
            'total_rules' => TajweedRule::count(),
            'active_rules' => TajweedRule::where('is_active', true)->count(),
            'total_segments' => \App\Models\AyahTajweedSegment::count(),
            'categories_count' => TajweedRule::distinct('category')->whereNotNull('category')->count('category'),
        ];

        return view('tajweed-rules.index', compact('tajweedRules', 'categories', 'stats'));
    }

    /**
     * Show the form for creating a new tajweed rule.
     */
    public function create()
    {
        $this->authorizeAdmin();

        $categories = $this->getTajweedCategories();
        $colorPalette = $this->getColorPalette();

        return view('tajweed-rules.create', compact('categories', 'colorPalette'));
    }

    /**
     * Store a newly created tajweed rule in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tajweed_rules,name',
            'category' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:20',
            'description' => 'required|string',
            'example_text' => 'nullable|string',
            'priority' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // پشکنینی دووبارە نەبوونی slug
        $count = 1;
        $originalSlug = $validated['slug'];
        while (TajweedRule::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        $tajweedRule = TajweedRule::create($validated);

        return redirect()
            ->route('tajweed-rules.show', $tajweedRule)
            ->with('success', __('tajweed_rules.messages.created'));
    }

    /**
     * Display the specified tajweed rule.
     */
    public function show(TajweedRule $tajweedRule)
    {
        $tajweedRule->load(['ayahTajweedSegments.ayah.surah']);

        $segments = $tajweedRule->ayahTajweedSegments()
            ->with(['ayah.surah'])
            ->orderBy('ayah_id')
            ->paginate(20);

        return view('tajweed-rules.show', compact('tajweedRule', 'segments'));
    }

    /**
     * Show the form for editing the specified tajweed rule.
     */
    public function edit(TajweedRule $tajweedRule)
    {
        $this->authorizeAdmin();

        $categories = $this->getTajweedCategories();
        $colorPalette = $this->getColorPalette();

        return view('tajweed-rules.edit', compact('tajweedRule', 'categories', 'colorPalette'));
    }

    /**
     * Update the specified tajweed rule in storage.
     */
    public function update(Request $request, TajweedRule $tajweedRule)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tajweed_rules,name,' . $tajweedRule->id,
            'category' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:20',
            'description' => 'required|string',
            'example_text' => 'nullable|string',
            'priority' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // پشکنینی دووبارە نەبوونی slug
        $count = 1;
        $originalSlug = $validated['slug'];
        while (TajweedRule::where('slug', $validated['slug'])->where('id', '!=', $tajweedRule->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        $tajweedRule->update($validated);

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
}