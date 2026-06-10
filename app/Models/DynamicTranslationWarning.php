<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicTranslationWarning extends Model
{
    protected $table = 'dynamic_translation_warnings';

    protected $fillable = [
        'file_path',
        'line_number',
        'expression',
    ];

    // Disable updated_at timestamps since we only need created_at
    const UPDATED_AT = null;
}
