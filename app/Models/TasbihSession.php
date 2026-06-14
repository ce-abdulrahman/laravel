<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TasbihSession extends Model
{
    protected $table = 'tasbih_sessions';

    protected $fillable = [
        'user_id',
        'dhikr_id',
        'custom_dhikr_name',
        'start_time',
        'end_time',
        'duration_seconds',
        'total_count',
        'avg_per_minute',
        'session_date',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'dhikr_id' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_seconds' => 'integer',
        'total_count' => 'integer',
        'avg_per_minute' => 'decimal:2',
        'session_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dhikr(): BelongsTo
    {
        return $this->belongsTo(Tasbih::class, 'dhikr_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TasbihSessionLog::class, 'session_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TasbihSessionTranslation::class, 'tasbih_session_id');
    }

    /**
     * Helper to get translated custom dhikr name if available.
     */
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $lang = Language::where('code', $locale)->first();
        
        if ($lang) {
            $trans = $this->translations()
                ->where('language_id', $lang->id)
                ->where('field', $field)
                ->value('value');
            if ($trans) {
                return $trans;
            }
        }

        return $this->attributes[$field] ?? null;
    }
}
