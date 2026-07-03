<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reciter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'riwayah',
        'country',
        'language',
        'image',
        'audio_base_url',
        'supports_ayah_audio',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'supports_ayah_audio' => 'boolean',
        ];
    }

    /**
     * Set the audio_base_url attribute ensuring it ends with a trailing slash.
     */
    protected function audioBaseUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn (string $value) => rtrim($value, '/') . '/',
        );
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

    public function audioFavorites()
    {
        return $this->morphMany(AudioFavorite::class, 'favoritable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
