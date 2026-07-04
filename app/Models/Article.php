<?php

namespace App\Models;

use App\Enums\ArticleType;
use App\Enums\Language;
use App\Enums\PublishStatus;
use App\Enums\WorkflowStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'category_id',
    'author_id',
    'editor_id',
    'featured_media_id',
    'title',
    'slug',
    'type',
    'status',
    'workflow_stage',
    'language',
    'excerpt',
    'body',
    'body_en',
    'meta',
    'seo_title',
    'seo_description',
    'seo_keywords',
    'canonical_url',
    'source_url',
    'is_breaking',
    'is_featured',
    'is_ai_generated',
    'published_at',
    'scheduled_at',
])]
class Article extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ArticleType::class,
            'status' => PublishStatus::class,
            'workflow_stage' => WorkflowStage::class,
            'language' => Language::class,
            'meta' => 'array',
            'seo_keywords' => 'array',
            'is_breaking' => 'boolean',
            'is_featured' => 'boolean',
            'is_ai_generated' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    /**
     * The article's primary category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Additional categories this article is tagged in, beyond its
     * primary category. Use `allCategories()` to get the full set.
     */
    public function additionalCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'article_media')
            ->withPivot(['role', 'order', 'caption_override'])
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * The primary category plus any additional tagged categories,
     * deduplicated by id.
     *
     * @return \Illuminate\Support\Collection<int, Category>
     */
    public function allCategories(): \Illuminate\Support\Collection
    {
        return collect([$this->category])
            ->filter()
            ->merge($this->additionalCategories)
            ->unique('id')
            ->values();
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PublishStatus::Published)
            ->where('published_at', '<=', Carbon::now());
    }

    #[Scope]
    protected function breaking(Builder $query): void
    {
        $query->where('is_breaking', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    #[Scope]
    protected function ofType(Builder $query, ArticleType $type): void
    {
        $query->where('type', $type);
    }

    #[Scope]
    protected function inCategory(Builder $query, int $categoryId): void
    {
        $query->where(function (Builder $query) use ($categoryId) {
            $query->where('category_id', $categoryId)
                ->orWhereHas('additionalCategories', function (Builder $query) use ($categoryId) {
                    $query->where('categories.id', $categoryId);
                });
        });
    }
}
