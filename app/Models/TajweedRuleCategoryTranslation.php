<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TajweedRuleCategoryTranslation extends Model
{
    protected $fillable = [
        'tajweed_rule_category_id',
        'locale',
        'name',
        'description',
    ];

    public function tajweedRuleCategory(): BelongsTo
    {
        return $this->belongsTo(TajweedRuleCategory::class);
    }
}
