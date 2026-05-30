<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasbih extends Model
{
    protected $fillable = [
        'name',
        'target',
        'is_active',
    ];

    protected $casts = [
        'target' => 'integer',
        'is_active' => 'boolean',
    ];
}
