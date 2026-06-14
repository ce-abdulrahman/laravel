<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatisticsSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'snapshot_date',
        'snapshot_type',
        'data_json',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'data_json'     => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
