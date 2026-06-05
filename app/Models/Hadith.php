<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hadith extends Model
{
    protected $table = 'hadiths';

    protected $fillable = [
        'category_id',
        'arabic_text',
        'translation_ku',
        'translation_en',
        'narrator',
        'source',
        'explanation_ku',
        'explanation_en',
        'order',
        'is_active',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(HadithCategory::class, 'category_id');
    }
}
