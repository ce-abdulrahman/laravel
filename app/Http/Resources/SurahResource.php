<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Surah
 */
class SurahResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => (int) $this->number,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name_ku' => $this->name_ku,
            'total_ayahs' => (int) $this->ayah_count,
            'revelation_type' => $this->revelation_type,
        ];
    }
}
