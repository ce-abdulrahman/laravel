<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class LanguageHelper
{
    /**
     * Get text direction based on current locale
     *
     * @return string
     */
    public static function getDirection(): string
    {
        return in_array(App::getLocale(), ['ar', 'ku']) ? 'rtl' : 'ltr';
    }

    /**
     * Check if current language is RTL
     *
     * @return bool
     */
    public static function isRtl(): bool
    {
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
