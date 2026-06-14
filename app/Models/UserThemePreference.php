<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserThemePreference extends Model
{
    protected $fillable = [
        'user_id', 'theme_id', 'sound_enabled', 'haptic_enabled', 
        'animation_enabled', 'custom_ring_color', 'custom_font_scale'
    ];

    protected $casts = [
        'sound_enabled' => 'boolean',
        'haptic_enabled' => 'boolean',
        'animation_enabled' => 'boolean',
        'custom_font_scale' => 'double',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }
}
