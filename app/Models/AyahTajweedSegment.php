<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AyahTajweedSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ayah_id',
        'tajweed_rule_id',
        'text_segment',
        'start_index',
        'end_index',
        'note',
    ];

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }

    public function tajweedRule()
    {
        return $this->belongsTo(TajweedRule::class);
    }
}
