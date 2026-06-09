<?php

namespace App\Traits;

use App\Models\Language;
use Illuminate\Support\Facades\Schema;

trait HasTranslations
{
    /**
     * Temporary storage for translations extracted during saving.
     */
    protected array $queuedTranslations = [];

    /**
     * Get the list of translatable attributes.
     */
    public function getTranslatableAttributes(): array
    {
        return property_exists($this, 'translatable') && is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    /**
     * Boot the trait and register Eloquent events.
     */
    public static function bootHasTranslations(): void
    {
        static::saving(function ($model) {
            $model->prepareTranslationsFromAttributes();
        });

        static::saved(function ($model) {
            $model->saveTranslationsFromAttributes();
        });

        static::deleting(function ($model) {
            $model->translations()->delete();
        });
    }

    /**
     * Extract translatable values from attributes and strip non-existent columns.
     */
    public function prepareTranslationsFromAttributes(): void
    {
        if (!property_exists($this, 'translatable') || !is_array($this->translatable)) {
            return;
        }

        $activeCodes = class_exists(Language::class) ? Language::activeCodes() : ['ku', 'ar', 'en'];
        $schemaColumns = Schema::getColumnListing($this->getTable());

        foreach ($this->translatable as $field) {
            // 1. Direct translatable attribute (e.g. name)
            if (array_key_exists($field, $this->attributes)) {
                $value = $this->attributes[$field];
                $this->queuedTranslations[$field][app()->getLocale()] = $value;

                // Strip from attributes if the column doesn't exist in the database table
                if (!in_array($field, $schemaColumns)) {
                    unset($this->attributes[$field]);
                }
            }

            // 2. Suffix translatable attributes (e.g. name_ku)
            foreach ($activeCodes as $locale) {
                $suffixKey = $field . '_' . $locale;
                if (array_key_exists($suffixKey, $this->attributes)) {
                    $value = $this->attributes[$suffixKey];
                    $this->queuedTranslations[$field][$locale] = $value;

                    // Strip from attributes if the column doesn't exist in the database table
                    if (!in_array($suffixKey, $schemaColumns)) {
                        unset($this->attributes[$suffixKey]);
                    }
                }
            }
        }
    }

    /**
     * Persist any queued translations to the database.
     */
    public function saveTranslationsFromAttributes(): void
    {
        if (!empty($this->queuedTranslations)) {
            foreach ($this->queuedTranslations as $field => $locales) {
                foreach ($locales as $locale => $value) {
                    $this->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [$field => $value]
                    );
                }
            }
            $this->queuedTranslations = [];
        }

        if ($this->relationLoaded('translations')) {
            $this->unsetRelation('translations');
        }
    }

    /**
     * Persist nested translation arrays (e.g. from translations.* inputs) to the database.
     */
    public function saveTranslationsFromArray(array $translations): void
    {
        foreach ($translations as $locale => $data) {
            if (is_array($data) && !empty($data)) {
                $this->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $data
                );
            }
        }

        if ($this->relationLoaded('translations')) {
            $this->unsetRelation('translations');
        }
    }

    /**
     * Get a translation and the resolved locale code it was found in.
     * Fallback chain: requested/current locale -> fallback locale -> any active DB locale -> first available.
     *
     * @param string $field
     * @param string|null $locale
     * @return array Contains 'value' and 'locale' keys
     */
    public function getTranslationWithLocale(string $field, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        // 1. Try requested locale
        $translation = $this->translations
            ->where('locale', $locale)
            ->first();
        $value = $translation?->{$field};

        if ($value !== null && $value !== '') {
            return ['value' => $value, 'locale' => $locale];
        }

        // 2. Try configuration fallback locale
        $fallback = config('app.fallback_locale', 'en');
        if ($fallback !== $locale) {
            $translation = $this->translations
                ->where('locale', $fallback)
                ->first();
            $value = $translation?->{$field};

            if ($value !== null && $value !== '') {
                return ['value' => $value, 'locale' => $fallback];
            }
        }

        // 3. Try other active locales from the languages registry
        if (class_exists(Language::class)) {
            $activeCodes = Language::activeCodes();
            foreach ($activeCodes as $code) {
                if ($code === $locale || $code === $fallback) {
                    continue;
                }
                $translation = $this->translations
                    ->where('locale', $code)
                    ->first();
                $value = $translation?->{$field};

                if ($value !== null && $value !== '') {
                    return ['value' => $value, 'locale' => $code];
                }
            }
        }

        // 4. Last resort: first available non-empty translation in the database
        $translation = $this->translations
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->first();

        if ($translation) {
            return ['value' => $translation->{$field}, 'locale' => $translation->locale];
        }

        return ['value' => null, 'locale' => null];
    }

    /**
     * Get a translation for a specific field and locale.
     */
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        return $this->getTranslationWithLocale($field, $locale)['value'];
    }

    /**
     * Get CSS and HTML attributes for displaying a translation dynamically.
     * Decouples the presentation metadata from translation resolution.
     *
     * @param string $field
     * @param string|null $locale
     * @return array
     */
    public function getTranslationAttributes(string $field, ?string $locale = null): array
    {
        $res = $this->getTranslationWithLocale($field, $locale);
        $value = $res['value'];
        $resolvedLocale = $res['locale'];

        if ($value === null || $value === '') {
            return [
                'value' => null,
                'dir' => 'ltr',
                'class' => 'text-muted',
                'style' => 'text-align: left;',
            ];
        }

        $lang = $resolvedLocale ? Language::activeList()->where('code', $resolvedLocale)->first() : null;

        return [
            'value' => $value,
            'dir' => $lang ? $lang->direction : 'ltr',
            'class' => $lang ? trim("{$lang->typography_class} {$lang->align_class}") : '',
            'style' => $lang ? "text-align: {$lang->text_align};" : '',
        ];
    }

    /**
     * Get the SQL expression (subquery or COALESCE of subqueries) for the resolved translation of a field.
     */
    public static function getResolvedTranslationSql(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');
        
        $instance = new static;
        $table = $instance->getTable();
        $primaryKey = $instance->getKeyName();
        $translationTable = $instance->translations()->getRelated()->getTable();
        $foreignKey = $instance->translations()->getForeignKeyName();

        $locales = [$locale];
        if ($fallback !== $locale) {
            $locales[] = $fallback;
        }

        if (class_exists(Language::class)) {
            foreach (Language::activeCodes() as $code) {
                if (!in_array($code, $locales)) {
                    $locales[] = $code;
                }
            }
        }

        $subqueries = [];
        foreach ($locales as $code) {
            $subqueries[] = "(SELECT {$field} FROM {$translationTable} WHERE {$translationTable}.{$foreignKey} = {$table}.{$primaryKey} AND {$translationTable}.locale = '{$code}' LIMIT 1)";
        }

        $subqueries[] = "(SELECT {$field} FROM {$translationTable} WHERE {$translationTable}.{$foreignKey} = {$table}.{$primaryKey} AND {$translationTable}.{$field} IS NOT NULL AND {$translationTable}.{$field} != '' ORDER BY {$translationTable}.id LIMIT 1)";

        return "COALESCE(" . implode(', ', $subqueries) . ")";
    }

    /**
     * Scope to search by resolved translation value.
     */
    public function scopeWhereTranslationLike($query, string $field, string $search)
    {
        $sql = static::getResolvedTranslationSql($field);
        return $query->whereRaw("({$sql}) LIKE ?", ["%{$search}%"]);
    }

    /**
     * Scope to orWhere by resolved translation value.
     */
    public function scopeOrWhereTranslationLike($query, string $field, string $search)
    {
        $sql = static::getResolvedTranslationSql($field);
        return $query->orWhereRaw("({$sql}) LIKE ?", ["%{$search}%"]);
    }

    /**
     * Scope to search a translatable field across any active languages simultaneously.
     * Uses EXISTS subquery to prevent duplicates and keep memory usage minimal.
     */
    public function scopeWhereTranslationLikeAny($query, string $field, string $search)
    {
        if (trim($search) === '') {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereHas('translations', function ($q) use ($field, $search) {
            $q->whereIn('locale', class_exists(Language::class) ? Language::activeCodes() : ['ku', 'ar', 'en'])
              ->whereNotNull($field)
              ->where($field, '!=', '')
              ->where($field, 'like', "%{$search}%");
        });
    }

    /**
     * Scope to orWhere a translatable field across any active languages simultaneously.
     */
    public function scopeOrWhereTranslationLikeAny($query, string $field, string $search)
    {
        if (trim($search) === '') {
            return $query;
        }
        return $query->orWhereHas('translations', function ($q) use ($field, $search) {
            $q->whereIn('locale', class_exists(Language::class) ? Language::activeCodes() : ['ku', 'ar', 'en'])
              ->whereNotNull($field)
              ->where($field, '!=', '')
              ->where($field, 'like', "%{$search}%");
        });
    }

    /**
     * Scope to order by resolved translation value.
     * Secondary sort by primary key ensures deterministic ordering.
     */
    public function scopeOrderByTranslation($query, string $field, string $direction = 'asc')
    {
        $sql = static::getResolvedTranslationSql($field);
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        return $query->orderByRaw("({$sql}) {$direction}")
            ->orderBy($this->getTable() . '.' . $this->getKeyName(), $direction);
    }

    /**
     * Set a translation for a specific field and locale.
     */
    public function setTranslation(string $field, string $locale, ?string $value): self
    {
        $this->translations()->updateOrCreate(
            ['locale' => $locale],
            [$field => $value]
        );

        if ($this->relationLoaded('translations')) {
            $this->unsetRelation('translations');
        }

        return $this;
    }

    /**
     * Define the relationship to translations.
     */
    public function translations()
    {
        return $this->hasMany($this->getTranslationModelClass());
    }

    /**
     * Define the single translation relationship for current locale.
     */
    public function translation(?string $locale = null)
    {
        $locale ??= app()->getLocale();
        return $this->hasOne($this->getTranslationModelClass())
            ->where('locale', $locale);
    }

    /**
     * Get the class name of the translation model.
     */
    protected function getTranslationModelClass(): string
    {
        if (isset(static::$translationModel)) {
            return static::$translationModel;
        }
        return 'App\\Models\\' . class_basename($this) . 'Translation';
    }

    /**
     * Override Eloquent's getAttribute to dynamically route translatable attributes.
     * Supports both $model->field and suffix format $model->field_locale (e.g. $model->name_ku)
     */
    public function getAttribute($key)
    {
        // 1. Direct translatable attribute lookup (e.g. $model->name)
        if (property_exists($this, 'translatable') && is_array($this->translatable) && in_array($key, $this->translatable)) {
            return $this->getTranslation($key);
        }

        // 2. Suffix translatable attribute lookup (e.g. $model->name_ku)
        if (property_exists($this, 'translatable') && is_array($this->translatable)) {
            foreach ($this->translatable as $field) {
                if (str_starts_with($key, $field . '_')) {
                    $locale = substr($key, strlen($field) + 1);
                    if (class_exists(Language::class) && in_array($locale, Language::activeCodes())) {
                        if (array_key_exists($key, $this->attributes)) {
                            return $this->attributes[$key];
                        }
                        return $this->getTranslation($field, $locale);
                    }
                }
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Override Eloquent's toArray to dynamically append translatable properties.
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        if (property_exists($this, 'translatable') && is_array($this->translatable)) {
            foreach ($this->translatable as $field) {
                // Add the dynamic localized field (e.g. 'name') to the top-level array
                $array[$field] = $this->getTranslation($field);
                
                // For backward compatibility, also add 'field_locale' (e.g. 'name_ar')
                $activeCodes = class_exists(Language::class) ? Language::activeCodes() : ['ku', 'ar', 'en'];
                foreach ($activeCodes as $locale) {
                    $suffixKey = $field . '_' . $locale;
                    if (!array_key_exists($suffixKey, $array)) {
                        $array[$suffixKey] = $this->getTranslation($field, $locale);
                    }
                }
            }
        }

        return $array;
    }
}
