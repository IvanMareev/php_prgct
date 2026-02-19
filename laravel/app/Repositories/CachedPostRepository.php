<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CachedPostRepository implements PostRepositoryInterface
{
    public function __construct(private readonly PostRepositoryInterface $repository)
    {

    }

    public function getAll(array $fields = ['*']): Collection
    {
        $cacheKey = 'post' . md5(json_encode($fields));
        return Cache::tags(['posts'])->remember($cacheKey, now()->addDay(), function () use ($fields) {
            return $this->repository->getAll($fields);
        });
    }

    public function findById(int $id): ?Post
    {
        $cacheKey = 'post' . md5(json_encode($id));
        return Cache::tags(['posts'])->remember($cacheKey, 60, function () use ($id) {
            return $this->repository->findById($id);
        });
    }

    public function update(Post $post, array $data): bool
    {
        $post = $this->repository->update($post, $data);
        Cache::tags(['posts'])->flush();
        return $post;
    }

    public function delete(Post $post): bool
    {
        Cache::tags(['posts'])->flush();
        return $post->delete();
    }

    public function createForUser(int $userId, array $data): ?Post
    {
        Cache::tags(['posts'])->flush();
        $user = User::find($userId);
        if ($user) {
            return $user->posts()->create($data);
        }
        return null;
    }

    public function create(array $data): Post
    {
        $post = $this->repository->create($data);
        Cache::tags(['posts'])->flush();
        return $post;
    }

    public function createComment(Post $post, int $user_id, string $text): Post
    {
        $post = $this->repository->createComment($post, $user_id, $text);
        Cache::tags(['posts'])->flush();
        return $post;
    }
}
