<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdhkarCategory extends Model
{
    use HasTranslations;

    protected $translatable = ['name'];

    protected $with = ['translations'];

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

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
