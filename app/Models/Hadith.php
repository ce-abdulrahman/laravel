<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hadith extends Model
{
    use HasTranslations;

    protected $table = 'hadiths';

    protected $translatable = ['translation', 'explanation'];

    protected $with = ['translations'];

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
