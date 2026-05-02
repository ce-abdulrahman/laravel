<?php
// app/Helpers/SettingsHelper.php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    public static function get($key, $default = null)
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return Setting::first();
        });

        return $settings ? ($settings->$key ?? $default) : $default;
    }

    public static function appName()
    {
        return self::get('app_name', 'Quran App');
    }

    public static function appLogo()
    {
        return self::get('app_logo');
    }

    public static function defaultLanguage()
    {
        return self::get('default_language', 'ku');
    }

    public static function defaultTafsirBookId()
    {
        return self::get('default_tafsir_book_id');
    }

    public static function defaultReciterId()
    {
        return self::get('default_reciter_id');
    }

    public static function defaultQiraatId()
    {
        return self::get('default_qiraah_id');
    }

    public static function aboutText()
    {
        return self::get('about_text');
    }

    public static function contactEmail()
    {
        return self::get('contact_email');
    }
}