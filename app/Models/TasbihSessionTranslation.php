<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TasbihSessionTranslation extends Model
{
    protected $table = 'tasbih_session_translations';

    protected $fillable = [
        'tasbih_session_id',
        'language_id',
        'field',
        'value',
    ];

    protected $casts = [
        'tasbih_session_id' => 'integer',
        'language_id' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TasbihSession::class, 'tasbih_session_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
