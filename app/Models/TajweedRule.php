<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TajweedRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ku',
        'name_ar',
        'slug',
        'category',
        'color_code',
        'description',
        'description_ku',
        'example_text',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ayahTajweedSegments()
    {
        return $this->hasMany(AyahTajweedSegment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
