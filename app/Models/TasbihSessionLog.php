<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TasbihSessionLog extends Model
{
    protected $table = 'tasbih_session_logs';

    protected $fillable = [
        'session_id',
        'event_uuid',
        'event_type',
        'value',
        'timestamp',
    ];

    protected $casts = [
        'session_id' => 'integer',
        'value' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TasbihSession::class, 'session_id');
    }
}
