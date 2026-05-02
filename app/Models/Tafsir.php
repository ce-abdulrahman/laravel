<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tafsir extends Model
{
    use HasFactory;

    protected $fillable = [
        'ayah_id',
        'tafsir_book_id',
        'content',
        'short_content',
        'source_reference',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }

    public function tafsirBook()
    {
        return $this->belongsTo(TafsirBook::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
