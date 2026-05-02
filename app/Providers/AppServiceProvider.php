<?php

namespace App\Providers;

use App\Models\Ayah;
use App\Models\Surah;
use App\Services\QuranApiCache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Surah::saved(function (Surah $surah): void {
            QuranApiCache::forgetAllForSurah($surah->id);
        });

        Surah::deleted(function (Surah $surah): void {
            QuranApiCache::forgetAllForSurah($surah->id);
        });

        Ayah::saved(function (Ayah $ayah): void {
            QuranApiCache::forgetSurahsList();
            QuranApiCache::forgetAyahsForSurah((int) $ayah->surah_id);
        });

        Ayah::deleted(function (Ayah $ayah): void {
            QuranApiCache::forgetSurahsList();
            QuranApiCache::forgetAyahsForSurah((int) $ayah->surah_id);
        });
    }
}
