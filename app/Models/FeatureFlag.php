<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'is_enabled',
        'rollout_percentage',
        'platform',
        'min_app_version',
        'max_app_version',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'rollout_percentage' => 'integer',
    ];
}
