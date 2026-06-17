<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFeatureOverride extends Model
{
    protected $fillable = ['user_id', 'flag_key', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
