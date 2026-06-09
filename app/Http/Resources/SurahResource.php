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
        $activeCodes = \App\Models\Language::activeCodes();

        $data = [
            'id' => $this->id,
            'number' => (int) $this->number,
            'name' => $this->name,
            'total_ayahs' => (int) $this->ayah_count,
            'revelation_type' => $this->revelation_type,
        ];

        foreach ($activeCodes as $code) {
            $data['name_' . $code] = $this->getTranslation('name', $code);
        }

        return $data;
    }
}
