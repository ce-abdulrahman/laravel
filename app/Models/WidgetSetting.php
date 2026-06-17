<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WidgetSetting extends Model
{
    use HasFactory;

    protected $table = 'widget_settings';

    protected $fillable = [
        'widget_enabled',
        'widget_visibility',
        'widget_refresh_interval',
        'widget_default_city_id',
        'widget_display_order',
        'hijri_source',
    ];

    protected $casts = [
        'widget_enabled' => 'boolean',
        'widget_refresh_interval' => 'integer',
        'widget_default_city_id' => 'integer',
        'widget_display_order' => 'integer',
    ];

    public function defaultCity()
    {
        return $this->belongsTo(City::class, 'widget_default_city_id');
    }
}
