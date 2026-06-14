<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * UserReminder — Per-user reminder preferences.
 *
 * One row per user per reminder_type.
 * Frequency and type stored as VARCHAR (not ENUM) for extensibility.
 */
class UserReminder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'reminder_type',
        'enabled',
        'scheduled_time',
        'frequency',
        'custom_days',
        'timezone',
        'last_sent_at',
    ];

    protected $casts = [
        'enabled'      => 'boolean',
        'custom_days'  => 'array',
        'last_sent_at' => 'datetime',
    ];

    // ─── Frequency Constants ─────────────────────────────────────────────────────

    public const FREQUENCY_DAILY    = 'daily';
    public const FREQUENCY_WEEKDAYS = 'weekdays';
    public const FREQUENCY_WEEKENDS = 'weekends';
    public const FREQUENCY_CUSTOM   = 'custom';

    public static function allFrequencies(): array
    {
        return [
            self::FREQUENCY_DAILY,
            self::FREQUENCY_WEEKDAYS,
            self::FREQUENCY_WEEKENDS,
            self::FREQUENCY_CUSTOM,
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('reminder_type', $type);
    }
}
