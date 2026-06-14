<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['code'];

    public function translations()
    {
        return $this->hasMany(CountryTranslation::class);
    }

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
}
