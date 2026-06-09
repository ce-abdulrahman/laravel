<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QuranApiCache
{
    public const KEY_SURAHS = 'surahs.all';
    public const KEY_SETTINGS = 'settings.all';

    public static function keyAyahsForSurah(int $surahId): string
    {
        return "surah.{$surahId}.ayahs";
    }

    /**
     * Get the global translation/language cache version.
     */
    public static function getGlobalVersion(): string
    {
        return (string) Cache::rememberForever('quran_api:global_version', function () {
            return '1';
        });
    }

    /**
     * Increment the global version to invalidate all cached data.
     */
    public static function incrementGlobalVersion(): void
    {
        try {
            $v = (int) Cache::get('quran_api:global_version', 1);
            Cache::forever('quran_api:global_version', $v + 1);
        } catch (\Exception $e) {}
    }

    /**
     * Clear all caches for all registered active languages.
     */
    public static function clearAllLocales(): void
    {
        self::forgetSurahsList();
        self::forgetSettings();
        
        try {
            // Bust all active surahs' ayahs caches
            $surahIds = \App\Models\Surah::pluck('number')->toArray();
            foreach ($surahIds as $id) {
                self::forgetAyahsForSurah($id);
            }
        } catch (\Exception $e) {}

        // Increment version to safely clear versioned cache keys across environments
        self::incrementGlobalVersion();
    }

    public static function getSurahsKey(string $locale): string
    {
        $version = self::getGlobalVersion();
        return self::KEY_SURAHS . '.' . $locale . '.' . $version;
    }

    public static function getAyahsKey(int $surahId, string $locale): string
    {
        $version = self::getGlobalVersion();
        return self::keyAyahsForSurah($surahId) . '.' . $locale . '.' . $version;
    }

    public static function forgetSurahsList(): void
    {
        $version = self::getGlobalVersion();
        Cache::forget(self::KEY_SURAHS);
        Cache::forget(self::KEY_SURAHS . '.' . $version);
        
        try {
            $codes = \App\Models\Language::pluck('code')->toArray();
            foreach ($codes as $code) {
                Cache::forget(self::KEY_SURAHS . '.' . $code);
                Cache::forget(self::KEY_SURAHS . '.' . $code . '.' . $version);
            }
        } catch (\Exception $e) {}
    }

    public static function forgetAyahsForSurah(int $surahId): void
    {
        $version = self::getGlobalVersion();
        $baseKey = self::keyAyahsForSurah($surahId);
        Cache::forget($baseKey);
        Cache::forget($baseKey . '.' . $version);

        try {
            $codes = \App\Models\Language::pluck('code')->toArray();
            foreach ($codes as $code) {
                Cache::forget($baseKey . '.' . $code);
                Cache::forget($baseKey . '.' . $code . '.' . $version);
            }
        } catch (\Exception $e) {}
    }

    public static function forgetSettings(): void
    {
        Cache::forget(self::KEY_SETTINGS);
    }

    public static function forgetAllForSurah(int $surahId): void
    {
        self::forgetSurahsList();
        self::forgetAyahsForSurah($surahId);
    }
}
