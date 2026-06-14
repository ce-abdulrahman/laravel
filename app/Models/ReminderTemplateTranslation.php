<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderTemplateTranslation extends Model
{
    protected $fillable = [
        'reminder_template_id',
        'language_id',
        'field',
        'value',
    ];

    public function reminderTemplate(): BelongsTo
    {
        return $this->belongsTo(ReminderTemplate::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
