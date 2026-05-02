<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AudioFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'reciter_id',
        'surah_id',
        'ayah_id',
        'file_path',
        'duration_seconds',
        'quality',
        'source_type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function reciter()
    {
        return $this->belongsTo(Reciter::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
