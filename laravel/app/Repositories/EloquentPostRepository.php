<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function getAll(array $fields = ['*']): Collection
    {
        return Post::select($fields)->get();
    }

    public function findById(int $id): ?Post
    {
        return Post::find($id);
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): bool
    {
        return $post->update($data);
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function createForUser(int $userId, array $data): ?Post
    {
        $user = User::find($userId);
        if ($user) {
            return $user->posts()->create($data);
        }
        return null;
    }

    public function createComment(Post $post,int $user_id, string $text): Post
    {
        return $post->comments()->create([
            'user_id' => $user_id,
            'text' => $text,
        ]);
    }
}