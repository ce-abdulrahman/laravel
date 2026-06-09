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
        $activeCodes = \App\Models\Language::activeCodes();

        $textEn = $translations->firstWhere('language_code', 'en')?->content;
        $translation = $translations->firstWhere('language_code', app()->getLocale())?->content 
            ?? $textEn;

        $data = [
            'id' => $this->id,
            'ayah_number' => (int) $this->ayah_number,
            'text_uthmani' => $this->text_uthmani,
            'translation' => $translation,
        ];

        foreach ($activeCodes as $code) {
            $data['text_' . $code] = $translations->firstWhere('language_code', $code)?->content;
        }

        $data['tajweed_segments'] = $this->relationLoaded('tajweedSegments')
            ? $this->tajweedSegments->map(function ($segment) use ($activeCodes) {
                return [
                    'text_segment' => $segment->text_segment,
                    'start_index' => $segment->start_index !== null ? (int) $segment->start_index : null,
                    'end_index' => $segment->end_index !== null ? (int) $segment->end_index : null,
                    'note' => $segment->note,
                    'rule' => $segment->tajweedRule ? array_merge([
                        'slug' => $segment->tajweedRule->slug,
                        'name' => $segment->tajweedRule->name,
                        'color_code' => $segment->tajweedRule->color_code,
                    ], collect($activeCodes)->mapWithKeys(function ($code) use ($segment) {
                        return ['name_' . $code => $segment->tajweedRule->getTranslation('name', $code)];
                    })->toArray()) : null,
                ];
            })->values()->all() : [];

        return $data;
    }
}
