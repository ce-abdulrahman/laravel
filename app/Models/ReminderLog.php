<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReminderLog — Delivery history for analytics and troubleshooting.
 */
class ReminderLog extends Model
{
    // No soft deletes on logs — preserve history for analytics
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'template_id',
        'notification_type',
        'notification_id',
        'sent_at',
        'opened_at',
        'status',
        'device_platform',
        'timezone',
        'payload_json',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'opened_at'    => 'datetime',
        'payload_json' => 'array',
    ];

    // ─── Status Constants ────────────────────────────────────────────────────────

    public const STATUS_SENT      = 'sent';
    public const STATUS_OPENED    = 'opened';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_SNOOZED   = 'snoozed';
    public const STATUS_CANCELLED = 'cancelled';

    // ─── Platform Constants ──────────────────────────────────────────────────────

    public const PLATFORM_ANDROID = 'android';
    public const PLATFORM_IOS     = 'ios';
    public const PLATFORM_WEB     = 'web';

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReminderTemplate::class, 'template_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sent_at', now()->toDateString());
    }
}
