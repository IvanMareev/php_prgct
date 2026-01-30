<?php

namespace App\Services\Post;


use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepositoryInterface;
use App\Services\Post\DTO\CreatePostData;
use App\Services\Post\DTO\UpdatePostData;
use App\Services\UploadFiles\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class PostService
{

    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly PostRepositoryInterface $postRepository
    ) {
    }

    public function getAllPosts($fields = ['id', 'title', 'thumbnail', 'views', 'created_at']): Collection|array
    {
        return $this->postRepository->getAll($fields);
    }


    public function update(UpdatePostData $request, Post $post): Post
    {
        $data = $request->toArray();

        $data['thumbnail'] = $this->fileUploadService->uploadFile(
            $request->thumbnail,
            'public',
            'thumbnails',
            $post->thumbnail
        );

        $this->postRepository->update($post, $data);

        return $post->fresh();
    }

    public function store(CreatePostData $data, User $user): Post
    {
        $data['thumbnail'] = $this->fileUploadService
            ->uploadFile($request->file['thumbnail'] ?? null, 'public', 'thumbnails');


        return $this->postRepository->createForUser($user->id, $data->toArray());
    }


    public function deletePost(Post $post): bool
    {
        return $this->postRepository->delete($post);
    }


    public function createComment(Post $post, Request $request): Post
    {
        return $this->postRepository->createComment(
            $post,
            $request->user->id,
            $request->input('text')
        );
    }
}
