<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | Define the system-wide languages here.
    | Adding, removing, or modifying languages in this config dynamically syncs
    | to the database registry on the next application boot.
    |
    | Exactly one language must be configured as the default ('is_default' => true).
    |
    */
    'supported' => [
        'en' => [
            'name'        => 'English',
            'native_name' => 'English',
            'direction'   => 'ltr',
            'flag'        => '🇬🇧',
            'is_active'   => true,
            'is_default'  => true,
            'order'       => 1,
        ],
        'ku' => [
            'name'        => 'Kurdish',
            'native_name' => 'کوردی',
            'direction'   => 'rtl',
            'flag'        => '☀️',
            'is_active'   => true,
            'is_default'  => false,
            'order'       => 2,
        ],
        'ar' => [
            'name'        => 'Arabic',
            'native_name' => 'العربية',
            'direction'   => 'rtl',
            'flag'        => '🌙',
            'is_active'   => true,
            'is_default'  => false,
            'order'       => 3,
        ],
        'tr' => [
            'name'        => 'Turkish',
            'native_name' => 'Türkçe',
            'direction'   => 'ltr',
            'flag'        => '🇹🇷',
            'is_active'   => true,
            'is_default'  => false,
            'order'       => 4,
        ],
    ],
];
