<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightLog extends Model
{
    protected $fillable = [
        'user_id',
        'insight_type',
        'insight_data',
        'icon',
        'generated_at',
        'expires_at',
        'is_read',
    ];

    protected $casts = [
        'insight_data' => 'array',
        'generated_at' => 'datetime',
        'expires_at'   => 'datetime',
        'is_read'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: only fresh (not expired) insights.
     */
    public function scopeFresh($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
