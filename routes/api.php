<?php

use App\Http\Controllers\Api\AyahController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SurahController;
use App\Http\Controllers\Api\TafsirController as PublicTafsirController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AudioFileController;
use App\Http\Controllers\Api\V1\AudioTimingController;
use App\Http\Controllers\Api\V1\AyahController as V1AyahController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\LastReadController;
use App\Http\Controllers\Api\V1\LeaderboardController;
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
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\UserAyahProgressController;
use App\Http\Controllers\Api\V1\BannerController as V1BannerController;
use App\Http\Controllers\Api\V1\AdhkarController as V1AdhkarController;
use App\Http\Controllers\Api\V1\TasbihController as V1TasbihController;
use App\Http\Controllers\Api\V1\HadithController as V1HadithController;
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


Route::prefix('v1')->group(function () {

    // Public Routes - Authentication
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

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
    Route::get('reciters', [ReciterController::class, 'index']);
    Route::get('reciters/{id}', [ReciterController::class, 'show']);
    Route::get('tajweed-rules', [TajweedRuleController::class, 'index']);
    Route::get('qiraats', [QiraatController::class, 'index']);
    Route::get('qiraats/{id}', [QiraatController::class, 'show']);
    Route::get('qiraat-texts', [QiraatController::class, 'qiraatTexts']);
    Route::get('settings', [V1SettingController::class, 'index']);
    Route::get('leaderboard', [LeaderboardController::class, 'index']);
    Route::get('banners', [V1BannerController::class, 'index']);
    Route::get('adhkars', [V1AdhkarController::class, 'index']);
    Route::get('tasbihs', [V1TasbihController::class, 'index']);
    Route::get('hadiths', [V1HadithController::class, 'index']);

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

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::put('auth/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);

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
        Route::get('me/stats', [LeaderboardController::class, 'myStats']);
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

        // Memorization Plans
        Route::get('memorization-plans/today', [MemorizationPlanController::class, 'today']);
        Route::apiResource('memorization-plans', MemorizationPlanController::class)
            ->names('api.v1.memorization-plans');
        Route::put('memorization-plans/{planId}/items/{itemId}/status', [MemorizationPlanController::class, 'updateItemStatus']);

        // Memorization Reviews
        Route::apiResource('memorization-reviews', MemorizationReviewController::class)
            ->names('api.v1.memorization-reviews');

        // User Progress
        Route::get('user-ayah-progress', [UserAyahProgressController::class, 'index']);
        Route::get('user-ayah-progress/dashboard', [UserAyahProgressController::class, 'dashboard']);
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

        // Favorites
        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);
        Route::delete('favorites/{id}', [FavoriteController::class, 'destroy']);
    });
});
