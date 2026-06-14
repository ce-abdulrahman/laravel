<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintSetting extends Model
{
    protected $table = 'fingerprint_settings';

    protected $fillable = [
        'user_id',
        'count_mode',
        'hold_interval_seconds',
        'haptic_profile',
        'custom_haptic_vibration_ms',
        'audio_profile',
        'blind_mode',
        'focus_mode',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'hold_interval_seconds' => 'integer',
        'custom_haptic_vibration_ms' => 'integer',
        'blind_mode' => 'boolean',
        'focus_mode' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
