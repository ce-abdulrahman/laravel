<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrayerTime extends Model
{
    protected $table = 'prayer_times';

    protected $fillable = [
        'city_id',
        'date',
        'year',
        'fajr',
        'sunrise',
        'dhuhr',
        'asr',
        'maghrib',
        'isha',
        'source',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'year' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // ─── Query Scopes ─────────────────────────────────────────────────────────

    /**
     * Filter by city ID.
     */
    public function scopeForCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Filter by exact date.
     */
    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Filter by year.
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Filter by date range (inclusive).
     */
    public function scopeForDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    /**
     * Filter by source (manual | import | calculated).
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return all 6 prayer times as an array [name => HH:MM].
     */
    public function timesArray(): array
    {
        return [
            'fajr'    => $this->fajr,
            'sunrise' => $this->sunrise,
            'dhuhr'   => $this->dhuhr,
            'asr'     => $this->asr,
            'maghrib' => $this->maghrib,
            'isha'    => $this->isha,
        ];
    }
}
