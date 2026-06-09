<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurahTranslation extends Model
{
    protected $fillable = [
        'surah_id',
        'locale',
        'name',
    ];

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }
}
