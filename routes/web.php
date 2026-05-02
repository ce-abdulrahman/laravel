<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AyahController;
use App\Http\Controllers\Api\V1\SurahController;
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\TafsirController;
use App\Http\Controllers\Api\V1\ReciterController;
use App\Http\Controllers\Api\V1\AudioFileController;
use App\Http\Controllers\Api\V1\MemorizationPlanController;
use App\Http\Controllers\Api\V1\MemorizationReviewController;
use App\Http\Controllers\Api\V1\UserAyahProgressController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\TajweedRuleController;
use App\Http\Controllers\Api\V1\QiraatController;
use App\Http\Controllers\Api\V1\SettingController;

Route::prefix('v1')->group(function () {

    // Public Routes - Authentication
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);

    // Public Routes - Quran Data
    Route::get('surahs', [SurahController::class, 'index']);
    Route::get('surahs/{id}', [SurahController::class, 'show']);
    Route::get('ayahs', [AyahController::class, 'index']);
    Route::get('ayahs/{id}', [AyahController::class, 'show']);
    Route::get('surahs/{surahId}/ayahs', [AyahController::class, 'ayahsBySurah']);
    Route::get('translations', [TranslationController::class, 'index']);
    Route::get('ayahs/{ayahId}/translations', [TranslationController::class, 'ayahTranslations']);
    Route::get('tafsirs', [TafsirController::class, 'index']);
    Route::get('tafsir-books', [TafsirController::class, 'tafsirBooks']);
    Route::get('ayahs/{ayahId}/tafsirs', [TafsirController::class, 'ayahTafsirs']);
    Route::get('reciters', [ReciterController::class, 'index']);
    Route::get('reciters/{id}', [ReciterController::class, 'show']);
    Route::get('tajweed-rules', [TajweedRuleController::class, 'index']);
    Route::get('qiraats', [QiraatController::class, 'index']);
    Route::get('qiraats/{id}', [QiraatController::class, 'show']);
    Route::get('qiraat-texts', [QiraatController::class, 'qiraatTexts']);
    Route::get('settings', [SettingController::class, 'index']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::put('auth/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);

        // Audio Files
        Route::get('audio-files', [AudioFileController::class, 'index']);
        Route::get('audio-files/{id}', [AudioFileController::class, 'show']);
        Route::get('audio-files/{id}/stream', [AudioFileController::class, 'stream']);

        // Memorization Plans
        Route::apiResource('memorization-plans', MemorizationPlanController::class);
        Route::put('memorization-plans/{planId}/items/{itemId}/status', [MemorizationPlanController::class, 'updateItemStatus']);

        // Memorization Reviews
        Route::apiResource('memorization-reviews', MemorizationReviewController::class);

        // User Progress
        Route::get('user-ayah-progress', [UserAyahProgressController::class, 'index']);
        Route::get('user-ayah-progress/dashboard', [UserAyahProgressController::class, 'dashboard']);
        Route::post('user-ayah-progress', [UserAyahProgressController::class, 'store']);
        Route::put('user-ayah-progress/{id}', [UserAyahProgressController::class, 'update']);

        // Bookmarks
        Route::get('bookmarks', [BookmarkController::class, 'index']);
        Route::post('bookmarks/toggle', [BookmarkController::class, 'toggle']);
        Route::delete('bookmarks/{id}', [BookmarkController::class, 'destroy']);

        // Favorites
        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);
        Route::delete('favorites/{id}', [FavoriteController::class, 'destroy']);
    });
});
