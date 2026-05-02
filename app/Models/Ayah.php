<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ayah extends Model
{
    use HasFactory;

    protected $fillable = [
        'surah_id',
        'ayah_number',
        'text_uthmani',
        'text_simple',
        'page_number',
        'juz_number',
        'hizb_number',
        'rub_number',
        'sajda_flag',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sajda_flag' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }

    public function tafsirs()
    {
        return $this->hasMany(Tafsir::class);
    }

    public function tajweedSegments()
    {
        return $this->hasMany(AyahTajweedSegment::class);
    }

    public function audioFiles()
    {
        return $this->hasMany(AudioFile::class);
    }

    public function qiraatTexts()
    {
        return $this->hasMany(QiraatText::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function readingHistories()
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function memorizationReviews()
    {
        return $this->hasMany(MemorizationReview::class);
    }

    public function progress()
    {
        return $this->hasMany(UserAyahProgress::class);
    }

    public function fromPlanItems()
    {
        return $this->hasMany(MemorizationPlanItem::class, 'from_ayah_id');
    }

    public function toPlanItems()
    {
        return $this->hasMany(MemorizationPlanItem::class, 'to_ayah_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
