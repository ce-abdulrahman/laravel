<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyGoalTemplate extends Model
{
    use HasTranslations;

    protected $table = 'daily_goal_templates';

    protected $translatable = ['title', 'description'];

    protected $with = ['translations'];

    protected $fillable = [
        'value',
        'is_active',
    ];

    protected $casts = [
        'value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Define the relationship to translations explicitly override foreign key.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(DailyGoalTemplateTranslation::class, 'daily_goal_id');
    }

    /**
     * Define the single translation relationship for current locale override foreign key.
     */
    public function translation(?string $locale = null): HasOne
    {
        $locale ??= app()->getLocale();
        return $this->hasOne(DailyGoalTemplateTranslation::class, 'daily_goal_id')
            ->where('locale', $locale);
    }
}
