<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPrayerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prayer_method_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prayerMethod()
    {
        return $this->belongsTo(PrayerMethod::class);
    }
}
