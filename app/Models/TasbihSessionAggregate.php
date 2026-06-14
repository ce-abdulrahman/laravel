<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TasbihSessionAggregate extends Model
{
    protected $table = 'tasbih_session_aggregates';

    protected $fillable = [
        'user_id',
        'total_sessions',
        'total_dhikr_count',
        'avg_duration_seconds',
        'activity_date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_sessions' => 'integer',
        'total_dhikr_count' => 'integer',
        'avg_duration_seconds' => 'integer',
        'activity_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
