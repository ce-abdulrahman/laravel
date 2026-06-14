<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserBackup extends Model
{
    protected $table = 'user_backups';

    protected $fillable = [
        'user_id',
        'backup_type',
        'storage_type',
        'backup_version',
        'file_name',
        'file_size',
        'checksum_sha256',
        'is_encrypted',
        'status',
        'device_type',
        'platform',
        'app_version',
        'is_processing',
        'expires_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_encrypted' => 'boolean',
        'is_processing' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restoreLogs(): HasMany
    {
        return $this->hasMany(BackupRestoreLog::class, 'backup_id');
    }
}
