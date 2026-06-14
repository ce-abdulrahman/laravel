<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTasbihStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_activity_date' => 'date',
    ];

    /**
     * Get the user that owns the tasbih streak.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saved(function (UserTasbihStreak $streak) {
            $user = $streak->user;
            if ($user) {
                $engine = app(\App\Services\LeaderboardEngine::class);
                $engine->updateScoreForUser($user, 'CURRENT_STREAK');
                $engine->updateScoreForUser($user, 'LONGEST_STREAK');
                $engine->updateScoreForUser($user, 'CUSTOM_SCORING');
                app(\App\Services\LeaderboardCacheService::class)->clearCache();
            }
        });
    }
}
