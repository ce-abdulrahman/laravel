<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Achievement extends Model
{
    use HasTranslations;

    protected $translatable = ['name', 'description'];

    protected $with = ['translations'];

    protected $fillable = [
        'key',
        'category_id',
        'icon',
        'badge_image',
        'condition_type',
        'condition_value',
        'condition_meta',
        'reward_type',
        'reward_points',
        'reward_value',
        'version',
        'is_hidden',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'condition_value' => 'integer',
        'condition_meta'  => 'array',
        'reward_points'   => 'integer',
        'version'         => 'integer',
        'sort_order'      => 'integer',
        'is_hidden'       => 'boolean',
        'is_active'       => 'boolean',
    ];

    // Supported condition types
    public const CONDITION_TOTAL_DHIKR       = 'TOTAL_DHIKR';
    public const CONDITION_CURRENT_STREAK    = 'CURRENT_STREAK';
    public const CONDITION_LONGEST_STREAK    = 'LONGEST_STREAK';
    public const CONDITION_GOALS_COMPLETED   = 'GOALS_COMPLETED';
    public const CONDITION_SESSION_DHIKR     = 'SESSION_DHIKR_COUNT';
    public const CONDITION_CONSECUTIVE_DAYS  = 'CONSECUTIVE_DAYS';
    public const CONDITION_SPECIAL_EVENT     = 'SPECIAL_EVENT';
    public const CONDITION_CUSTOM_RULE       = 'CUSTOM_RULE';
    public const CONDITION_FINGERPRINT_TOTAL_COUNTS   = 'FINGERPRINT_TOTAL_COUNTS';
    public const CONDITION_FINGERPRINT_TOTAL_SESSIONS = 'FINGERPRINT_TOTAL_SESSIONS';
    public const CONDITION_FINGERPRINT_BLIND_SESSIONS = 'FINGERPRINT_BLIND_SESSIONS';
    public const CONDITION_FINGERPRINT_FOCUS_SESSIONS = 'FINGERPRINT_FOCUS_SESSIONS';

    // Reward types
    public const REWARD_POINTS         = 'POINTS';
    public const REWARD_BADGE          = 'BADGE';
    public const REWARD_TITLE          = 'TITLE';
    public const REWARD_SPECIAL_THEME  = 'SPECIAL_THEME';
    public const REWARD_FUTURE         = 'FUTURE_REWARD';

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(AchievementTranslation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCondition($query, string $conditionType)
    {
        return $query->where('condition_type', $conditionType);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ─── Cache Helpers ───────────────────────────────────────────────────────────

    /**
     * Get all active achievements grouped by condition_type, from cache.
     * Used by AchievementEngine to avoid N+1 queries on every evaluation.
     */
    public static function cachedByConditionType(): array
    {
        return Cache::remember('achievements:by_condition_type', 900, function () {
            return static::active()
                ->with('translations')
                ->ordered()
                ->get()
                ->groupBy('condition_type')
                ->toArray();
        });
    }

    /**
     * Bust the achievement cache whenever an achievement is saved or deleted.
     */
    protected static function booted(): void
    {
        $bust = fn() => Cache::forget('achievements:by_condition_type');
        static::saved($bust);
        static::deleted($bust);
    }
}
