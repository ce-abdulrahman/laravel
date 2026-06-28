<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Surah extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['name'];

    protected $with = ['translations'];

    protected $fillable = [
        'number',
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

    public function tajweedSegments()
    {
        return $this->hasMany(AyahTajweedSegment::class);
    }

    /**
     * @deprecated Use ayahTimings() instead
     */
    public function audioFiles()
    {
        return $this->hasMany(AudioFile::class);
    }

    public function ayahTimings()
    {
        return $this->hasMany(AyahTiming::class);
    }

    public function memorizationPlanItems()
    {
        return $this->hasMany(MemorizationPlanItem::class);
    }

    public function audioFavorites()
    {
        return $this->morphMany(AudioFavorite::class, 'favoritable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
