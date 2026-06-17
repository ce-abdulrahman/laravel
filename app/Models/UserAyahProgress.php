<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAyahProgress extends Model
{
    use HasFactory;

    protected $table = 'user_ayah_progress';

    protected $fillable = [
        'user_id',
        'ayah_id',
        'memorize_status',
        'last_memorized_at',
        'last_reviewed_at',
        'next_review_date',
        'review_count',
        'current_interval_days',
        'mastery_level',
        'last_review_result',
        'strength_score',
        'mistakes_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_memorized_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'next_review_date' => 'date',
            'mastery_level' => \App\Enums\MasteryLevel::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }
}
