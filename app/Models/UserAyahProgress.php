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
        'strength_score',
        'mistakes_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_memorized_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
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
