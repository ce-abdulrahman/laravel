<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistic extends Model
{
    protected $fillable = [
        'user_id',
        'total_dhikr',
        'total_sessions',
        'current_streak',
        'longest_streak',
        'total_streak_days',
        'total_goals_completed',
        'total_goals_missed',
        'goal_completion_rate',
        'total_achievements',
        'rare_achievements',
        'fingerprint_total_counts',
        'fingerprint_total_sessions',
        'current_rank',
        'highest_rank',
        'reminders_sent',
        'reminders_opened',
        'productivity_score',
        'productivity_label',
        'last_calculated_at',
    ];

    protected $casts = [
        'last_calculated_at'  => 'datetime',
        'goal_completion_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
