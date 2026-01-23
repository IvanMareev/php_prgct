<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;
use App\Enums\UserRole;

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
