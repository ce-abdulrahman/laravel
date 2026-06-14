<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileTranslation extends Model
{
    protected $fillable = [
        'user_profile_id',
        'language_id',
        'field',
        'value'
    ];

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'user_profile_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
