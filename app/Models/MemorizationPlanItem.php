<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemorizationPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'memorization_plan_id',
        'surah_id',
        'from_ayah_id',
        'to_ayah_id',
        'day_number',
        'target_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
        ];
    }

    public function memorizationPlan()
    {
        return $this->belongsTo(MemorizationPlan::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function fromAyah()
    {
        return $this->belongsTo(Ayah::class, 'from_ayah_id');
    }

    public function toAyah()
    {
        return $this->belongsTo(Ayah::class, 'to_ayah_id');
    }
}
