<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemorizationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'plan_type',
        'start_date',
        'target_end_date',
        'daily_target_type',
        'daily_target_value',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_end_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MemorizationPlanItem::class);
    }
}
