<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Enums\UserRole;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)
            ->has(
                Product::factory(3)
                    ->has(ProductImage::factory(rand(1, 4)), 'images')
                    ->has(
                        ProductReview::factory(rand(0, 10))->for(User::factory()),
                        'reviews'
                    )
            )
            ->create([
                'role' => Arr::random([
                    UserRole::Admin->value,
                    UserRole::User->value,
                    UserRole::Moderator->value,
                ]),
            ]);
    }
}