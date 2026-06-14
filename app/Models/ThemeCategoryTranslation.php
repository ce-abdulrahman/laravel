<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeCategoryTranslation extends Model
{
    protected $fillable = ['theme_category_id', 'language_id', 'field', 'value'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ThemeCategory::class, 'theme_category_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
