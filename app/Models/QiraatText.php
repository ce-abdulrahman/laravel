<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QiraatText extends Model
{
    use HasFactory;

    protected $fillable = [
        'qiraah_id',
        'ayah_id',
        'text_variant',
        'note',
    ];

    public function qiraat()
    {
        return $this->belongsTo(Qiraat::class, 'qiraah_id');
    }

    public function ayah()
    {
        return $this->belongsTo(Ayah::class);
    }
}
