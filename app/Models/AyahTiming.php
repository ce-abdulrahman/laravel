<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AyahTiming extends Model
{
    use HasFactory;

    protected $fillable = [
        'reciter_id',
        'surah_id',
        'timing_file_path',
        'duration_seconds',
    ];

    public function reciter()
    {
        return $this->belongsTo(Reciter::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }
}
