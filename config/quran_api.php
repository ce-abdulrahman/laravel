<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Quran API Configurations
    |--------------------------------------------------------------------------
    |
    | Configurations for default reciter slug, cache durations, and other options.
    |
    */

    'default_reciter_slug' => env('DEFAULT_RECITER_SLUG', 'alafasy'),

    'cache_ttl' => env('QURAN_API_CACHE_TTL', 3600),
];
