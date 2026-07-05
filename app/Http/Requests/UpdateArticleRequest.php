<?php

namespace App\Http\Requests;

use App\Enums\ArticleType;
use App\Enums\Language;
use App\Enums\PublishStatus;
use App\Enums\WorkflowStage;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Article $article */
        $article = $this->route('article');

        return (bool) $this->user()?->can('update', $article);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Article $article */
        $article = $this->route('article');

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'additional_category_ids' => ['nullable', 'array'],
            'additional_category_ids.*' => ['integer', 'exists:categories,id'],

            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'editor_id' => ['nullable', 'integer', 'exists:users,id'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],

            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('articles', 'slug')->ignore($article->id)],

            'type' => ['sometimes', 'required', Rule::enum(ArticleType::class)],
            'status' => ['nullable', Rule::enum(PublishStatus::class)],
            'workflow_stage' => ['nullable', Rule::enum(WorkflowStage::class)],
            'language' => ['nullable', Rule::enum(Language::class)],

            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['sometimes', 'required', 'string'],
            'body_en' => ['nullable', 'string'],

            'meta' => ['nullable', 'array'],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'array'],
            'seo_keywords.*' => ['string', 'max:100'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:255'],

            'is_breaking' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_ai_generated' => ['nullable', 'boolean'],

            'published_at' => ['nullable', 'date'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Article $article */
            $article = $this->route('article');

            $typeInput = $this->input('type');
            $type = $typeInput !== null ? ArticleType::tryFrom((string) $typeInput) : $article->type;

            if ($type === null) {
                return;
            }

            // Only enforce required meta keys when meta is actually being
            // touched in this request, or the type is being changed — an
            // unrelated PATCH (e.g. just toggling is_featured) shouldn't
            // suddenly demand the full meta payload again.
            $touchingMeta = $this->has('meta') || $this->has('type');

            if ($touchingMeta) {
                $meta = (array) $this->input('meta', $article->meta ?? []);

                foreach ($type->expectedMetaKeys() as $key) {
                    if (blank(Arr::get($meta, $key))) {
                        $validator->errors()->add(
                            "meta.{$key}",
                            "The meta.{$key} field is required for the \"{$type->label()}\" article type."
                        );
                    }
                }
            }

            $status = $this->input('status') ?? $article->status?->value;

            if ($status === PublishStatus::Scheduled->value
                && blank($this->input('scheduled_at') ?? $article->scheduled_at)) {
                $validator->errors()->add('scheduled_at', 'A scheduled_at date is required when status is "scheduled".');
            }
        });
    }
}
