<?php

namespace Database\Factories;

use App\Enums\ArticleType;
use App\Enums\Language;
use App\Enums\PublishStatus;
use App\Enums\WorkflowStage;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'category_id' => Category::factory(),
            'author_id' => User::factory(),
            'editor_id' => null,
            'featured_media_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 999999),
            'type' => ArticleType::StandardNews,
            'status' => PublishStatus::Draft,
            'workflow_stage' => WorkflowStage::Research,
            'language' => Language::Urdu,
            'excerpt' => fake()->sentence(20),
            'body' => implode("\n\n", fake()->paragraphs(5)),
            'body_en' => null,
            'meta' => null,
            'seo_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
            'canonical_url' => null,
            'source_url' => null,
            'is_breaking' => false,
            'is_featured' => false,
            'is_ai_generated' => false,
            'published_at' => null,
            'scheduled_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Published,
            'workflow_stage' => WorkflowStage::Monitoring,
            'published_at' => now(),
        ]);
    }

    public function breaking(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ArticleType::BreakingNews,
            'is_breaking' => true,
        ]);
    }

    public function ofType(ArticleType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
