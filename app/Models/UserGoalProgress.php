<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGoalProgress extends Model
{
    use HasFactory;

    protected $table = 'user_goal_progress';

    protected $fillable = [
        'user_id',
        'goal_id',
        'current_progress',
        'percentage',
        'is_completed',
        'completed_at',
        'goal_date',
        'period',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'goal_date' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(DailyGoalTemplate::class, 'goal_id');
    }
}
