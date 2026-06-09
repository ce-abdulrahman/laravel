<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdhkarTranslation extends Model
{
    protected $fillable = [
        'adhkar_id',
        'locale',
        'translation',
    ];

    public function adhkar(): BelongsTo
    {
        return $this->belongsTo(Adhkar::class);
    }
}
