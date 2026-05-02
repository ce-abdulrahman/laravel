<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qiraat extends Model
{
    use HasFactory;

    protected $table = 'qiraats';

    protected $fillable = [
        'name',
        'riwayah',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function texts()
    {
        return $this->hasMany(QiraatText::class, 'qiraah_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTextsCountAttribute()
    {
        return $this->texts()->count();
    }

    public function scopeSearch($query, $value)
    {
        if (empty($value)) {
            return $query;
        }
        
        // Search in name and description
        return $query->where('name', 'LIKE', "%{$value}%")
                     ->orWhere('description', 'LIKE', "%{$value}%");
    }

    public function scopeFilterByRiwayah($query, $riwayah)
    {
        if (empty($riwayah)) {
            return $query;
        }
        
        return $query->where('riwayah', $riwayah);
    }

    public function scopeFilterByStatus($query, $status)
    {
        if (empty($status)) {
            return $query;
        }
        
        $isActive = ($status === 'active');
        return $query->where('is_active', $isActive);
    }
}
