<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeCategory extends Model
{
    protected $fillable = ['icon', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ThemeCategoryTranslation::class, 'theme_category_id');
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class, 'category_id')->orderBy('sort_order');
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        $lang = Language::where('code', $locale)->first();
        if ($lang) {
            $trans = $this->translations()->where('language_id', $lang->id)->first();
            if ($trans) {
                return $trans->value;
            }
        }
        // Fallback English
        $enLang = Language::where('code', 'en')->first();
        if ($enLang) {
            $trans = $this->translations()->where('language_id', $enLang->id)->first();
            if ($trans) {
                return $trans->value;
            }
        }
        return 'Category ' . $this->id;
    }
}
