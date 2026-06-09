<?php

use App\Services\TranslationService;

if (!function_exists('t')) {
    /**
     * Get a UI translation for the given key (database-backed).
     *
     * @param string $key       e.g. 'auth.login', 'dashboard.title'
     * @param array  $replace   Placeholder replacements e.g. ['count' => 5]
     * @param string|null $locale Override locale, defaults to app locale
     */
    function t(string $key, array $replace = [], ?string $locale = null): string
    {
        return app(\App\Services\TranslationService::class)->get($key, $replace, $locale);
    }
}

if (!function_exists('translate')) {
    /**
     * Alias for t() — get a UI translation for the given key.
     *
     * @param string $key       e.g. 'auth.login', 'dashboard.title'
     * @param array  $replace   Placeholder replacements
     * @param string|null $locale Override locale
     */
    function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        return app(\App\Services\TranslationService::class)->get($key, $replace, $locale);
    }
}


if (!function_exists('calculate_table_cols')) {
    /**
     * Calculate total table columns dynamically.
     *
     * @param int $staticColumns Count of static columns (excluding language and action columns)
     * @param int $activeLanguagesCount Count of active languages
     * @param int $actionColumns Count of action columns (default 1)
     * @return int
     */
    function calculate_table_cols(int $staticColumns, int $activeLanguagesCount, int $actionColumns = 1): int
    {
        return $staticColumns + $activeLanguagesCount + $actionColumns;
    }
}
