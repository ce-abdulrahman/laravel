<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReciterUsageHistory extends Model
{
    use HasFactory;

    protected $table = 'reciter_usage_history';

    protected $fillable = [
        'reciter_id',
        'user_id',
        'last_used_at',
        'usage_count',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
    ];

    public function reciter()
    {
        return $this->belongsTo(Reciter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
