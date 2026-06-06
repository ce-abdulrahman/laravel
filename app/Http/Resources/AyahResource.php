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
            'tajweed_segments' => $this->relationLoaded('tajweedSegments')
                ? $this->tajweedSegments->map(function ($segment) {
                    return [
                        'text_segment' => $segment->text_segment,
                        'start_index' => $segment->start_index !== null ? (int) $segment->start_index : null,
                        'end_index' => $segment->end_index !== null ? (int) $segment->end_index : null,
                        'note' => $segment->note,
                        'rule' => $segment->tajweedRule ? [
                            'slug' => $segment->tajweedRule->slug,
                            'name' => $segment->tajweedRule->name,
                            'name_ku' => $segment->tajweedRule->name_ku,
                            'name_ar' => $segment->tajweedRule->name_ar,
                            'color_code' => $segment->tajweedRule->color_code,
                        ] : null,
                    ];
                })->values()->all() : [],
        ];
    }
}
