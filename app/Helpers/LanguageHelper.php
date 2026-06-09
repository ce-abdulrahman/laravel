<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class LanguageHelper
{
    /**
     * Resolve the locale for the current request based on priority.
     * 1. Query parameter (?locale=)
     * 2. Accept-Language header
     * 3. User preferred locale (if authenticated)
     * 4. System default language (database default)
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public static function resolveLocale(\Illuminate\Http\Request $request): string
    {
        // Get active codes first (cached)
        try {
            $activeCodes = \App\Models\Language::activeCodes();
        } catch (\Exception $e) {
            $activeCodes = [];
        }

        // 1. Check query parameter
        if ($request->has('locale')) {
            $queryLocale = $request->query('locale');
            if (is_string($queryLocale) && in_array($queryLocale, $activeCodes, true)) {
                return $queryLocale;
            }
        }

        // 2. Check session (for web requests only)
        if (!$request->is('api/*') && !$request->expectsJson() && $request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
            if (is_string($sessionLocale) && in_array($sessionLocale, $activeCodes, true)) {
                return $sessionLocale;
            }
        }

        // 3. Contextual priorities
        if ($request->is('api/*') || $request->expectsJson()) {
            // API Stack: Accept-Language -> User Preferred
            if ($request->headers->has('Accept-Language')) {
                $preferred = strtolower((string) $request->getPreferredLanguage());
                $primary = strtolower(strtok(str_replace('_', '-', $preferred), '-') ?: '');
                if ($primary === 'ckb') {
                    $primary = 'ku';
                }
                if ($primary && in_array($primary, $activeCodes, true)) {
                    return $primary;
                }
            }

            if (auth()->check()) {
                $userLocale = auth()->user()->preferred_locale;
                if ($userLocale && in_array($userLocale, $activeCodes, true)) {
                    return $userLocale;
                }
            }
        } else {
            // Web Stack: User Preferred -> Accept-Language
            if (auth()->check()) {
                $userLocale = auth()->user()->preferred_locale;
                if ($userLocale && in_array($userLocale, $activeCodes, true)) {
                    return $userLocale;
                }
            }

            if ($request->headers->has('Accept-Language')) {
                $preferred = strtolower((string) $request->getPreferredLanguage());
                $primary = strtolower(strtok(str_replace('_', '-', $preferred), '-') ?: '');
                if ($primary === 'ckb') {
                    $primary = 'ku';
                }
                if ($primary && in_array($primary, $activeCodes, true)) {
                    return $primary;
                }
            }
        }

        // 5. Default database language
        try {
            $defaultLang = \App\Models\Language::default();
            if ($defaultLang && in_array($defaultLang->code, $activeCodes, true)) {
                return $defaultLang->code;
            }
        } catch (\Exception $e) {}

        // System fallback
        if (!empty($activeCodes)) {
            return $activeCodes[0];
        }

        return config('app.fallback_locale', 'en');
    }

    /**
     * Get text direction based on current locale
     *
     * @return string
     */
    public static function getDirection(): string
    {
        $lang = \App\Models\Language::activeList()->where('code', App::getLocale())->first();
        if ($lang) {
            return $lang->direction;
        }
        return in_array(App::getLocale(), ['ar', 'ku']) ? 'rtl' : 'ltr';
    }

    /**
     * Check if current language is RTL
     *
     * @return bool
     */
    public static function isRtl(): bool
    {
        $lang = \App\Models\Language::activeList()->where('code', App::getLocale())->first();
        if ($lang) {
            return $lang->is_rtl;
        }
        return in_array(App::getLocale(), ['ar', 'ku']);
    }

    /**
     * Get text alignment class
     *
     * @return string
     */
    public static function getTextAlignClass(): string
    {
        return self::isRtl() ? 'text-end' : 'text-start';
    }

    /**
     * Get float class for current direction
     *
     * @param string $direction 'start' or 'end'
     * @return string
     */
    public static function getFloatClass(string $direction = 'start'): string
    {
        if ($direction === 'start') {
            return self::isRtl() ? 'float-end' : 'float-start';
        }
        return self::isRtl() ? 'float-start' : 'float-end';
    }

    /**
     * Get margin class for spacing
     *
     * @param string $direction 'start' or 'end'
     * @param int $size 1-5
     * @return string
     */
    public static function getMarginClass(string $direction = 'start', int $size = 1): string
    {
        $sizes = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'];
        $sizeValue = $sizes[$size] ?? '1';

        if ($direction === 'start') {
            return self::isRtl() ? "me-{$sizeValue}" : "ms-{$sizeValue}";
        }
        return self::isRtl() ? "ms-{$sizeValue}" : "me-{$sizeValue}";
    }
}
