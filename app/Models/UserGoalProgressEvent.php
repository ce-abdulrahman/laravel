<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGoalProgressEvent extends Model
{
    use HasFactory;

    protected $table = 'user_goal_progress_events';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
