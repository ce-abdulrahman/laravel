<?php

namespace App\Models;

use App\Services\QuranApiCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Key/value rows for GET /api/settings (mobile Mushaf).
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class SettingEntry extends Model
{
    use HasFactory;

    protected $table = 'setting_entries';

    public $timestamps = true;

    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => QuranApiCache::forgetSettings());
        static::deleted(fn () => QuranApiCache::forgetSettings());
    }
}
