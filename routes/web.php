<?php

use App\Http\Controllers\AudioFileController;
use App\Http\Controllers\AyahController;
use App\Http\Controllers\AyahTajweedSegmentController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MemorizationPlanController;
use App\Http\Controllers\MemorizationReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QiraatController;
use App\Http\Controllers\QiraatTextController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ReadingHistoryController;
use App\Http\Controllers\ReciterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SurahController;
use App\Http\Controllers\TafsirBookController;
use App\Http\Controllers\TafsirController;
use App\Http\Controllers\TajweedRuleController;
use App\Http\Controllers\TajweedRuleCategoryController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserAyahProgressController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AdhkarCategoryController;
use App\Http\Controllers\AdhkarController;
use App\Http\Controllers\TasbihController;
use App\Http\Controllers\HadithCategoryController;
use App\Http\Controllers\HadithController;
use App\Http\Controllers\ReminderTemplateController;
use App\Http\Controllers\AdminReminderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $surahs = \App\Models\Surah::active()->orderBy('number')->get();
    return view('welcome', compact('surahs'));
});

Route::get('/read/surah/{surah}', [App\Http\Controllers\ReadController::class, 'show'])->name('read.surah');
Route::get('/read/juz/{juz}', [App\Http\Controllers\ReadController::class, 'juz'])->name('read.juz');
Route::get('/read/page/{page}', [App\Http\Controllers\ReadController::class, 'page'])->name('read.page');

Route::get('/dashboard', function () {
    $stats = [
        'users' => \App\Models\User::count(),
        'hadiths' => \App\Models\Hadith::count(),
        'hadith_categories' => \App\Models\HadithCategory::count(),
        'adhkars' => \App\Models\Adhkar::count(),
        'adhkar_categories' => \App\Models\AdhkarCategory::count(),
        'tasbihs' => \App\Models\Tasbih::count(),
        'surahs' => \App\Models\Surah::count(),
        'ayahs' => \App\Models\Ayah::count(),
        'reciters' => \App\Models\Reciter::count(),
        'audio_files' => \App\Models\AudioFile::count(),
        'banners' => \App\Models\Banner::count(),
    ];

    if (auth()->user()?->role === 'admin') {
        $locale = app()->getLocale();
        $cacheKey = "translation_dashboard_stats.admin.{$locale}";

        $translationStats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () {
            $activeLanguages = \App\Models\Language::activeList();
            if ($activeLanguages->isEmpty()) {
                return [
                    'total_languages' => 0,
                    'total_translation_records' => 0,
                    'missing_translations' => 0,
                    'translation_coverage' => 0.0,
                    'active_locales' => [],
                ];
            }

            $activeCodes = $activeLanguages->pluck('code')->toArray();
            $activeLangIds = $activeLanguages->pluck('id')->toArray();

            // Total Languages
            $totalLanguages = $activeLanguages->count();

            // Total Translation Records
            $totalTranslationRecords = 
                \App\Models\SurahTranslation::count() +
                \App\Models\TajweedRuleTranslation::count() +
                \App\Models\TajweedRuleCategoryTranslation::count() +
                \App\Models\HadithCategoryTranslation::count() +
                \App\Models\HadithTranslation::count() +
                \App\Models\AdhkarCategoryTranslation::count() +
                \App\Models\AdhkarTranslation::count() +
                \App\Models\Translation::count() +
                \App\Models\UiTranslation::count();

            // Expected translation units across active languages
            $activeCount = count($activeCodes);
            $expectedUnits = (
                \App\Models\Surah::count() +
                \App\Models\HadithCategory::count() +
                \App\Models\Hadith::count() +
                \App\Models\AdhkarCategory::count() +
                \App\Models\Adhkar::count() +
                \App\Models\TajweedRuleCategory::count() +
                \App\Models\TajweedRule::count() +
                \App\Models\Ayah::count() +
                \App\Models\TranslationKey::count()
            ) * $activeCount;

            // Completed translation units
            $completedSurah = \App\Models\SurahTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('name')->where('name', '!=', '')->count();

            $completedHadithCat = \App\Models\HadithCategoryTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('name')->where('name', '!=', '')->count();

            $completedHadith = \App\Models\HadithTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('translation')->where('translation', '!=', '')
                ->whereNotNull('explanation')->where('explanation', '!=', '')->count();

            $completedAdhkarCat = \App\Models\AdhkarCategoryTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('name')->where('name', '!=', '')->count();

            $completedAdhkar = \App\Models\AdhkarTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('translation')->where('translation', '!=', '')->count();

            $completedTajweedCat = \App\Models\TajweedRuleCategoryTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('name')->where('name', '!=', '')
                ->whereNotNull('description')->where('description', '!=', '')->count();

            $completedTajweedRule = \App\Models\TajweedRuleTranslation::whereIn('locale', $activeCodes)
                ->whereNotNull('name')->where('name', '!=', '')
                ->whereNotNull('description')->where('description', '!=', '')->count();

            $completedAyah = \App\Models\Translation::whereIn('language_code', $activeCodes)
                ->whereNotNull('content')->where('content', '!=', '')->count();

            $completedUi = \App\Models\UiTranslation::whereIn('language_id', $activeLangIds)
                ->whereNotNull('value')->where('value', '!=', '')->count();

            $completedUnits = $completedSurah + $completedHadithCat + $completedHadith + $completedAdhkarCat + 
                              $completedAdhkar + $completedTajweedCat + $completedTajweedRule + $completedAyah + $completedUi;

            $missingCount = max(0, $expectedUnits - $completedUnits);
            $coveragePct = $expectedUnits > 0 ? round(($completedUnits / $expectedUnits) * 100, 2) : 0.0;

            return [
                'total_languages' => $totalLanguages,
                'total_translation_records' => $totalTranslationRecords,
                'missing_translations' => $missingCount,
                'translation_coverage' => $coveragePct,
                'active_locales' => $activeCodes,
            ];
        });

        $stats = array_merge($stats, $translationStats);
    }

    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/lang/{code}', [LanguageController::class, 'switchLang'])->name('lang.switch');
Route::get('/language/current', [LanguageController::class, 'getCurrentLanguage'])->name('language.current');

// JavaScript Localization Bridge — served publicly, cached per locale
Route::get('/localization/js-translations', [App\Http\Controllers\LocalizationJsController::class, 'translations'])
    ->name('localization.js-translations');

Route::middleware('auth')->group(function () {
    Route::get('/juz', [App\Http\Controllers\ReadController::class, 'juzIndex'])->name('juz.index');
    Route::get('/page', [App\Http\Controllers\ReadController::class, 'pageIndex'])->name('page.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Unified resource routes for authenticated users (permissions handled at controller level)
    Route::resource('surahs', SurahController::class);
    Route::resource('languages', LanguageController::class);
    Route::resource('ayahs', AyahController::class);
    Route::resource('tajweed-rules', TajweedRuleController::class);
    Route::resource('tajweed-rule-categories', TajweedRuleCategoryController::class);
    Route::resource('reciters', ReciterController::class);
    Route::resource('audio-files', AudioFileController::class);
    Route::resource('qiraats', QiraatController::class);
    Route::resource('qiraat-texts', QiraatTextController::class);
    Route::resource('tafsir-books', TafsirBookController::class);
    Route::resource('tafsirs', TafsirController::class);
    Route::resource('translations', TranslationController::class);

    // Admin-only write and utility endpoints
    Route::middleware(['admin'])->group(function () {
        Route::post('surahs/import', [SurahController::class, 'import'])->name('surahs.import');
        
        // ── User Management ──────────────────────────────────────────────────────
        Route::get('admin/users/dashboard', [App\Http\Controllers\AdminUserController::class, 'dashboard'])->name('admin.users.dashboard');
        Route::get('admin/users', [App\Http\Controllers\AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('admin/users/{id}', [App\Http\Controllers\AdminUserController::class, 'show'])->name('admin.users.show')->whereNumber('id');
        Route::post('admin/users/{id}/suspend', [App\Http\Controllers\AdminUserController::class, 'suspend'])->name('admin.users.suspend')->whereNumber('id');
        Route::post('admin/users/{id}/unsuspend', [App\Http\Controllers\AdminUserController::class, 'unsuspend'])->name('admin.users.unsuspend')->whereNumber('id');
        Route::post('admin/users/{id}/force-logout', [App\Http\Controllers\AdminUserController::class, 'forceLogout'])->name('admin.users.force-logout')->whereNumber('id');
        Route::post('admin/users/{id}/reset-password', [App\Http\Controllers\AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password')->whereNumber('id');
        Route::post('ayahs/import', [AyahController::class, 'import'])->name('ayahs.import');
        Route::post('tajweed-rules/import', [TajweedRuleController::class, 'import'])->name('tajweed-rules.import');
        Route::post('tajweed-segments/import', [AyahTajweedSegmentController::class, 'import'])->name('tajweed-segments.import');
        Route::get('tajweed-segments/export', [AyahTajweedSegmentController::class, 'export'])->name('tajweed-segments.export');
        Route::post('tajweed-segments/rebuild', [AyahTajweedSegmentController::class, 'rebuild'])->name('tajweed-segments.rebuild');
        Route::resource('tajweed-segments', AyahTajweedSegmentController::class);
        Route::post('audio-files/upload', [AudioFileController::class, 'upload'])->name('audio-files.upload');
        Route::post('tafsirs/import', [TafsirController::class, 'import'])->name('tafsirs.import');
        Route::post('translations/import', [TranslationController::class, 'import'])->name('translations.import');
        
        // Tasbih Streak management routes
        Route::get('user-streaks', [App\Http\Controllers\UserStreakController::class, 'index'])->name('user-streaks.index');
        Route::post('user-streaks/{id}/reset', [App\Http\Controllers\UserStreakController::class, 'reset'])->name('user-streaks.reset');
        Route::post('user-streaks/{id}/edit', [App\Http\Controllers\UserStreakController::class, 'update'])->name('user-streaks.update');
        Route::get('user-streaks/export', [App\Http\Controllers\UserStreakController::class, 'exportCsv'])->name('user-streaks.export');

        // Daily Goals management routes
        Route::get('user-goals', [App\Http\Controllers\UserGoalController::class, 'index'])->name('user-goals.index');
        Route::post('user-goals/{id}/reset', [App\Http\Controllers\UserGoalController::class, 'reset'])->name('user-goals.reset');
        Route::post('user-goals/{id}/edit', [App\Http\Controllers\UserGoalController::class, 'update'])->name('user-goals.update');
        Route::get('user-goals/export', [App\Http\Controllers\UserGoalController::class, 'exportCsv'])->name('user-goals.export');

        // Default Goal Templates CRUD routes
        Route::resource('daily-goal-templates', App\Http\Controllers\DailyGoalTemplateController::class)->except(['show']);

        // Goal Progress management routes
        Route::get('user-goal-progress', [App\Http\Controllers\AdminUserGoalProgressController::class, 'index'])->name('user-goal-progress.index');
        Route::post('user-goal-progress/{id}/reset', [App\Http\Controllers\AdminUserGoalProgressController::class, 'reset'])->name('user-goal-progress.reset');
        Route::post('user-goal-progress/{id}/edit', [App\Http\Controllers\AdminUserGoalProgressController::class, 'update'])->name('user-goal-progress.update');
        Route::post('user-goal-progress/{id}/force-complete', [App\Http\Controllers\AdminUserGoalProgressController::class, 'forceComplete'])->name('user-goal-progress.force-complete');
        Route::get('user-goal-progress/export', [App\Http\Controllers\AdminUserGoalProgressController::class, 'exportCsv'])->name('user-goal-progress.export');

        // ── Achievement System ────────────────────────────────────────────────────
        Route::resource('achievements', App\Http\Controllers\AchievementController::class);
        Route::resource('achievement-categories', App\Http\Controllers\AchievementCategoryController::class);

        // User Achievements: view, grant, revoke, reset
        Route::get('user-achievements', [App\Http\Controllers\AdminUserAchievementController::class, 'index'])->name('user-achievements.index');
        Route::get('user-achievements/analytics', [App\Http\Controllers\AdminUserAchievementController::class, 'analytics'])->name('user-achievements.analytics');
        Route::post('user-achievements/users/{user}/grant', [App\Http\Controllers\AdminUserAchievementController::class, 'grant'])->name('user-achievements.grant');
        Route::delete('user-achievements/{userAchievement}/revoke', [App\Http\Controllers\AdminUserAchievementController::class, 'revoke'])->name('user-achievements.revoke');
        Route::post('user-achievements/{userAchievement}/reset', [App\Http\Controllers\AdminUserAchievementController::class, 'reset'])->name('user-achievements.reset');

        // ── Leaderboard System ──────────────────────────────────────────────────
        Route::get('admin/leaderboard/overview', [App\Http\Controllers\LeaderboardAdminController::class, 'overview'])->name('admin.leaderboard.overview');
        Route::get('admin/leaderboard', [App\Http\Controllers\LeaderboardAdminController::class, 'index'])->name('admin.leaderboard.index');
        Route::get('admin/leaderboard/config', [App\Http\Controllers\LeaderboardAdminController::class, 'config'])->name('admin.leaderboard.config');
        Route::post('admin/leaderboard/config/save', [App\Http\Controllers\LeaderboardAdminController::class, 'saveConfig'])->name('admin.leaderboard.config.save');
        Route::get('admin/leaderboard/analytics', [App\Http\Controllers\LeaderboardAdminController::class, 'analytics'])->name('admin.leaderboard.analytics');

        // ── Tasbih Sessions System ──────────────────────────────────────────────
        Route::get('admin/sessions/overview', [App\Http\Controllers\TasbihSessionAdminController::class, 'overview'])->name('admin.sessions.overview');
        Route::get('admin/sessions/analytics', [App\Http\Controllers\TasbihSessionAdminController::class, 'analytics'])->name('admin.sessions.analytics');
        Route::get('admin/sessions', [App\Http\Controllers\TasbihSessionAdminController::class, 'index'])->name('admin.sessions.index');
        Route::get('admin/sessions/{id}', [App\Http\Controllers\TasbihSessionAdminController::class, 'show'])->name('admin.sessions.show')->whereNumber('id');
        Route::post('admin/sessions/{id}/force-close', [App\Http\Controllers\TasbihSessionAdminController::class, 'forceClose'])->name('admin.sessions.force-close')->whereNumber('id');
        Route::delete('admin/sessions/{id}', [App\Http\Controllers\TasbihSessionAdminController::class, 'destroy'])->name('admin.sessions.destroy')->whereNumber('id');

        // ── Tasbih Themes System ────────────────────────────────────────────────
        Route::get('admin/themes/dashboard', [App\Http\Controllers\AdminThemeController::class, 'dashboard'])->name('admin.themes.dashboard');
        Route::get('admin/themes/analytics', [App\Http\Controllers\AdminThemeController::class, 'analytics'])->name('admin.themes.analytics');
        Route::get('admin/themes/categories', [App\Http\Controllers\AdminThemeController::class, 'categories'])->name('admin.themes.categories');
        Route::post('admin/themes/categories', [App\Http\Controllers\AdminThemeController::class, 'categories']);
        Route::post('admin/themes/categories/{id}', [App\Http\Controllers\AdminThemeController::class, 'updateCategory'])->name('admin.themes.categories.update')->whereNumber('id');
        Route::delete('admin/themes/categories/{id}', [App\Http\Controllers\AdminThemeController::class, 'destroyCategory'])->name('admin.themes.categories.destroy')->whereNumber('id');
        Route::resource('admin/themes', App\Http\Controllers\AdminThemeController::class)->names([
            'index' => 'admin.themes.index',
            'create' => 'admin.themes.create',
            'store' => 'admin.themes.store',
            'edit' => 'admin.themes.edit',
            'update' => 'admin.themes.update',
            'destroy' => 'admin.themes.destroy',
        ])->except(['show']);

        // ── Backup & Restore System ─────────────────────────────────────────────
        Route::get('admin/backups/overview', [App\Http\Controllers\BackupAdminController::class, 'overview'])->name('admin.backups.overview');
        Route::get('admin/backups', [App\Http\Controllers\BackupAdminController::class, 'index'])->name('admin.backups.index');
        Route::get('admin/backups/logs', [App\Http\Controllers\BackupAdminController::class, 'logs'])->name('admin.backups.logs');
        Route::get('admin/backups/settings', [App\Http\Controllers\BackupAdminController::class, 'settings'])->name('admin.backups.settings');
        Route::post('admin/backups/settings', [App\Http\Controllers\BackupAdminController::class, 'updateSettings'])->name('admin.backups.settings.save');
        Route::get('admin/backups/{id}/download', [App\Http\Controllers\BackupAdminController::class, 'download'])->name('admin.backups.download')->whereNumber('id');
        Route::delete('admin/backups/{id}', [App\Http\Controllers\BackupAdminController::class, 'destroy'])->name('admin.backups.destroy')->whereNumber('id');

        // ── Fingerprint System ──────────────────────────────────────────────────
        Route::get('admin/fingerprint/dashboard', [App\Http\Controllers\FingerprintAdminController::class, 'dashboard'])->name('admin.fingerprint.dashboard');
        Route::get('admin/fingerprint/users', [App\Http\Controllers\FingerprintAdminController::class, 'users'])->name('admin.fingerprint.users');
        Route::get('admin/fingerprint/settings', [App\Http\Controllers\FingerprintAdminController::class, 'settings'])->name('admin.fingerprint.settings');
        Route::post('admin/fingerprint/settings', [App\Http\Controllers\FingerprintAdminController::class, 'updateSettings'])->name('admin.fingerprint.settings.save');

        // ── Statistics & Analytics System ────────────────────────────────────────
        Route::get('admin/statistics', [App\Http\Controllers\StatisticsAdminController::class, 'index'])->name('admin.statistics.index');
        Route::get('admin/statistics/users', [App\Http\Controllers\StatisticsAdminController::class, 'users'])->name('admin.statistics.users');
        Route::get('admin/statistics/insights', [App\Http\Controllers\StatisticsAdminController::class, 'insights'])->name('admin.statistics.insights');
        Route::get('admin/statistics/settings', [App\Http\Controllers\StatisticsAdminController::class, 'settings'])->name('admin.statistics.settings');
        Route::post('admin/statistics/settings', [App\Http\Controllers\StatisticsAdminController::class, 'saveSettings'])->name('admin.statistics.settings.save');

        // ── Prayer Settings System ──────────────────────────────────────────────
        Route::get('admin/prayer-settings', [App\Http\Controllers\PrayerSettingsController::class, 'index'])->name('admin.prayer-settings.index');
        Route::post('admin/prayer-settings', [App\Http\Controllers\PrayerSettingsController::class, 'update'])->name('admin.prayer-settings.update');
        Route::post('admin/prayer-settings/cities', [App\Http\Controllers\PrayerSettingsController::class, 'storeCity'])->name('admin.prayer-settings.store-city');
        Route::put('admin/prayer-settings/cities/{city}', [App\Http\Controllers\PrayerSettingsController::class, 'updateCity'])->name('admin.prayer-settings.update-city');
        Route::delete('admin/prayer-settings/cities/{city}', [App\Http\Controllers\PrayerSettingsController::class, 'destroyCity'])->name('admin.prayer-settings.destroy-city');
        Route::post('admin/prayer-settings/clear-cache', [App\Http\Controllers\PrayerSettingsController::class, 'clearCache'])->name('admin.prayer-settings.clear-cache');

        // ── Prayer Methods System ──────────────────────────────────────────────
        Route::get('admin/prayer-methods', [App\Http\Controllers\PrayerMethodsController::class, 'index'])->name('admin.prayer-methods.index');
        Route::put('admin/prayer-methods/{id}', [App\Http\Controllers\PrayerMethodsController::class, 'update'])->name('admin.prayer-methods.update');
        Route::post('admin/prayer-methods/{id}/toggle-active', [App\Http\Controllers\PrayerMethodsController::class, 'toggleActive'])->name('admin.prayer-methods.toggle-active');
        Route::post('admin/prayer-methods/{id}/set-default', [App\Http\Controllers\PrayerMethodsController::class, 'setDefault'])->name('admin.prayer-methods.set-default');

        // ── Prayer Widget Settings System ──────────────────────────────────────────────
        Route::get('admin/prayer-widget-settings', [App\Http\Controllers\PrayerWidgetSettingsController::class, 'index'])->name('admin.prayer-widget-settings.index');
        Route::post('admin/prayer-widget-settings', [App\Http\Controllers\PrayerWidgetSettingsController::class, 'update'])->name('admin.prayer-widget-settings.update');
    });


    Route::get('audio-files/{audioFile}/stream', [AudioFileController::class, 'stream'])->name('audio-files.stream');

    // Dynamic UI Translation Manager Routes
    Route::get('translations-manager', [App\Http\Controllers\TranslationManagerController::class, 'index'])->name('translations-manager.index');
    Route::post('translations-manager', [App\Http\Controllers\TranslationManagerController::class, 'store'])->name('translations-manager.store');
    Route::put('translations-manager/update-inline', [App\Http\Controllers\TranslationManagerController::class, 'updateInline'])->name('translations-manager.update-inline');
    Route::delete('translations-manager/{key}', [App\Http\Controllers\TranslationManagerController::class, 'destroy'])->name('translations-manager.destroy');
    Route::post('translations-manager/export', [App\Http\Controllers\TranslationManagerController::class, 'export'])->name('translations-manager.export');
    Route::post('translations-manager/import', [App\Http\Controllers\TranslationManagerController::class, 'import'])->name('translations-manager.import');
    Route::get('translations-manager/history/{translation}', [App\Http\Controllers\TranslationManagerController::class, 'history'])->name('translations-manager.history');
    Route::post('translations-manager/rollback/{version}', [App\Http\Controllers\TranslationManagerController::class, 'rollback'])->name('translations-manager.rollback');
    Route::get('translations-manager/audit', [App\Http\Controllers\TranslationManagerController::class, 'audit'])->name('translations-manager.audit');
    Route::get('translations-manager/sync', [App\Http\Controllers\TranslationManagerController::class, 'syncPage'])->name('translations-manager.sync-page');
    Route::post('translations-manager/sync/pull', [App\Http\Controllers\TranslationManagerController::class, 'syncPull'])->name('translations-manager.sync-pull');
    Route::post('translations-manager/sync/push', [App\Http\Controllers\TranslationManagerController::class, 'syncPush'])->name('translations-manager.sync-push');

    // Automatic UI Translation Discovery & Diagnostics Routes
    Route::get('translations-manager/report', [App\Http\Controllers\TranslationManagerController::class, 'report'])->name('translations-manager.report');
    Route::post('translations-manager/scan', [App\Http\Controllers\TranslationManagerController::class, 'scan'])->name('translations-manager.scan');
    Route::post('translations-manager/sync', [App\Http\Controllers\TranslationManagerController::class, 'sync'])->name('translations-manager.sync');
    Route::post('translations-manager/clear-cache', [App\Http\Controllers\TranslationManagerController::class, 'clearCache'])->name('translations-manager.clear-cache');

    // Bulk Translation Controller Routes
    Route::get('translations-manager/bulk', [App\Http\Controllers\BulkTranslationController::class, 'index'])->name('translations-manager.bulk');
    Route::post('translations-manager/bulk/update', [App\Http\Controllers\BulkTranslationController::class, 'bulkUpdate'])->name('translations-manager.bulk-update');
    Route::post('translations-manager/bulk/delete', [App\Http\Controllers\BulkTranslationController::class, 'bulkDelete'])->name('translations-manager.bulk-delete');
    Route::post('translations-manager/bulk/generate-ai', [App\Http\Controllers\BulkTranslationController::class, 'bulkGenerateAI'])->name('translations-manager.bulk-generate-ai');

    // Translation Intelligence Controller Routes
    Route::get('translations-manager/intelligence', [App\Http\Controllers\TranslationIntelligenceController::class, 'index'])->name('translations-manager.intelligence');
    Route::post('translations-manager/intelligence/search', [App\Http\Controllers\TranslationIntelligenceController::class, 'search'])->name('translations-manager.intelligence.search');
    Route::post('translations-manager/intelligence/suggest', [App\Http\Controllers\TranslationIntelligenceController::class, 'suggest'])->name('translations-manager.intelligence.suggest');
    Route::post('translations-manager/intelligence/rebuild-groups', [App\Http\Controllers\TranslationIntelligenceController::class, 'rebuildGroups'])->name('translations-manager.intelligence.rebuild-groups');
    Route::get('translations-manager/intelligence/consistency', [App\Http\Controllers\TranslationIntelligenceController::class, 'consistency'])->name('translations-manager.intelligence.consistency');
    Route::post('translations-manager/intelligence/similar', [App\Http\Controllers\TranslationIntelligenceController::class, 'similar'])->name('translations-manager.intelligence.similar');
    Route::post('translations-manager/intelligence/translate-ai', [App\Http\Controllers\TranslationIntelligenceController::class, 'translateAi'])->name('translations-manager.intelligence.translate-ai');

    // Translation Analytics Controller Routes
    Route::get('translations-manager/analytics', [App\Http\Controllers\TranslationAnalyticsController::class, 'index'])->name('translations-manager.analytics');
    Route::post('translations-manager/analytics/flush', [App\Http\Controllers\TranslationAnalyticsController::class, 'flushAnalytics'])->name('translations-manager.analytics.flush');
    Route::post('translations-manager/analytics/ai-fix', [App\Http\Controllers\TranslationAnalyticsController::class, 'generateAiFix'])->name('translations-manager.analytics.ai-fix');

    Route::post('bookmarks/toggle/{ayah}', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::get('bookmarks/export', [BookmarkController::class, 'export'])->name('bookmarks.export');
    Route::resource('bookmarks', BookmarkController::class);
    Route::post('favorites/toggle/{ayah?}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('favorites/bulk-delete', [FavoriteController::class, 'bulkDelete'])->name('favorites.bulk-delete');
    Route::resource('favorites', FavoriteController::class);
    Route::get('memorization-plans/export/{format}', [MemorizationPlanController::class, 'exportPlans'])
        ->name('memorization-plans.export');
    Route::post('memorization-plans/import', [MemorizationPlanController::class, 'importPlans'])
        ->name('memorization-plans.import');
    Route::resource('memorization-plans', MemorizationPlanController::class);
    Route::get('memorization-reviews/stats', [MemorizationReviewController::class, 'stats'])->name('memorization-reviews.stats-page');
    Route::get('memorization-reviews/export/{format}', [MemorizationReviewController::class, 'exportReviews'])
        ->name('memorization-reviews.export');
    Route::post('memorization-reviews/import', [MemorizationReviewController::class, 'importReviews'])
        ->name('memorization-reviews.import');
    Route::resource('memorization-reviews', MemorizationReviewController::class);
    Route::get('user-ayah-progress/dashboard', [UserAyahProgressController::class, 'dashboard'])
        ->name('user-ayah-progress.dashboard');
    Route::resource('user-ayah-progress', UserAyahProgressController::class);

    // Other Unified resources
    Route::resource('settings', SettingController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('adhkar-categories', AdhkarCategoryController::class);
    Route::resource('adhkars', AdhkarController::class);
    Route::resource('tasbihs', TasbihController::class);
    Route::resource('hadith-categories', HadithCategoryController::class);
    Route::resource('hadiths', HadithController::class);

    Route::get('reading-history', [ReadingHistoryController::class, 'index'])->name('reading-history.index');
    Route::post('reading-history/track', [ReadingHistoryController::class, 'track'])->name('reading-history.track');
    Route::post('reading-history/track-batch', [ReadingHistoryController::class, 'trackBatch'])->name('reading-history.track-batch');
    Route::get('reading-history/stats', [ReadingHistoryController::class, 'stats'])->name('reading-history.stats');
    Route::delete('reading-history/clear', [ReadingHistoryController::class, 'clear'])->name('reading-history.clear');
    Route::get('reading-history/continue-reading', [ReadingHistoryController::class, 'continueReading'])
        ->name('reading-history.continue-reading');

    Route::get('leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::post('users/{user}/reset-points', [LeaderboardController::class, 'resetUserPoints'])->name('leaderboard.reset');

    // ─── Smart Reminders ────────────────────────────────────────────────────────
    Route::get('reminders', [ReminderTemplateController::class, 'index'])->name('reminders.index');
    Route::get('reminders/create', [ReminderTemplateController::class, 'create'])->name('reminders.create');
    Route::post('reminders', [ReminderTemplateController::class, 'store'])->name('reminders.store');
    Route::get('reminders/{reminder}/edit', [ReminderTemplateController::class, 'edit'])->name('reminders.edit');
    Route::put('reminders/{reminder}', [ReminderTemplateController::class, 'update'])->name('reminders.update');
    Route::delete('reminders/{reminder}', [ReminderTemplateController::class, 'destroy'])->name('reminders.destroy');
    Route::post('reminders/{reminder}/duplicate', [ReminderTemplateController::class, 'duplicate'])->name('reminders.duplicate');
    Route::post('reminders/{reminder}/test', [AdminReminderController::class, 'test'])->name('reminders.test');

    // Reminder monitoring & analytics
    Route::get('reminders/users', [AdminReminderController::class, 'users'])->name('reminders.users');
    Route::get('reminders/analytics', [AdminReminderController::class, 'analytics'])->name('reminders.analytics');
});

// Translation Sync API Endpoints
Route::get('api/translations/sync', [App\Http\Controllers\TranslationManagerController::class, 'apiSyncGet']);
Route::post('api/translations/sync', [App\Http\Controllers\TranslationManagerController::class, 'apiSyncPost']);

require __DIR__.'/auth.php';
