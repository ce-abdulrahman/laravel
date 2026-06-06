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
    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/language/current', [LanguageController::class, 'getCurrentLanguage'])->name('language.current');

Route::middleware('auth')->group(function () {
    Route::get('/juz', [App\Http\Controllers\ReadController::class, 'juzIndex'])->name('juz.index');
    Route::get('/page', [App\Http\Controllers\ReadController::class, 'pageIndex'])->name('page.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('surahs/import', [SurahController::class, 'import'])->name('surahs.import');
    Route::resource('surahs', SurahController::class);

    Route::post('ayahs/import', [AyahController::class, 'import'])->name('ayahs.import');
    Route::resource('ayahs', AyahController::class);

    Route::post('tajweed-rules/import', [TajweedRuleController::class, 'import'])->name('tajweed-rules.import');
    Route::resource('tajweed-rules', TajweedRuleController::class);
    Route::resource('tajweed-rule-categories', TajweedRuleCategoryController::class);
    Route::resource('tajweed-segments', AyahTajweedSegmentController::class);
    Route::resource('reciters', ReciterController::class);
    Route::resource('audio-files', AudioFileController::class);
    Route::get('audio-files/{audioFile}/stream', [AudioFileController::class, 'stream'])->name('audio-files.stream');
    Route::post('audio-files/upload', [AudioFileController::class, 'upload'])->name('audio-files.upload');
    Route::resource('qiraats', QiraatController::class);
    Route::resource('qiraat-texts', QiraatTextController::class);
    Route::resource('tafsir-books', TafsirBookController::class);

    Route::post('tafsirs/import', [TafsirController::class, 'import'])->name('tafsirs.import');
    Route::resource('tafsirs', TafsirController::class);

    Route::post('translations/import', [TranslationController::class, 'import'])->name('translations.import');
    Route::resource('translations', TranslationController::class);
    Route::resource('bookmarks', BookmarkController::class);
    Route::resource('favorites', FavoriteController::class);
    Route::resource('memorization-plans', MemorizationPlanController::class);
    Route::get('memorization-reviews/stats', [MemorizationReviewController::class, 'stats'])->name('memorization-reviews.stats-page');
    Route::resource('memorization-reviews', MemorizationReviewController::class);
    Route::get('user-ayah-progress/dashboard', [UserAyahProgressController::class, 'dashboard'])
        ->name('user-ayah-progress.dashboard');
    Route::resource('user-ayah-progress', UserAyahProgressController::class);
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

require __DIR__.'/auth.php';
