<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Theme;
use App\Models\ThemeCategory;
use App\Models\ThemeCategoryTranslation;
use App\Models\ThemeDownload;
use App\Models\ThemeTranslation;
use App\Models\ThemeUsageLog;
use App\Models\UserTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminThemeController extends Controller
{
    /**
     * Display themes dashboard.
     */
    public function dashboard()
    {
        $totalThemes = Theme::count();
        $activeThemes = Theme::where('is_active', true)->count();
        $featuredThemes = Theme::where('is_featured', true)->count();
        $totalCategories = ThemeCategory::count();

        // Analytics metrics
        $totalDownloads = ThemeDownload::count();
        $totalFavorites = UserTheme::where('is_favorite', true)->count();

        $mostUsedTheme = Theme::join('user_themes', 'themes.id', '=', 'user_themes.theme_id')
            ->select('themes.theme_key', DB::raw('count(user_themes.id) as count'))
            ->where('user_themes.is_active', true)
            ->groupBy('themes.id', 'themes.theme_key')
            ->orderByDesc('count')
            ->first();

        $topDownloads = Theme::join('theme_downloads', 'themes.id', '=', 'theme_downloads.theme_id')
            ->select('themes.theme_key', DB::raw('count(theme_downloads.id) as count'))
            ->groupBy('themes.id', 'themes.theme_key')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $topFavorites = Theme::join('user_themes', 'themes.id', '=', 'user_themes.theme_id')
            ->select('themes.theme_key', DB::raw('count(user_themes.id) as count'))
            ->where('user_themes.is_favorite', true)
            ->groupBy('themes.id', 'themes.theme_key')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return view('admin.themes.dashboard', compact(
            'totalThemes',
            'activeThemes',
            'featuredThemes',
            'totalCategories',
            'totalDownloads',
            'totalFavorites',
            'mostUsedTheme',
            'topDownloads',
            'topFavorites'
        ));
    }

    /**
     * List themes.
     */
    public function index(Request $request)
    {
        $query = Theme::with(['category.translations', 'translations']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('theme_key', 'like', "%{$q}%")
                    ->orWhereHas('translations', function ($t) use ($q) {
                        $t->where('value', 'like', "%{$q}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === '1');
        }

        $themes = $query->orderBy('sort_order')->paginate(15)->withQueryString();
        $categories = ThemeCategory::with('translations')->get();

        return view('admin.themes.index', compact('themes', 'categories'));
    }

    /**
     * Show create theme form.
     */
    public function create()
    {
        $categories = ThemeCategory::with('translations')->get();
        $languages = Language::activeList();

        return view('admin.themes.create', compact('categories', 'languages'));
    }

    /**
     * Store new theme.
     */
    public function store(Request $request)
    {
        $request->validate([
            'theme_key' => 'required|string|max:100|unique:themes,theme_key',
            'category_id' => 'required|exists:theme_categories,id',
            'preview_image' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|string|max:255',
            'unlock_type' => 'required|string|in:free,points,achievement,streak,event,premium',
            'unlock_value' => 'nullable|string|max:255',
            'min_app_version' => 'nullable|string|max:20',
            'max_app_version' => 'nullable|string|max:20',
            'sort_order' => 'required|integer|min:0',
            'theme_metadata' => 'required|json',
            'translations' => 'required|array',
            'translations.*.*' => 'required|string',
        ]);

        $metadata = json_decode($request->theme_metadata, true);

        // Ensure schema_version is present
        if (!isset($metadata['schema_version'])) {
            $metadata['schema_version'] = 1;
        }

        DB::transaction(function () use ($request, $metadata) {
            $theme = Theme::create([
                'theme_key' => $request->theme_key,
                'category_id' => $request->category_id,
                'preview_image' => $request->preview_image,
                'thumbnail' => $request->thumbnail,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'unlock_type' => $request->unlock_type,
                'unlock_value' => $request->unlock_value,
                'min_app_version' => $request->min_app_version,
                'max_app_version' => $request->max_app_version,
                'theme_metadata' => $metadata,
                'sort_order' => $request->sort_order,
                'version' => 1,
            ]);

            // Save translations
            foreach ($request->translations as $langCode => $fields) {
                $lang = Language::where('code', $langCode)->first();
                if ($lang) {
                    foreach ($fields as $field => $val) {
                        ThemeTranslation::create([
                            'theme_id' => $theme->id,
                            'language_id' => $lang->id,
                            'field' => $field,
                            'value' => $val,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.themes.index')->with('success', 'Theme created successfully.');
    }

    /**
     * Show edit theme form.
     */
    public function edit($id)
    {
        $theme = Theme::with('translations')->findOrFail($id);
        $categories = ThemeCategory::with('translations')->get();
        $languages = Language::activeList();

        // Format translations for the view
        $themeTranslations = [];
        foreach ($theme->translations as $trans) {
            $langCode = $trans->language->code;
            $themeTranslations[$langCode][$trans->field] = $trans->value;
        }

        return view('admin.themes.edit', compact('theme', 'categories', 'languages', 'themeTranslations'));
    }

    /**
     * Update theme.
     */
    public function update(Request $request, $id)
    {
        $theme = Theme::findOrFail($id);

        $request->validate([
            'theme_key' => 'required|string|max:100|unique:themes,theme_key,' . $theme->id,
            'category_id' => 'required|exists:theme_categories,id',
            'preview_image' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|string|max:255',
            'unlock_type' => 'required|string|in:free,points,achievement,streak,event,premium',
            'unlock_value' => 'nullable|string|max:255',
            'min_app_version' => 'nullable|string|max:20',
            'max_app_version' => 'nullable|string|max:20',
            'sort_order' => 'required|integer|min:0',
            'theme_metadata' => 'required|json',
            'translations' => 'required|array',
            'translations.*.*' => 'required|string',
        ]);

        $metadata = json_decode($request->theme_metadata, true);
        if (!isset($metadata['schema_version'])) {
            $metadata['schema_version'] = 1;
        }

        DB::transaction(function () use ($request, $theme, $metadata) {
            $theme->update([
                'theme_key' => $request->theme_key,
                'category_id' => $request->category_id,
                'preview_image' => $request->preview_image,
                'thumbnail' => $request->thumbnail,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'unlock_type' => $request->unlock_type,
                'unlock_value' => $request->unlock_value,
                'min_app_version' => $request->min_app_version,
                'max_app_version' => $request->max_app_version,
                'theme_metadata' => $metadata,
                'sort_order' => $request->sort_order,
                'version' => $theme->version + 1, // Bump version
            ]);

            // Sync translations
            ThemeTranslation::where('theme_id', $theme->id)->delete();
            foreach ($request->translations as $langCode => $fields) {
                $lang = Language::where('code', $langCode)->first();
                if ($lang) {
                    foreach ($fields as $field => $val) {
                        ThemeTranslation::create([
                            'theme_id' => $theme->id,
                            'language_id' => $lang->id,
                            'field' => $field,
                            'value' => $val,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.themes.index')->with('success', 'Theme updated successfully.');
    }

    /**
     * Delete theme.
     */
    public function destroy($id)
    {
        $theme = Theme::findOrFail($id);
        $theme->delete();

        return redirect()->route('admin.themes.index')->with('success', 'Theme deleted successfully.');
    }

    /**
     * Manage categories.
     */
    public function categories(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'icon' => 'required|string|max:50',
                'sort_order' => 'required|integer|min:0',
                'translations' => 'required|array',
                'translations.*.name' => 'required|string|max:255',
            ]);

            DB::transaction(function () use ($request) {
                $category = ThemeCategory::create([
                    'icon' => $request->icon,
                    'sort_order' => $request->sort_order,
                    'is_active' => true,
                ]);

                foreach ($request->translations as $langCode => $fields) {
                    $lang = Language::where('code', $langCode)->first();
                    if ($lang) {
                        ThemeCategoryTranslation::create([
                            'theme_category_id' => $category->id,
                            'language_id' => $lang->id,
                            'field' => 'name',
                            'value' => $fields['name'],
                        ]);
                    }
                }
            });

            return redirect()->route('admin.themes.categories')->with('success', 'Category created successfully.');
        }

        $categories = ThemeCategory::with('translations')->orderBy('sort_order')->get();
        $languages = Language::activeList();

        return view('admin.themes.categories', compact('categories', 'languages'));
    }

    /**
     * Update category status or details.
     */
    public function updateCategory(Request $request, $id)
    {
        $category = ThemeCategory::findOrFail($id);

        if ($request->has('toggle_active')) {
            $category->update(['is_active' => !$category->is_active]);
            return redirect()->route('admin.themes.categories')->with('success', 'Category status updated.');
        }

        $request->validate([
            'icon' => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $category) {
            $category->update([
                'icon' => $request->icon,
                'sort_order' => $request->sort_order,
            ]);

            ThemeCategoryTranslation::where('theme_category_id', $category->id)->delete();
            foreach ($request->translations as $langCode => $fields) {
                $lang = Language::where('code', $langCode)->first();
                if ($lang) {
                    ThemeCategoryTranslation::create([
                        'theme_category_id' => $category->id,
                        'language_id' => $lang->id,
                        'field' => 'name',
                        'value' => $fields['name'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.themes.categories')->with('success', 'Category updated successfully.');
    }

    /**
     * Delete Category.
     */
    public function destroyCategory($id)
    {
        $category = ThemeCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.themes.categories')->with('success', 'Category deleted.');
    }

    /**
     * Theme analytics view.
     */
    public function analytics()
    {
        $downloadsTimeline = ThemeDownload::select(
            DB::raw('date(downloaded_at) as date'),
            DB::raw('count(id) as count')
        )
            ->where('downloaded_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $eventDistribution = ThemeUsageLog::select('event_type', DB::raw('count(id) as count'))
            ->groupBy('event_type')
            ->get();

        $adoptionRates = Theme::leftJoin('user_themes', function ($join) {
                $join->on('themes.id', '=', 'user_themes.theme_id')
                    ->where('user_themes.is_active', '=', true);
            })
            ->select('themes.theme_key', DB::raw('count(user_themes.id) as active_count'))
            ->groupBy('themes.id', 'themes.theme_key')
            ->orderByDesc('active_count')
            ->get();

        return view('admin.themes.analytics', compact('downloadsTimeline', 'eventDistribution', 'adoptionRates'));
    }
}
