<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'nickname',
        'public_title',
        'profile_quote',
        'preferences',
        'settings'
    ];

    protected $casts = [
        'preferences' => 'array',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function translations()
    {
        return $this->hasMany(ProfileTranslation::class);
    }
}
