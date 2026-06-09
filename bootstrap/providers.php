<?php

use App\Providers\AppServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;

return [
    AppServiceProvider::class,
    App\Providers\LocalizationServiceProvider::class,
    SanctumServiceProvider::class,
];

