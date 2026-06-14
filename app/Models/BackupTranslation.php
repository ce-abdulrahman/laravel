<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupTranslation extends Model
{
    protected $table = 'backup_translations';

    protected $fillable = [
        'translatable_id',
        'language_id',
        'field',
        'value',
    ];

    public function backupVersion(): BelongsTo
    {
        return $this->belongsTo(BackupVersion::class, 'translatable_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
