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
        Route::post('ayahs/import', [AyahController::class, 'import'])->name('ayahs.import');
        Route::post('tajweed-rules/import', [TajweedRuleController::class, 'import'])->name('tajweed-rules.import');
        Route::resource('tajweed-segments', AyahTajweedSegmentController::class);
        Route::post('audio-files/upload', [AudioFileController::class, 'upload'])->name('audio-files.upload');
        Route::post('tafsirs/import', [TafsirController::class, 'import'])->name('tafsirs.import');
        Route::post('translations/import', [TranslationController::class, 'import'])->name('translations.import');
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

    Route::resource('bookmarks', BookmarkController::class);
    Route::resource('favorites', FavoriteController::class);
    Route::resource('memorization-plans', MemorizationPlanController::class);
    Route::get('memorization-reviews/stats', [MemorizationReviewController::class, 'stats'])->name('memorization-reviews.stats-page');
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
});

// Translation Sync API Endpoints
Route::get('api/translations/sync', [App\Http\Controllers\TranslationManagerController::class, 'apiSyncGet']);
Route::post('api/translations/sync', [App\Http\Controllers\TranslationManagerController::class, 'apiSyncPost']);

require __DIR__.'/auth.php';
