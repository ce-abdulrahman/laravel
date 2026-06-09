<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TajweedRuleTranslation extends Model
{
    protected $fillable = [
        'tajweed_rule_id',
        'locale',
        'name',
        'description',
    ];

    public function tajweedRule(): BelongsTo
    {
        return $this->belongsTo(TajweedRule::class);
    }
}
