<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'timezone',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function prayerTimes(): HasMany
    {
        return $this->hasMany(PrayerTime::class);
    }
}
