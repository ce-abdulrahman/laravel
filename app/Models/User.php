<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MemorizationReview;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function readingHistories()
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function memorizationPlans()
    {
        return $this->hasMany(MemorizationPlan::class);
    }

    public function memorizationReviews()
    {
        return $this->hasMany(MemorizationReview::class);
    }

    public function ayahProgress()
    {
        return $this->hasMany(UserAyahProgress::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
}
