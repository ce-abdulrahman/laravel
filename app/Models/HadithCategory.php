<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HadithCategory extends Model
{
    protected $table = 'hadith_categories';

    protected $fillable = [
        'name_ku',
        'name_ar',
        'name_en',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function hadiths(): HasMany
    {
        return $this->hasMany(Hadith::class, 'category_id')->orderBy('order');
    }
}
