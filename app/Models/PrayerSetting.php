<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrayerSetting extends Model
{
    use HasFactory;

    protected $table = 'prayer_settings';

    protected $fillable = [
        'calculation_method',
        'global_notifications_enabled',
        'adhan_settings',
        'cached_prayer_data',
    ];

    protected $casts = [
        'global_notifications_enabled' => 'boolean',
        'adhan_settings' => 'array',
        'cached_prayer_data' => 'array',
    ];
}
