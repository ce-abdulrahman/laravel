<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AchievementCategory extends Model
{
    use HasTranslations;

    protected $translatable = ['name'];

    protected $with = ['translations'];

    protected $fillable = [
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(AchievementCategoryTranslation::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class, 'category_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
