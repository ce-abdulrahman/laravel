<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TajweedRuleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ku',
        'name_ar',
        'slug',
        'description',
        'description_ku',
        'description_ar',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order'     => 'integer',
        ];
    }

    /**
     * A category has many tajweed rules.
     */
    public function tajweedRules()
    {
        return $this->hasMany(TajweedRule::class);
    }

    /**
     * Scope: only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get localized name based on current app locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return match ($locale) {
            'ku' => $this->name_ku ?: $this->name,
            'ar' => $this->name_ar ?: $this->name,
            default => $this->name,
        };
    }
}
