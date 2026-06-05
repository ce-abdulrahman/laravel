<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'app_logo',
        'default_language',
        'default_tafsir_book_id',
        'default_reciter_id',
        'default_qiraah_id',
        'about_text',
        'contact_email',
        'font_ar',
        'font_ku',
        'font_en',
    ];

    public function defaultTafsirBook()
    {
        return $this->belongsTo(TafsirBook::class, 'default_tafsir_book_id');
    }

    public function defaultReciter()
    {
        return $this->belongsTo(Reciter::class, 'default_reciter_id');
    }

    public function defaultQiraat()
    {
        return $this->belongsTo(Qiraat::class, 'default_qiraah_id');
    }
}
