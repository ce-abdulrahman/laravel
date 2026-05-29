<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ayah
 */
class AyahResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();
        $textEn = $translations->firstWhere('language_code', 'en')?->content;
        $textKu = $translations->firstWhere('language_code', 'ku')?->content;

        return [
            'id' => $this->id,
            'ayah_number' => (int) $this->ayah_number,
            'text_uthmani' => $this->text_uthmani,
            'text_en' => $textEn,
            'text_ku' => $textKu,
        ];
    }
}
