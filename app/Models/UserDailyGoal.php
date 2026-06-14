<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyGoal extends Model
{
    protected $table = 'user_daily_goals';

    protected $fillable = [
        'user_id',
        'goal_value',
        'today_progress',
        'goal_date',
        'is_completed',
    ];

    protected $casts = [
        'goal_value' => 'integer',
        'today_progress' => 'integer',
        'goal_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saved(function (UserDailyGoal $goal) {
            $user = $goal->user;
            if ($user) {
                $engine = app(\App\Services\LeaderboardEngine::class);
                $engine->updateScoreForUser($user, 'DAILY_DHIKR');
                $engine->updateScoreForUser($user, 'WEEKLY_DHIKR');
                $engine->updateScoreForUser($user, 'MONTHLY_DHIKR');
                $engine->updateScoreForUser($user, 'ALL_TIME_DHIKR');
                if ($goal->isDirty('is_completed')) {
                    $engine->updateScoreForUser($user, 'GOALS_COMPLETED');
                }
                $engine->updateScoreForUser($user, 'CUSTOM_SCORING');
                app(\App\Services\LeaderboardCacheService::class)->clearCache();
            }
        });
    }
}
