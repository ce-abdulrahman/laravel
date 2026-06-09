<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HadithTranslation extends Model
{
    protected $fillable = [
        'hadith_id',
        'locale',
        'translation',
        'explanation',
    ];

    public function hadith(): BelongsTo
    {
        return $this->belongsTo(Hadith::class);
    }
}
