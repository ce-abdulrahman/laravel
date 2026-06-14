<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeAsset extends Model
{
    protected $fillable = ['theme_id', 'asset_type', 'file_path', 'file_size', 'checksum', 'version'];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
    ];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }
}
