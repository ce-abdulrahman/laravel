<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintStatistic extends Model
{
    protected $table = 'fingerprint_statistics';

    protected $fillable = [
        'user_id',
        'total_counts',
        'total_sessions',
        'total_blind_sessions',
        'total_focus_sessions',
        'avg_touch_rate',
        'favorite_mode',
        'last_used_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_counts' => 'integer',
        'total_sessions' => 'integer',
        'total_blind_sessions' => 'integer',
        'total_focus_sessions' => 'integer',
        'avg_touch_rate' => 'decimal:2',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
