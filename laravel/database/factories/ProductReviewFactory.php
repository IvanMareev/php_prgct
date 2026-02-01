<?php

namespace Database\Factories;

use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReview>
 */
class ProductReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'text' => fake()->text(100),
            'rating' => fake()->numberBetween(1,5),
        ];
    }
}
