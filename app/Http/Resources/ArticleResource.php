<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Article
 */
class ArticleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,

            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'workflow_stage' => $this->workflow_stage?->value,
            'workflow_stage_label' => $this->workflow_stage?->label(),
            'language' => $this->language->value,

            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'body_en' => $this->body_en,
            'meta' => $this->meta,

            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords' => $this->seo_keywords,
            'canonical_url' => $this->canonical_url,
            'source_url' => $this->source_url,

            'is_breaking' => $this->is_breaking,
            'is_featured' => $this->is_featured,
            'is_ai_generated' => $this->is_ai_generated,
            'views_count' => $this->views_count,

            'published_at' => $this->published_at,
            'scheduled_at' => $this->scheduled_at,

            'category' => CategoryResource::make($this->whenLoaded('category')),
            'additional_categories' => CategoryResource::collection($this->whenLoaded('additionalCategories')),
            'author' => UserSummaryResource::make($this->whenLoaded('author')),
            'editor' => UserSummaryResource::make($this->whenLoaded('editor')),
            'featured_media' => MediaResource::make($this->whenLoaded('featuredMedia')),
            'media' => MediaResource::collection($this->whenLoaded('media')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
