<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function getAll(array $fields = ['*']): Collection;
    public function findById(int $id): ?Post;
    public function create(array $data): Post;
    public function update(Post $post, array $data): bool;
    public function delete(Post $post): bool;
    public function createForUser(int $userId, array $data): ?Post;
    public function createComment(Post $post, int $user_id, string $text): Post;
}