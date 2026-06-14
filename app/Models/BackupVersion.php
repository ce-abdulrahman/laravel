<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupVersion extends Model
{
    protected $table = 'backup_versions';

    protected $fillable = [
        'version',
        'description',
        'status',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(BackupTranslation::class, 'translatable_id');
    }

    /**
     * Get dynamic translation for a field.
     */
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $lang = Language::where('code', $locale)->first();
        if (!$lang) {
            return $this->attributes[$field] ?? null;
        }

        $translation = $this->translations()
            ->where('language_id', $lang->id)
            ->where('field', $field)
            ->first();

        if ($translation && !empty($translation->value)) {
            return $translation->value;
        }

        // Fallback to configuration locale
        $fallbackLocale = config('app.fallback_locale', 'en');
        if ($locale !== $fallbackLocale) {
            $fallbackLang = Language::where('code', $fallbackLocale)->first();
            if ($fallbackLang) {
                $fallbackTrans = $this->translations()
                    ->where('language_id', $fallbackLang->id)
                    ->where('field', $field)
                    ->first();
                if ($fallbackTrans && !empty($fallbackTrans->value)) {
                    return $fallbackTrans->value;
                }
            }
        }

        return $this->attributes[$field] ?? null;
    }

    /**
     * Get translatable attribute dynamically.
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description');
    }
}
