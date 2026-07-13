<?php

use App\Http\Controllers\Api\AyahController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SurahController;
use App\Http\Controllers\Api\TafsirController as PublicTafsirController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AudioFileController;
use App\Http\Controllers\Api\V1\AudioTimingController;
use App\Http\Controllers\Api\V1\AyahController as V1AyahController;
use App\Http\Controllers\Api\V1\MobileSyncController;
use App\Http\Controllers\Api\V1\MemorizationPlanController;
use App\Http\Controllers\Api\V1\MemorizationReviewController;
use App\Http\Controllers\Api\V1\QiraatController;
use App\Http\Controllers\Api\V1\ReciterController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SettingController as V1SettingController;
use App\Http\Controllers\Api\V1\SurahController as V1SurahController;
use App\Http\Controllers\Api\V1\TafsirController;
use App\Http\Controllers\Api\V1\TajweedRuleController;
use App\Http\Controllers\Api\V1\TajweedCategoryController;
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\UserAyahProgressController;
use App\Http\Controllers\Api\V1\BannerController as V1BannerController;
use App\Http\Controllers\Api\V1\AdhkarController as V1AdhkarController;
use App\Http\Controllers\Api\V1\TasbihController as V1TasbihController;
use App\Http\Controllers\Api\V1\HadithController as V1HadithController;
use App\Http\Controllers\Api\V1\DailyGoalController;
use App\Http\Controllers\Api\V1\AchievementController;
use App\Http\Controllers\Api\V1\ReminderController;
use App\Http\Controllers\Api\V1\FeatureFlagController;
use App\Http\Controllers\Api\V1\ContentPackageController;
use App\Http\Controllers\Api\V1\AudioFavoriteController;
use App\Http\Controllers\Api\V1\AudioDownloadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Mushaf API (cached, minimal JSON — matches Flutter v1 reader)
| URIs: /api/surahs, /api/surahs/{id}/ayahs, /api/settings
|--------------------------------------------------------------------------
*/
Route::get('surahs', [SurahController::class, 'index']);
Route::get('surahs/{id}/ayahs', [AyahController::class, 'bySurah'])->whereNumber('id');
Route::get('settings', [SettingController::class, 'index']);
Route::get('tafsir-books', [PublicTafsirController::class, 'books']);
Route::get('tafsirs/ayah/{ayah}', [PublicTafsirController::class, 'byAyah'])->whereNumber('ayah');
Route::get('tafsirs/surah/{surah}', [PublicTafsirController::class, 'bySurah'])->whereNumber('surah');

// Streak update root shortcut
Route::post('streak/update', [App\Http\Controllers\Api\V1\StreakController::class, 'update']);

// ── Feature Flags (public — no auth required, ETag-cached) ────────────────────
Route::prefix('v1')->group(function () {
    Route::get('feature-flags',                  [FeatureFlagController::class,   'index']);
    Route::get('offline-packages/manifest',      [ContentPackageController::class, 'manifest']);
    Route::get('offline-packages/{package}',     [ContentPackageController::class, 'download']);
    
    // Offline-First Package Architecture Routes
    Route::get('packages/manifests',             [ContentPackageController::class, 'manifests']);
    Route::get('packages/{package}/manifest',    [ContentPackageController::class, 'manifest']);
    Route::get('packages/{package}/download',    [ContentPackageController::class, 'download']);
});

// Daily Goal root shortcuts
Route::get('daily-goal/today', [DailyGoalController::class, 'getToday']);
Route::post('daily-goal/update', [DailyGoalController::class, 'updateProgress']);
Route::post('daily-goal/set', [DailyGoalController::class, 'setGoal']);

// Goal progress routes
Route::post('goals/progress/update', [App\Http\Controllers\Api\V1\GoalProgressController::class, 'update']);
Route::get('goals/progress/{goal_id}', [App\Http\Controllers\Api\V1\GoalProgressController::class, 'show'])->whereNumber('goal_id');
Route::post('goals/progress/reset', [App\Http\Controllers\Api\V1\GoalProgressController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('favorites/toggle', [AudioFavoriteController::class, 'toggle']);
    Route::get('favorites', [AudioFavoriteController::class, 'index']);
    Route::post('audio-downloads', [AudioDownloadController::class, 'storeOrUpdate']);
    Route::get('audio-downloads', [AudioDownloadController::class, 'index']);
});

Route::prefix('v1')->group(function () {

    // Public Routes - Quran Data
    Route::get('surahs', [V1SurahController::class, 'index']);
    Route::get('surahs/{id}', [V1SurahController::class, 'show']);
    Route::get('ayahs/daily', [V1AyahController::class, 'daily']);
    Route::get('ayahs', [V1AyahController::class, 'index']);
    Route::get('ayahs/{id}', [V1AyahController::class, 'show']);
    Route::get('surahs/{surahId}/ayahs', [V1AyahController::class, 'ayahsBySurah']);
    Route::get('translations', [TranslationController::class, 'index']);
    Route::get('ayahs/{ayahId}/translations', [TranslationController::class, 'ayahTranslations']);
    Route::get('surahs/{surahId}/translations', [TranslationController::class, 'surahTranslations']);
    Route::get('tafsirs', [TafsirController::class, 'index']);
    Route::get('tafsir-books', [TafsirController::class, 'tafsirBooks']);
    Route::get('ayahs/{ayahId}/tafsirs', [TafsirController::class, 'ayahTafsirs']);
    Route::get('reciters/recent', [\App\Http\Controllers\Api\V1\ReciterHistoryController::class, 'recent']);
    Route::get('reciters', [ReciterController::class, 'index']);
    Route::get('reciters/{id}', [ReciterController::class, 'show']);
    Route::post('reciters/{id}/select', [\App\Http\Controllers\Api\V1\ReciterHistoryController::class, 'select']);
    Route::get('reciters/{id}/surahs/{surah}', [ReciterController::class, 'showPlayback']);
    Route::get('tajweed-rules', [TajweedRuleController::class, 'index']);
    Route::get('tajweed-categories', [TajweedCategoryController::class, 'index']);
    Route::get('qiraats', [QiraatController::class, 'index']);
    Route::get('qiraats/{id}', [QiraatController::class, 'show']);
    Route::get('qiraat-texts', [QiraatController::class, 'qiraatTexts']);
    Route::get('settings', [V1SettingController::class, 'index']);

    Route::get('banners', [V1BannerController::class, 'index']);
    Route::get('adhkars', [V1AdhkarController::class, 'index']);
    Route::get('tasbihs', [V1TasbihController::class, 'index']);
    Route::get('hadiths', [V1HadithController::class, 'index']);
    Route::get('themes', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'index']);
    Route::get('prayer-settings', [App\Http\Controllers\Api\V1\PrayerSettingsController::class, 'index']);
    Route::get('prayer-methods', [App\Http\Controllers\Api\V1\PrayerMethodsController::class, 'index']);
    Route::get('prayer-widget', [App\Http\Controllers\Api\V1\PrayerWidgetController::class, 'index']);
    Route::get('themes/{id}', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'show'])->whereNumber('id');
    Route::post('streak/update', [App\Http\Controllers\Api\V1\StreakController::class, 'update']);
    Route::get('daily-goal/today', [DailyGoalController::class, 'getToday']);
    Route::post('daily-goal/update', [DailyGoalController::class, 'updateProgress']);
    Route::post('daily-goal/set', [DailyGoalController::class, 'setGoal']);

    // ── Prayer Times Calendar (DB-first, ETag-cached) ─────────────────────────
    Route::get('prayer-times',        [App\Http\Controllers\Api\V1\PrayerTimesController::class, 'index']);
    Route::get('prayer-times/cities', [App\Http\Controllers\Api\V1\PrayerTimesController::class, 'cities']);


    Route::post('goals/progress/update', [App\Http\Controllers\Api\V1\GoalProgressController::class, 'update']);
    Route::get('goals/progress/{goal_id}', [App\Http\Controllers\Api\V1\GoalProgressController::class, 'show'])->whereNumber('goal_id');
    Route::post('goals/progress/reset', [App\Http\Controllers\Api\V1\GoalProgressController::class, 'reset']);

    // Achievement routes (public listing + optional user progress)
    Route::get('achievements', [AchievementController::class, 'index']);
    Route::get('achievements/unlocked', [AchievementController::class, 'unlocked']);
    Route::get('achievements/{id}', [AchievementController::class, 'show'])->whereNumber('id');
    Route::post('achievements/sync', [AchievementController::class, 'sync']);

    // Public Audio (Reader v2.1)
    Route::get('audio-files', [AudioFileController::class, 'index']);
    Route::get('audio-files/{id}', [AudioFileController::class, 'show']);
    Route::get('audio-files/{id}/stream', [AudioFileController::class, 'stream']);
    Route::get('audio-files/{audioFileId}/ayah-timings', [AudioTimingController::class, 'getAyahTimings']);
    Route::get('surahs/{surahId}/audio', [AudioTimingController::class, 'getSurahAudio']);

    // Public Search Routes
    Route::get('search', [SearchController::class, 'search']);
    Route::get('search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('search/by-juz/{juzNumber}', [SearchController::class, 'searchByJuz']);
    Route::get('search/by-page/{pageNumber}', [SearchController::class, 'searchByPage']);
    Route::get('juz-list', [SearchController::class, 'getJuzList']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {


        Route::post('user/prayer-method', [App\Http\Controllers\Api\V1\PrayerMethodsController::class, 'updateUserMethod']);

        // Advanced Search (requires auth)
        Route::post('search/advanced', [SearchController::class, 'advanced']);

        // Last Read / Reading History
        Route::get('reading-history', [LastReadController::class, 'index']);
        Route::get('last-read', [LastReadController::class, 'getLastRead']);
        Route::post('last-read', [LastReadController::class, 'saveLastRead']);
        Route::put('reading-time/{ayahId}', [LastReadController::class, 'updateReadingTime']);
        Route::get('reading-progress/surah/{surahId}', [LastReadController::class, 'getSurahReadingProgress']);
        Route::get('reading-progress/overall', [LastReadController::class, 'getOverallProgress']);
        Route::get('reading-streaks', [LastReadController::class, 'getReadingStreaks']);

        Route::delete('reading-history', [LastReadController::class, 'clearHistory']);
        Route::delete('reading-history/{id}', [LastReadController::class, 'deleteEntry']);

        // Audio Files
        // NOTE: Audio playback endpoints are public for the mobile reader (v2.1),
        // so they are defined above outside auth middleware.

        // Audio Timings
        Route::get('audio-files/{audioFileId}/timings', [AudioTimingController::class, 'getTimings']);
        Route::get('audio-files/{audioFileId}/ayah-timings/{ayahId}', [AudioTimingController::class, 'getAyahTiming']);
        Route::get('audio-timings/range', [AudioTimingController::class, 'getRangeTimings']);
        Route::get('audio-files/{audioFileId}/position', [AudioTimingController::class, 'getPositionByTime']);
        Route::get('audio-files/{audioFileId}/current-ayah', [AudioTimingController::class, 'getCurrentAyah']);
        Route::get('surahs/{surahId}/audio-timings', [AudioTimingController::class, 'getSurahAudioTimings']);
        Route::get('audio-files/{audioFileId}/info', [AudioTimingController::class, 'getAudioInfo']);

        // Admin only - Save/Delete timings
        Route::middleware('admin')->group(function () {
            Route::post('audio-files/{audioFileId}/timings', [AudioTimingController::class, 'saveTimings']);
            Route::delete('audio-files/{audioFileId}/timings', [AudioTimingController::class, 'deleteTimings']);
        });

        // User Progress
        Route::get('user-ayah-progress', [UserAyahProgressController::class, 'index']);
        Route::get('user-ayah-progress/dashboard', [UserAyahProgressController::class, 'dashboard']);
        Route::get('memorization/statistics', [UserAyahProgressController::class, 'fullStatistics']);
        Route::get('memorization/progress', [UserAyahProgressController::class, 'detailedProgress']);
        Route::get('memorization/forecast', [UserAyahProgressController::class, 'forecast']);
        Route::post('memorization/sessions', [UserAyahProgressController::class, 'storeSession']);
        Route::post('user-ayah-progress', [UserAyahProgressController::class, 'store']);
        Route::put('user-ayah-progress/{id}', [UserAyahProgressController::class, 'update']);

        // Bookmarks
        Route::get('bookmarks', [BookmarkController::class, 'index']);
        Route::post('bookmarks/toggle', [BookmarkController::class, 'toggle']);
        Route::delete('bookmarks/{id}', [BookmarkController::class, 'destroy']);

        // Mobile v1.1 lightweight sync (minimal payload)
        Route::get('sync/bookmarks', [MobileSyncController::class, 'bookmarks']);
        Route::post('sync/bookmarks', [MobileSyncController::class, 'upsertBookmarks']);
        Route::get('sync/last-read', [MobileSyncController::class, 'lastRead']);
        Route::post('sync/last-read', [MobileSyncController::class, 'saveLastRead']);
        Route::post('bookmarks/sync', [MobileSyncController::class, 'syncBookmarks']);
        Route::get('sync/inbox', [MobileSyncController::class, 'syncInbox']);

        // Favorites
        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);
        Route::delete('favorites/{id}', [FavoriteController::class, 'destroy']);

        // Audio Favorites & Downloads (Reader v2.1)
        Route::post('audio-favorites/toggle', [AudioFavoriteController::class, 'toggle']);
        Route::get('audio-favorites', [AudioFavoriteController::class, 'index']);
        Route::post('audio-downloads', [AudioDownloadController::class, 'storeOrUpdate']);
        Route::get('audio-downloads', [AudioDownloadController::class, 'index']);

        // ─── Smart Reminders ────────────────────────────────────────────────────
        Route::prefix('reminders')->group(function () {
            Route::get('/',        [ReminderController::class, 'index']);
            Route::post('save',    [ReminderController::class, 'save']);
            Route::post('enable',  [ReminderController::class, 'enable']);
            Route::post('disable', [ReminderController::class, 'disable']);
            Route::post('sync',    [ReminderController::class, 'sync']);
            Route::post('opened',  [ReminderController::class, 'opened']);
        });

        // ─── Tasbih Sessions ────────────────────────────────────────────────────
        Route::prefix('sessions')->group(function () {
            Route::post('start', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'start']);
            Route::post('increment', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'increment'])->middleware('throttle:120,1');
            Route::post('pause', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'pause']);
            Route::post('resume', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'resume']);
            Route::post('end', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'end']);
            Route::get('active', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'active']);
            Route::get('history', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'history']);
            Route::get('analytics', [App\Http\Controllers\Api\V1\TasbihSessionController::class, 'analytics']);
        });

        // ─── Backup & Restore ────────────────────────────────────────────────────
        Route::prefix('backups')->group(function () {
            Route::post('create', [App\Http\Controllers\Api\V1\BackupController::class, 'create']);
            Route::get('/', [App\Http\Controllers\Api\V1\BackupController::class, 'index']);
            Route::get('download/{id}', [App\Http\Controllers\Api\V1\BackupController::class, 'download']);
            Route::post('upload', [App\Http\Controllers\Api\V1\BackupController::class, 'upload']);
            Route::post('restore/preview', [App\Http\Controllers\Api\V1\BackupController::class, 'preview']);
            Route::post('restore', [App\Http\Controllers\Api\V1\BackupController::class, 'restore']);
            Route::delete('{id}', [App\Http\Controllers\Api\V1\BackupController::class, 'destroy']);
        });

        // Themes
        Route::prefix('themes')->group(function () {
            Route::post('apply', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'apply']);
            Route::post('favorite', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'favorite']);
            Route::post('download', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'download']);
            Route::post('sync', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'sync']);
            Route::post('preferences', [App\Http\Controllers\Api\V1\ThemeApiController::class, 'savePreferences']);
        });

        // Fingerprint
        Route::prefix('fingerprint')->group(function () {
            Route::get('settings', [App\Http\Controllers\Api\V1\FingerprintApiController::class, 'getSettings']);
            Route::post('settings', [App\Http\Controllers\Api\V1\FingerprintApiController::class, 'saveSettings']);
            Route::post('session', [App\Http\Controllers\Api\V1\FingerprintApiController::class, 'syncSession']);
            Route::get('statistics', [App\Http\Controllers\Api\V1\FingerprintApiController::class, 'getStatistics']);
        });

        // ─── Statistics & Analytics ──────────────────────────────────────────────
        Route::prefix('statistics')->group(function () {
            Route::get('dashboard',    [App\Http\Controllers\Api\V1\StatisticsController::class, 'dashboard']);
            Route::get('dhikr',        [App\Http\Controllers\Api\V1\StatisticsController::class, 'dhikr']);
            Route::get('sessions',     [App\Http\Controllers\Api\V1\StatisticsController::class, 'sessions']);
            Route::get('goals',        [App\Http\Controllers\Api\V1\StatisticsController::class, 'goals']);
            Route::get('achievements', [App\Http\Controllers\Api\V1\StatisticsController::class, 'achievements']);
            Route::get('streaks',      [App\Http\Controllers\Api\V1\StatisticsController::class, 'streaks']);
            Route::get('leaderboard',  [App\Http\Controllers\Api\V1\StatisticsController::class, 'leaderboard']);
            Route::get('fingerprint',  [App\Http\Controllers\Api\V1\StatisticsController::class, 'fingerprint']);
            Route::get('reminders',    [App\Http\Controllers\Api\V1\StatisticsController::class, 'reminders']);
            Route::get('insights',     [App\Http\Controllers\Api\V1\StatisticsController::class, 'insights']);
            Route::get('milestones',   [App\Http\Controllers\Api\V1\StatisticsController::class, 'milestones']);
            Route::post('export',      [App\Http\Controllers\Api\V1\StatisticsController::class, 'export']);
            Route::post('refresh',     [App\Http\Controllers\Api\V1\StatisticsController::class, 'refresh']);
        });
    });
});

