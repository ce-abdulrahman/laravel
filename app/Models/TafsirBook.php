<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TafsirBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'author',
        'language_code',
        'short_description',
        'source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tafsirs()
    {
        return $this->hasMany(Tafsir::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
