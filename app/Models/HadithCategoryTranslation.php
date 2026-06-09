<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HadithCategoryTranslation extends Model
{
    protected $fillable = [
        'hadith_category_id',
        'locale',
        'name',
    ];

    public function hadithCategory(): BelongsTo
    {
        return $this->belongsTo(HadithCategory::class);
    }
}
