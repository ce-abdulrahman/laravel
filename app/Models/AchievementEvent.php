<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementEvent extends Model
{
    // Append-only audit log — no updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'event_type',
        'event_value',
        'created_at',
    ];

    protected $casts = [
        'event_value' => 'integer',
        'created_at'  => 'datetime',
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
}
