<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reciter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'riwayah',
        'language',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function audioFiles()
    {
        return $this->hasMany(AudioFile::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
