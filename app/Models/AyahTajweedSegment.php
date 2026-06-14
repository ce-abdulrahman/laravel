<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AyahTajweedSegment
 *
 * @property int $id
 * @property int $surah_id
 * @property int $ayah_id
 * @property int $tajweed_rule_id
 * @property string $matched_text
 * @property string $text_segment Deprecated alias for matched_text
 * @property int|null $start_index
 * @property int|null $end_index
 * @property array|null $metadata
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ayah $ayah
 * @property-read \App\Models\Surah $surah
 * @property-read \App\Models\TajweedRule $tajweedRule
 */
class AyahTajweedSegment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'surah_id',
        'ayah_id',
        'tajweed_rule_id',
        'matched_text',
        'text_segment', // For backward compatibility in seeding/mass-assignment
        'start_index',
        'end_index',
        'metadata',
        'note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'start_index' => 'integer',
            'end_index' => 'integer',
        ];
    }

    /**
     * Get the Surah that contains this segment.
     */
    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }

    /**
     * Get the Ayah that contains this segment.
     */
    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }

    /**
     * Get the Tajweed Rule applied to this segment.
     */
    public function tajweedRule(): BelongsTo
    {
        return $this->belongsTo(TajweedRule::class);
    }

    /**
     * Mutator for backward compatibility: maps text_segment to matched_text.
     */
    public function setTextSegmentAttribute(?string $value): void
    {
        $this->attributes['matched_text'] = $value;
    }

    /**
     * Accessor for backward compatibility: returns matched_text as text_segment.
     */
    public function getTextSegmentAttribute(): string
    {
        return $this->attributes['matched_text'] ?? '';
    }
}
