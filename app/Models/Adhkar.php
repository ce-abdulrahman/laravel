<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adhkar extends Model
{
    use HasTranslations;

    protected $table = 'adhkars';

    protected $translatable = ['translation'];

    protected $with = ['translations'];

    protected $fillable = [
        'category_id',
        'arabic_text',
        'translation_ku',
        'translation_en',
        'count',
        'source',
        'description',
        'order',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'count' => 'integer',
        'order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AdhkarCategory::class, 'category_id');
    }
}
