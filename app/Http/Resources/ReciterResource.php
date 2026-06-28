<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Reciter
 */
class ReciterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'riwayah' => $this->riwayah,
            'country' => $this->country,
            'language' => $this->language,
            'image' => $this->image ? url(Storage::url($this->image)) : null, // Handle image url generation if relative path
            'supports_ayah_audio' => (bool) $this->supports_ayah_audio,
        ];
    }
}
