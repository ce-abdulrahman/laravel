<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StatisticsSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /**
     * Get a setting value by key, with caching.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("statistics_setting_{$key}", 3600, function () use ($key, $default) {
            $row = self::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    /**
     * Set a setting value and invalidate cache.
     */
    public static function setValue(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("statistics_setting_{$key}");
    }

    /**
     * Get all productivity score weights.
     */
    public static function getProductivityWeights(): array
    {
        return [
            'streak'      => (float) self::getValue('streak_weight',      0.25),
            'goal'        => (float) self::getValue('goal_weight',        0.30),
            'session'     => (float) self::getValue('session_weight',     0.25),
            'achievement' => (float) self::getValue('achievement_weight', 0.20),
        ];
    }
}
