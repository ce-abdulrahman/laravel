<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemorizationReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ayah_id',
        'review_date',
        'review_level',
        'result',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
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
