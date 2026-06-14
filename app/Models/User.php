<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MemorizationReview;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'gender',
        'birth_year',
        'country_id',
        'province_id',
        'role',
        'status',
        'preferred_locale',
        'points_total',
        'streak_days',
        'longest_streak',
        'last_read_date',
        'last_login_at',
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
            'last_login_at'     => 'datetime',
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

    public function reminders()
    {
        return $this->hasMany(UserReminder::class);
    }

    public function reminderLogs()
    {
        return $this->hasMany(ReminderLog::class);
    }


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

    /**
     * Get the tasbih streak associated with the user.
     */
    public function tasbihStreak()
    {
        return $this->hasOne(UserTasbihStreak::class);
    }

    /**
     * Get the tasbih daily goals associated with the user.
     */
    public function dailyGoals()
    {
        return $this->hasMany(UserDailyGoal::class);
    }

    /**
     * Get today's daily goal for the user.
     */
    public function todayGoal()
    {
        return $this->hasOne(UserDailyGoal::class)
            ->whereDate('goal_date', Carbon::now('Asia/Baghdad')->toDateString());
    }

    public function goalProgress()
    {
        return $this->hasMany(UserGoalProgress::class);
    }

    public function goalProgressEvents()
    {
        return $this->hasMany(UserGoalProgressEvent::class);
    }

    public function badges()
    {
        return $this->hasMany(UserBadge::class);
    }

    // ── Achievement System ─────────────────────────────────────────────

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function achievementEvents()
    {
        return $this->hasMany(AchievementEvent::class);
    }

    // ── Leaderboard System ─────────────────────────────────────────────

    public function leaderboardSettings()
    {
        return $this->hasOne(UserLeaderboardSetting::class, 'user_id');
    }

    public function leaderboardEntries()
    {
        return $this->hasMany(LeaderboardEntry::class, 'user_id');
    }

    public function leaderboardScores()
    {
        return $this->hasMany(LeaderboardScore::class, 'user_id');
    }

    public function tasbihSessions()
    {
        return $this->hasMany(TasbihSession::class, 'user_id');
    }

    public function tasbihSessionAggregates()
    {
        return $this->hasMany(TasbihSessionAggregate::class, 'user_id');
    }

    public function fingerprintSetting()
    {
        return $this->hasOne(FingerprintSetting::class);
    }

    public function fingerprintStatistic()
    {
        return $this->hasOne(FingerprintStatistic::class);
    }

    public function backups()
    {
        return $this->hasMany(UserBackup::class, 'user_id');
    }

    public function restoreLogs()
    {
        return $this->hasMany(BackupRestoreLog::class, 'user_id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(UserLoginLog::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function userThemes()
    {
        return $this->hasMany(UserTheme::class);
    }

    public function themePreferences()
    {
        return $this->hasMany(UserThemePreference::class);
    }

    public function themeDownloads()
    {
        return $this->hasMany(ThemeDownload::class);
    }

    public function themeUsageLogs()
    {
        return $this->hasMany(ThemeUsageLog::class);
    }

    public function getAgeAttribute()
    {
        return $this->birth_year ? (now()->year - $this->birth_year) : null;
    }

    public function getProfileCompletionPercentage(): int
    {
        $fields = [
            'name' => 20,
            'username' => 20,
            'avatar' => 15,
            'gender' => 15,
            'birth_year' => 10,
            'country_id' => 10,
            'province_id' => 10,
        ];
        
        $percentage = 0;
        foreach ($fields as $field => $weight) {
            if ($this->{$field} !== null && $this->{$field} !== '') {
                $percentage += $weight;
            }
        }
        return $percentage;
    }
}

