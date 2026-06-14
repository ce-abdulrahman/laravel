<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ReminderStatistic — Daily aggregate cache for fast analytics.
 *
 * Updated nightly by a scheduled job (or lazily on analytics page load).
 */
class ReminderStatistic extends Model
{
    protected $fillable = [
        'date',
        'sent_count',
        'opened_count',
        'failed_count',
        'snoozed_count',
        'active_users',
        'open_rate',
    ];

    protected $casts = [
        'date'          => 'date',
        'sent_count'    => 'integer',
        'opened_count'  => 'integer',
        'failed_count'  => 'integer',
        'snoozed_count' => 'integer',
        'active_users'  => 'integer',
        'open_rate'     => 'float',
    ];

    /**
     * Recalculate and upsert statistics for a given date.
     */
    public static function recalculate(string $date): self
    {
        $sent    = ReminderLog::whereDate('sent_at', $date)->count();
        $opened  = ReminderLog::whereDate('sent_at', $date)->whereNotNull('opened_at')->count();
        $failed  = ReminderLog::whereDate('sent_at', $date)->where('status', ReminderLog::STATUS_FAILED)->count();
        $snoozed = ReminderLog::whereDate('sent_at', $date)->where('status', ReminderLog::STATUS_SNOOZED)->count();
        $users   = ReminderLog::whereDate('sent_at', $date)->distinct('user_id')->count('user_id');
        $rate    = $sent > 0 ? round(($opened / $sent) * 100, 2) : 0;

        return static::updateOrCreate(
            ['date' => $date],
            [
                'sent_count'    => $sent,
                'opened_count'  => $opened,
                'failed_count'  => $failed,
                'snoozed_count' => $snoozed,
                'active_users'  => $users,
                'open_rate'     => $rate,
            ]
        );
    }
}
