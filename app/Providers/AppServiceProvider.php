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
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Language::syncFromConfig();

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

        // Versioning cache busting on any translation change
        $incrementVersion = function (): void {
            QuranApiCache::incrementGlobalVersion();
            try {
                if (app()->bound(\App\Services\TranslationService::class)) {
                    resolve(\App\Services\TranslationService::class)->clearCache();
                }
            } catch (\Exception $e) {}
        };

        $translationModels = [
            \App\Models\Translation::class,
            \App\Models\SurahTranslation::class,
            \App\Models\TajweedRuleTranslation::class,
            \App\Models\TajweedRuleCategoryTranslation::class,
            \App\Models\AdhkarCategoryTranslation::class,
            \App\Models\AdhkarTranslation::class,
            \App\Models\HadithCategoryTranslation::class,
            \App\Models\HadithTranslation::class,
            \App\Models\UiTranslation::class,
            \App\Models\TranslationKey::class,
        ];

        foreach ($translationModels as $modelClass) {
            if (class_exists($modelClass)) {
                $modelClass::saved($incrementVersion);
                $modelClass::deleted($incrementVersion);
            }
        }
    }
}
