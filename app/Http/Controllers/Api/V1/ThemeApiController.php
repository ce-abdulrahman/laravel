<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\ThemeCategory;
use App\Models\ThemeDownload;
use App\Models\ThemeUsageLog;
use App\Models\UserTheme;
use App\Models\UserThemePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ThemeApiController extends Controller
{
    /**
     * Get all categories and themes.
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();

        $categories = ThemeCategory::active()
            ->ordered()
            ->with(['translations'])
            ->get();

        $themes = Theme::where('is_active', true)
            ->with(['translations', 'assets'])
            ->orderBy('sort_order')
            ->get();

        // If user is authenticated, get their custom selections
        $userThemes = [];
        $userPreferences = [];
        if ($user) {
            $userThemes = UserTheme::where('user_id', $user->id)
                ->get()
                ->keyBy('theme_id');

            $userPreferences = UserThemePreference::where('user_id', $user->id)
                ->get()
                ->keyBy('theme_id');
        }

        // Format payload
        $formattedThemes = $themes->map(function ($theme) use ($user, $userThemes, $userPreferences) {
            $userTheme = $user ? ($userThemes[$theme->id] ?? null) : null;
            $pref = $user ? ($userPreferences[$theme->id] ?? null) : null;

            // Unlock logic
            $isUnlocked = true;
            if ($theme->unlock_type !== 'free') {
                $isUnlocked = $userTheme && $userTheme->unlocked_at !== null;
            }

            return [
                'id' => $theme->id,
                'theme_key' => $theme->theme_key,
                'category_id' => $theme->category_id,
                'name' => $theme->name,
                'description' => $theme->description,
                'preview_image' => $theme->preview_image ? asset($theme->preview_image) : null,
                'thumbnail' => $theme->thumbnail ? asset($theme->thumbnail) : null,
                'version' => $theme->version,
                'is_featured' => $theme->is_featured,
                'unlock_type' => $theme->unlock_type,
                'unlock_value' => $theme->unlock_value,
                'min_app_version' => $theme->min_app_version,
                'max_app_version' => $theme->max_app_version,
                'theme_metadata' => $theme->theme_metadata,
                'is_active' => $userTheme ? $userTheme->is_active : false,
                'is_favorite' => $userTheme ? $userTheme->is_favorite : false,
                'is_unlocked' => $isUnlocked,
                'unlocked_at' => $userTheme ? ($userTheme->unlocked_at ? $userTheme->unlocked_at->toIso8601String() : null) : null,
                'preferences' => $pref ? [
                    'sound_enabled' => $pref->sound_enabled,
                    'haptic_enabled' => $pref->haptic_enabled,
                    'animation_enabled' => $pref->animation_enabled,
                    'custom_ring_color' => $pref->custom_ring_color,
                    'custom_font_scale' => $pref->custom_font_scale,
                ] : null,
                'assets' => $theme->assets->map(function ($asset) {
                    return [
                        'asset_type' => $asset->asset_type,
                        'file_path' => asset($asset->file_path),
                        'file_size' => $asset->file_size,
                        'checksum' => $asset->checksum,
                        'version' => $asset->version,
                    ];
                }),
            ];
        });

        $formattedCategories = $categories->map(function ($cat) use ($formattedThemes) {
            return [
                'id' => $cat->id,
                'icon' => $cat->icon,
                'name' => $cat->name,
                'themes' => $formattedThemes->where('category_id', $cat->id)->values(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedCategories
        ]);
    }

    /**
     * Get single theme.
     */
    public function show($id)
    {
        $theme = Theme::where('is_active', true)
            ->with(['translations', 'assets'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $theme->id,
                'theme_key' => $theme->theme_key,
                'name' => $theme->name,
                'description' => $theme->description,
                'preview_image' => $theme->preview_image ? asset($theme->preview_image) : null,
                'thumbnail' => $theme->thumbnail ? asset($theme->thumbnail) : null,
                'version' => $theme->version,
                'is_featured' => $theme->is_featured,
                'unlock_type' => $theme->unlock_type,
                'unlock_value' => $theme->unlock_value,
                'theme_metadata' => $theme->theme_metadata,
                'assets' => $theme->assets,
            ]
        ]);
    }

    /**
     * Apply/activate a theme.
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme_id' => 'required_without:theme_key|exists:themes,id',
            'theme_key' => 'required_without:theme_id|exists:themes,theme_key',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user(); // Sanctum protected
        
        $theme = null;
        if ($request->has('theme_id')) {
            $theme = Theme::findOrFail($request->theme_id);
        } else {
            $theme = Theme::where('theme_key', $request->theme_key)->firstOrFail();
        }

        // Check if unlocked
        if ($theme->unlock_type !== 'free') {
            $ut = UserTheme::where('user_id', $user->id)
                ->where('theme_id', $theme->id)
                ->first();
            if (!$ut || $ut->unlocked_at === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Theme is locked. Please unlock it first.'
                ], 403);
            }
        }

        DB::transaction(function () use ($user, $theme) {
            // Set all user themes to inactive
            UserTheme::where('user_id', $user->id)->update(['is_active' => false]);

            // Set this one to active
            UserTheme::updateOrCreate(
                ['user_id' => $user->id, 'theme_id' => $theme->id],
                ['is_active' => true, 'unlocked_at' => now()]
            );

            // Log usage
            ThemeUsageLog::create([
                'user_id' => $user->id,
                'theme_id' => $theme->id,
                'event_type' => 'apply',
            ]);

            // Initialize default preferences if empty
            UserThemePreference::firstOrCreate(
                ['user_id' => $user->id, 'theme_id' => $theme->id],
                [
                    'sound_enabled' => true,
                    'haptic_enabled' => true,
                    'animation_enabled' => true,
                    'custom_ring_color' => null,
                    'custom_font_scale' => 1.0,
                ]
            );
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Theme applied successfully',
            'data' => [
                'theme_id' => $theme->id,
                'theme_key' => $theme->theme_key,
                'preferences' => UserThemePreference::where('user_id', $user->id)
                    ->where('theme_id', $theme->id)
                    ->first()
            ]
        ]);
    }

    /**
     * Toggle theme favorite status.
     */
    public function favorite(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
        ]);

        $user = auth()->user();
        $themeId = $request->theme_id;

        $ut = UserTheme::where('user_id', $user->id)
            ->where('theme_id', $themeId)
            ->first();

        $newFavorite = true;
        if ($ut) {
            $newFavorite = !$ut->is_favorite;
            $ut->update(['is_favorite' => $newFavorite]);
        } else {
            UserTheme::create([
                'user_id' => $user->id,
                'theme_id' => $themeId,
                'is_active' => false,
                'is_favorite' => true,
                'unlocked_at' => now(), // if they favorite it, unlocked implicitly if free
            ]);
        }

        // Log usage
        ThemeUsageLog::create([
            'user_id' => $user->id,
            'theme_id' => $themeId,
            'event_type' => $newFavorite ? 'favorite' : 'unfavorite',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $newFavorite ? 'Added to favorites' : 'Removed from favorites',
            'data' => [
                'is_favorite' => $newFavorite
            ]
        ]);
    }

    /**
     * Log a theme asset download.
     */
    public function download(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'version' => 'required|integer',
        ]);

        $user = auth()->user();

        ThemeDownload::create([
            'user_id' => $user->id,
            'theme_id' => $request->theme_id,
            'version' => $request->version,
        ]);

        ThemeUsageLog::create([
            'user_id' => $user->id,
            'theme_id' => $request->theme_id,
            'event_type' => 'download',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Theme download logged successfully'
        ]);
    }

    /**
     * Synchronize offline client themes state.
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'active_theme_key' => 'nullable|string|exists:themes,theme_key',
            'favorite_theme_keys' => 'nullable|array',
            'favorite_theme_keys.*' => 'string|exists:themes,theme_key',
            'preferences' => 'nullable|array',
            'preferences.*.theme_key' => 'required|string|exists:themes,theme_key',
            'preferences.*.sound_enabled' => 'required|boolean',
            'preferences.*.haptic_enabled' => 'required|boolean',
            'preferences.*.animation_enabled' => 'required|boolean',
            'preferences.*.custom_ring_color' => 'nullable|string|max:7',
            'preferences.*.custom_font_scale' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sync payload validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        DB::transaction(function () use ($user, $request) {
            // 1. Sync Active Theme
            if ($request->filled('active_theme_key')) {
                $activeTheme = Theme::where('theme_key', $request->active_theme_key)->first();
                if ($activeTheme) {
                    UserTheme::where('user_id', $user->id)->update(['is_active' => false]);
                    UserTheme::updateOrCreate(
                        ['user_id' => $user->id, 'theme_id' => $activeTheme->id],
                        ['is_active' => true, 'unlocked_at' => now()]
                    );
                }
            }

            // 2. Sync Favorites
            if ($request->has('favorite_theme_keys')) {
                $favKeys = $request->favorite_theme_keys;
                $favThemeIds = Theme::whereIn('theme_key', $favKeys)->pluck('id')->toArray();

                // Reset all favorites first
                UserTheme::where('user_id', $user->id)->update(['is_favorite' => false]);

                // Mark keys as favorites
                foreach ($favThemeIds as $tid) {
                    UserTheme::updateOrCreate(
                        ['user_id' => $user->id, 'theme_id' => $tid],
                        ['is_favorite' => true, 'unlocked_at' => now()]
                    );
                }
            }

            // 3. Sync Custom Preferences
            if ($request->has('preferences')) {
                foreach ($request->preferences as $prefData) {
                    $theme = Theme::where('theme_key', $prefData['theme_key'])->first();
                    if ($theme) {
                        UserThemePreference::updateOrCreate(
                            ['user_id' => $user->id, 'theme_id' => $theme->id],
                            [
                                'sound_enabled' => $prefData['sound_enabled'],
                                'haptic_enabled' => $prefData['haptic_enabled'],
                                'animation_enabled' => $prefData['animation_enabled'],
                                'custom_ring_color' => $prefData['custom_ring_color'] ?? null,
                                'custom_font_scale' => $prefData['custom_font_scale'] ?? 1.0,
                            ]
                        );
                    }
                }
            }
        });

        // Return current fresh state
        return $this->index($request);
    }

    /**
     * Save user theme custom overrides.
     */
    public function savePreferences(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'sound_enabled' => 'required|boolean',
            'haptic_enabled' => 'required|boolean',
            'animation_enabled' => 'required|boolean',
            'custom_ring_color' => 'nullable|string|max:7', // e.g. #ff0000
            'custom_font_scale' => 'required|numeric|min:0.5|max:2.0',
        ]);

        $user = auth()->user();

        $pref = UserThemePreference::updateOrCreate(
            ['user_id' => $user->id, 'theme_id' => $request->theme_id],
            [
                'sound_enabled' => $request->sound_enabled,
                'haptic_enabled' => $request->haptic_enabled,
                'animation_enabled' => $request->animation_enabled,
                'custom_ring_color' => $request->custom_ring_color,
                'custom_font_scale' => $request->custom_font_scale,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Theme preferences saved successfully',
            'data' => $pref
        ]);
    }
}
