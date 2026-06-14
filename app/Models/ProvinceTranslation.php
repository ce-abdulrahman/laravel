<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvinceTranslation extends Model
{
    protected $fillable = ['province_id', 'language_id', 'field', 'value'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
