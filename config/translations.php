<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Generate UI Translation Keys
    |--------------------------------------------------------------------------
    |
    | When set to true, if a translation key is looked up via t() but does not
    | exist in the database, it will be automatically inserted into the
    | translation_keys table, and empty translation placeholders will be
    | generated for all active languages. Highly useful during development,
    | but recommended to disable in production to prevent DB bloat.
    |
    */
    'auto_generate' => env('TRANSLATIONS_AUTO_GENERATE', true),

    /*
    |--------------------------------------------------------------------------
    | Sync Token
    |--------------------------------------------------------------------------
    |
    | Used to authorize remote environments pulling/pushing translations.
    |
    */
    'sync_token' => env('TRANSLATIONS_SYNC_TOKEN', 'secret-sync-token'),
];
