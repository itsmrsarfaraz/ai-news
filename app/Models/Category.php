<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'parent_id',
    'name',
    'slug',
    'description',
    'icon',
    'order',
    'is_active',
    'meta_title',
    'meta_description',
])]
class Category extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Articles whose *primary* category is this one.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Articles additionally tagged with this category (secondary tagging),
     * on top of whatever their primary category is.
     */
    public function taggedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function roots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('order')->orderBy('name');
    }
}
