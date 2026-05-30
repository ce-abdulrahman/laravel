<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdhkarCategory extends Model
{
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

    public function adhkars(): HasMany
    {
        return $this->hasMany(Adhkar::class, 'category_id')->orderBy('order');
    }
}
