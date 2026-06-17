<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UiTranslation extends Model
{
    protected $table = 'ui_translations';

    protected $fillable = [
        'translation_key_id',
        'language_id',
        'value',
        'is_auto_generated',
    ];

    protected $casts = [
        'is_auto_generated' => 'boolean',
    ];

    /**
     * Thread-safe static variable to define the source of the change programmatically.
     */
    public static string $currentChangeSource = 'manual';

    /**
     * Define the relationship to translation versions.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(UiTranslationVersion::class, 'ui_translation_id');
    }

    /**
     * Get the latest translation version.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(UiTranslationVersion::class, 'ui_translation_id')->latestOfMany();
    }

    /**
     * Rollback the translation value to a specific version.
     */
    public function rollback(int $versionId): bool
    {
        $version = $this->versions()->findOrFail($versionId);
        
        $oldSource = self::$currentChangeSource;
        self::$currentChangeSource = 'rollback';
        
        $this->value = $version->new_value;
        $this->is_auto_generated = false;
        $saved = $this->save();
        
        self::$currentChangeSource = $oldSource;
        
        return $saved;
    }

    /**
     * Get the translation key that owns the translation.
     */
    public function key(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'translation_key_id');
    }

    /**
     * Get the language that owns the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    /**
     * Register model observers to automatically log versions on save/update.
     */
    protected static function booted(): void
    {
        // Log version when an existing translation is updated with a new value
        static::updating(function (UiTranslation $translation) {
            if ($translation->isDirty('value')) {
                $keyStr = $translation->key->key ?? '';
                $group = $translation->key->group ?? 'general';
                $latestVer = UiTranslationVersion::where('key', $keyStr)->max('version') ?? 0;
                $nextVer = $latestVer + 1;

                UiTranslationVersion::create([
                    'ui_translation_id' => $translation->id,
                    'old_value' => $translation->getOriginal('value'),
                    'new_value' => $translation->value,
                    'changed_by' => auth()->id(),
                    'change_source' => self::$currentChangeSource,
                    'key' => $keyStr,
                    'module' => $group,
                    'version' => $nextVer,
                    'is_auto_generated' => (bool) ($translation->is_auto_generated ?? false),
                ]);
            }
        });

        // Log version when a translation is created with an initial value
        static::created(function (UiTranslation $translation) {
            if ($translation->value !== null && $translation->value !== '') {
                $keyStr = $translation->key->key ?? '';
                $group = $translation->key->group ?? 'general';
                $latestVer = UiTranslationVersion::where('key', $keyStr)->max('version') ?? 0;
                $nextVer = $latestVer + 1;

                UiTranslationVersion::create([
                    'ui_translation_id' => $translation->id,
                    'old_value' => null,
                    'new_value' => $translation->value,
                    'changed_by' => auth()->id(),
                    'change_source' => self::$currentChangeSource,
                    'key' => $keyStr,
                    'module' => $group,
                    'version' => $nextVer,
                    'is_auto_generated' => (bool) ($translation->is_auto_generated ?? false),
                ]);
            }
        });

        static::saved(function (UiTranslation $translation) {
            $language = $translation->language;
            if ($language) {
                app(\App\Services\TranslationService::class)->clearCache($language->code);
            } else {
                app(\App\Services\TranslationService::class)->clearCache();
            }
            \Illuminate\Support\Facades\Cache::forget('translation:coverage');
            \Illuminate\Support\Facades\Cache::forget('translation:missing_report');
            \App\Services\TranslationRegistryService::clearStaticCache();
        });

        static::deleted(function (UiTranslation $translation) {
            $language = $translation->language;
            if ($language) {
                app(\App\Services\TranslationService::class)->clearCache($language->code);
            } else {
                app(\App\Services\TranslationService::class)->clearCache();
            }
            \Illuminate\Support\Facades\Cache::forget('translation:coverage');
            \Illuminate\Support\Facades\Cache::forget('translation:missing_report');
            \App\Services\TranslationRegistryService::clearStaticCache();
        });
    }
}
