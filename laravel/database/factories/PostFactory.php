<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->randomHtml(),
            'thumbnail' => fake()->imageUrl(),
            'status' => fake()->randomElement([
                PostStatus::Draft,
                PostStatus::Private,
                PostStatus::Published,
            ]),
            'views' => fake()->numberBetween(0, 1000),
        ];
    }
}
