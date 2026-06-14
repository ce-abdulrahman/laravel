<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = [
        'theme_key', 'category_id', 'preview_image', 'thumbnail', 
        'version', 'is_active', 'is_featured', 'unlock_type', 
        'unlock_value', 'min_app_version', 'max_app_version', 
        'theme_metadata', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'version' => 'integer',
        'theme_metadata' => 'array',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ThemeCategory::class, 'category_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ThemeAsset::class, 'theme_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ThemeTranslation::class, 'theme_id');
    }

    public function userThemes(): HasMany
    {
        return $this->hasMany(UserTheme::class, 'theme_id');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserThemePreference::class, 'theme_id');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ThemeDownload::class, 'theme_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ThemeUsageLog::class, 'theme_id');
    }

    public function getNameAttribute(): string
    {
        return $this->getTranslatedField('name') ?: 'Theme ' . $this->theme_key;
    }

    public function getDescriptionAttribute(): string
    {
        return $this->getTranslatedField('description') ?: '';
    }

    private function getTranslatedField(string $fieldName): ?string
    {
        $locale = app()->getLocale();
        $lang = Language::where('code', $locale)->first();
        if ($lang) {
            $trans = $this->translations()
                ->where('language_id', $lang->id)
                ->where('field', $fieldName)
                ->first();
            if ($trans) {
                return $trans->value;
            }
        }
        // Fallback English
        $enLang = Language::where('code', 'en')->first();
        if ($enLang) {
            $trans = $this->translations()
                ->where('language_id', $enLang->id)
                ->where('field', $fieldName)
                ->first();
            if ($trans) {
                return $trans->value;
            }
        }
        return null;
    }
}
