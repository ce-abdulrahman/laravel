<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UiTranslationVersion extends Model
{
    protected $table = 'ui_translation_versions';

    public $timestamps = false;

    protected $fillable = [
        'ui_translation_id',
        'old_value',
        'new_value',
        'changed_by',
        'change_source',
    ];

    /**
     * Get the UI translation that owns the version.
     */
    public function translation(): BelongsTo
    {
        return $this->belongsTo(UiTranslation::class, 'ui_translation_id');
    }

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
