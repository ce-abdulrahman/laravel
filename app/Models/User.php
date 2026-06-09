<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\MemorizationReview;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'preferred_locale',
        'points_total',
        'streak_days',
        'longest_streak',
        'last_read_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status'            => 'boolean',
            'last_read_date'    => 'date',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────

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

    // ── Role helpers ───────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ── Points & Streak helpers ────────────────────────────────────────

    /**
     * Award points and update streak for a reading event.
     * Called every time the user logs reading a new ayah.
     */
    public function recordReading(): void
    {
        $today = Carbon::today()->toDateString();

        // Award base point for reading
        $this->increment('points_total', 1);

        // Update streak
        if ($this->last_read_date === null) {
            // First ever reading
            $this->streak_days    = 1;
            $this->last_read_date = $today;
        } elseif ($this->last_read_date->toDateString() === $today) {
            // Already read today — streak unchanged, just update points
        } elseif ($this->last_read_date->toDateString() === Carbon::yesterday()->toDateString()) {
            // Read yesterday → extend streak
            $this->streak_days   += 1;
            $this->last_read_date = $today;
            // +5 streak bonus per day
            $this->points_total  += 5;
        } else {
            // Gap in reading → reset streak
            $this->streak_days    = 1;
            $this->last_read_date = $today;
        }

        // Track longest streak
        if ($this->streak_days > $this->longest_streak) {
            $this->longest_streak = $this->streak_days;
        }

        $this->save();
    }

    /**
     * Award bonus points when a surah is fully read.
     */
    public function awardSurahBonus(): void
    {
        $this->increment('points_total', 10);
    }

    /**
     * Leaderboard rank based on points_total.
     */
    public function leaderboardRank(string $period = 'alltime'): int
    {
        return User::where('points_total', '>', $this->points_total)->count() + 1;
    }
}
