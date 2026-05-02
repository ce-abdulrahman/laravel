<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QuranApiCache
{
    public const KEY_SURAHS = 'surahs.all';

    public static function keyAyahsForSurah(int $surahId): string
    {
        return "surah.{$surahId}.ayahs";
    }

    public const KEY_SETTINGS = 'settings.all';

    public static function forgetSurahsList(): void
    {
        Cache::forget(self::KEY_SURAHS);
    }

    public static function forgetAyahsForSurah(int $surahId): void
    {
        Cache::forget(self::keyAyahsForSurah($surahId));
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
