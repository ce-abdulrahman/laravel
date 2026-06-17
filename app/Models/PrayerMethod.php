<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrayerMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'config',
        'sort_order',
        'is_enabled',
    ];

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function getTranslationKeyNameAttribute(): string
    {
        return "prayer.method.{$this->key}.name";
    }

    public function getTranslationKeyDescAttribute(): string
    {
        return "prayer.method.{$this->key}.desc";
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
