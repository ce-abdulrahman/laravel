<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemorizationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_type',
        'status',
        'started_at',
        'ended_at',
        'completed_at',
        'duration_seconds',
        'ayahs_reviewed',
        'ayahs_memorized',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
