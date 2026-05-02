<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Surah extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name_ar',
        'name_ku',
        'name_en',
        'revelation_type',
        'ayah_count',
        'page_start',
        'page_end',
        'juz_start',
        'juz_end',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ayahs()
    {
        return $this->hasMany(Ayah::class);
    }

    public function audioFiles()
    {
        return $this->hasMany(AudioFile::class);
    }

    public function memorizationPlanItems()
    {
        return $this->hasMany(MemorizationPlanItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
