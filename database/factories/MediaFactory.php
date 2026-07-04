<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uploaded_by' => User::factory(),
            'disk' => 'public',
            'path' => 'media/'.fake()->uuid().'.jpg',
            'type' => MediaType::Image,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 2_000_000),
            'width' => 1200,
            'height' => 630,
            'alt_text' => fake()->sentence(4),
            'caption' => null,
            'metadata' => null,
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::Video,
            'mime_type' => 'video/mp4',
            'path' => 'media/'.fake()->uuid().'.mp4',
            'width' => null,
            'height' => null,
        ]);
    }
}
