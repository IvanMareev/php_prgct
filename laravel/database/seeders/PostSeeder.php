<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory(3)
            ->has(
                Post::factory(5)
                    ->for(
                        User::factory()
                            ->create(['role' => UserRole::Moderator])
                    )
                    ->has(
                        Comment::factory(5)
                            ->for(
                                User::factory()
                                    ->create(['role' => UserRole::User])
                            )
                    )
            )
            ->create();
    }
}
