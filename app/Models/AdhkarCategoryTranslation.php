<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdhkarCategoryTranslation extends Model
{
    protected $fillable = [
        'adhkar_category_id',
        'locale',
        'name',
    ];

    public function adhkarCategory(): BelongsTo
    {
        return $this->belongsTo(AdhkarCategory::class);
    }
}
