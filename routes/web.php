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
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserAyahProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/language/current', [LanguageController::class, 'getCurrentLanguage'])->name('language.current');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('surahs', SurahController::class);
    Route::resource('ayahs', AyahController::class);
    Route::resource('tajweed-rules', TajweedRuleController::class);
    Route::resource('tajweed-segments', AyahTajweedSegmentController::class);
    Route::resource('reciters', ReciterController::class);
    Route::resource('audio-files', AudioFileController::class);
    Route::resource('qiraats', QiraatController::class);
    Route::resource('qiraat-texts', QiraatTextController::class);
    Route::resource('tafsir-books', TafsirBookController::class);
    Route::resource('tafsirs', TafsirController::class);
    Route::resource('translations', TranslationController::class);
    Route::resource('bookmarks', BookmarkController::class);
    Route::resource('favorites', FavoriteController::class);
    Route::resource('memorization-plans', MemorizationPlanController::class);
    Route::resource('memorization-reviews', MemorizationReviewController::class);
    Route::resource('user-ayah-progress', UserAyahProgressController::class);
    Route::resource('settings', SettingController::class);

    Route::get('user-ayah-progress/dashboard', [UserAyahProgressController::class, 'dashboard'])
        ->name('user-ayah-progress.dashboard');

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

require __DIR__.'/auth.php';
