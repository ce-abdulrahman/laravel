<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reciter_id',
        'surah_id',
        'status',
        'progress',
    ];

    protected $casts = [
        'progress' => 'float',
        'reciter_id' => 'integer',
        'surah_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
