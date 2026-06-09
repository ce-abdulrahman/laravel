<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationKey extends Model
{
    protected $fillable = [
        'key',
        'group',
        'description',
    ];

    /**
     * Get the translations associated with this key.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(UiTranslation::class, 'translation_key_id');
    }
}
