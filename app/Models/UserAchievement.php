<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    protected $fillable = [
        'user_id',
        'achievement_id',
        'progress_value',
        'is_completed',
        'completed_at',
        'unlocked_version',
    ];

    protected $casts = [
        'progress_value'   => 'integer',
        'is_completed'     => 'boolean',
        'completed_at'     => 'datetime',
        'unlocked_version' => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    protected static function booted(): void
    {
        static::saved(function (UserAchievement $ua) {
            $user = $ua->user;
            if ($user) {
                $engine = app(\App\Services\LeaderboardEngine::class);
                $engine->updateScoreForUser($user, 'ACHIEVEMENTS_EARNED');
                $engine->updateScoreForUser($user, 'ACHIEVEMENT_POINTS');
                $engine->updateScoreForUser($user, 'CUSTOM_SCORING');
                app(\App\Services\LeaderboardCacheService::class)->clearCache();
            }
        });
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }
}
