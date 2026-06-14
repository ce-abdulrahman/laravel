<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintSessionLog extends Model
{
    protected $table = 'fingerprint_session_logs';

    protected $fillable = [
        'session_id',
        'touch_count',
        'duration_seconds',
        'touch_rate',
        'is_blind',
        'is_focus',
    ];

    protected $casts = [
        'session_id' => 'integer',
        'touch_count' => 'integer',
        'duration_seconds' => 'integer',
        'touch_rate' => 'decimal:2',
        'is_blind' => 'boolean',
        'is_focus' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TasbihSession::class, 'session_id');
    }
}
