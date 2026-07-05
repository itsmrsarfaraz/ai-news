<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Media
 */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'mime_type' => $this->mime_type,
            'width' => $this->width,
            'height' => $this->height,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'role' => $this->whenPivotLoaded('article_media', fn () => $this->pivot->role),
            'order' => $this->whenPivotLoaded('article_media', fn () => $this->pivot->order),
            'caption_override' => $this->whenPivotLoaded('article_media', fn () => $this->pivot->caption_override),
        ];
    }
}
