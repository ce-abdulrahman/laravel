<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardEntry extends Model
{
    protected $table = 'leaderboard_entries';

    protected $fillable = [
        'period_id',
        'user_id',
        'rank_position',
        'score',
        'movement',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(LeaderboardPeriod::class, 'period_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeTop($query, int $limit = 3)
    {
        return $query->orderBy('rank_position', 'asc')->limit($limit);
    }
}
