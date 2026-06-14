<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyGoalTemplateTranslation extends Model
{
    protected $table = 'daily_goal_translations';

    protected $fillable = [
        'daily_goal_id',
        'locale',
        'title',
        'description',
    ];

    public function dailyGoal(): BelongsTo
    {
        return $this->belongsTo(DailyGoalTemplate::class, 'daily_goal_id');
    }
}
