<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/**
 * ReminderTemplate — Admin-managed notification template.
 *
 * Translatable fields: title, body
 * Each template maps to one reminder type (e.g. MORNING, STREAK, DAILY_GOAL).
 */
class ReminderTemplate extends Model
{
    use HasTranslations, SoftDeletes;

    protected $translatable = ['title', 'body'];

    protected $with = ['translations'];

    protected $fillable = [
        'key',
        'type',
        'icon',
        'priority',
        'sort_order',
        'version',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'priority'   => 'integer',
        'sort_order' => 'integer',
        'version'    => 'integer',
        'is_active'  => 'boolean',
        'metadata'   => 'array',
    ];

    // ─── Reminder Type Constants ─────────────────────────────────────────────────
    // Using class constants (not ENUM) — add new types without migrations

    public const TYPE_MORNING      = 'MORNING';
    public const TYPE_AFTERNOON    = 'AFTERNOON';
    public const TYPE_EVENING      = 'EVENING';
    public const TYPE_BEFORE_SLEEP = 'BEFORE_SLEEP';
    public const TYPE_DAILY_GOAL   = 'DAILY_GOAL';
    public const TYPE_STREAK       = 'STREAK';
    public const TYPE_ACHIEVEMENT  = 'ACHIEVEMENT';
    public const TYPE_INACTIVITY   = 'INACTIVITY';
    public const TYPE_CUSTOM       = 'CUSTOM';

    public static function allTypes(): array
    {
        return [
            self::TYPE_MORNING,
            self::TYPE_AFTERNOON,
            self::TYPE_EVENING,
            self::TYPE_BEFORE_SLEEP,
            self::TYPE_DAILY_GOAL,
            self::TYPE_STREAK,
            self::TYPE_ACHIEVEMENT,
            self::TYPE_INACTIVITY,
            self::TYPE_CUSTOM,
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(ReminderTemplateTranslation::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ReminderLog::class, 'template_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('priority')->orderBy('id');
    }

    // ─── Cache ───────────────────────────────────────────────────────────────────

    /**
     * Get all active templates indexed by type (15-min cache).
     * Used by ReminderEngine to build smart notification payloads.
     */
    public static function cachedByType(): array
    {
        return Cache::remember('reminder_templates:by_type', 900, function () {
            return static::active()
                ->with('translations')
                ->ordered()
                ->get()
                ->groupBy('type')
                ->toArray();
        });
    }

    /**
     * Get templates as a flat list from cache (for API responses).
     */
    public static function cachedAll(): array
    {
        return Cache::remember('reminder_templates:all', 900, function () {
            return static::active()->with('translations')->ordered()->get()->toArray();
        });
    }

    protected static function booted(): void
    {
        $bust = function () {
            Cache::forget('reminder_templates:by_type');
            Cache::forget('reminder_templates:all');
        };
        static::saved($bust);
        static::deleted($bust);
        static::restored($bust);
    }
}
