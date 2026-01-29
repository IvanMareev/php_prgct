<?php

namespace App\Services\Post;


use App\Models\Post;
use App\Repositories\PostRepositoryInterface;
use App\Services\Post\DTO\CreatePostData;
use App\Services\UploadFiles\FileUploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Services\Post\DTO\UpdatePostData;

final class PostService
{

    public function __construct(
        private FileUploadService $fileUploadService,
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function getAllPosts($fields = ['id', 'title', 'thumbnail', 'views', 'created_at']): Collection|array
    {


        return $this->postRepository->getAll($fields);
    }


    public function update(UpdatePostData $request, Post $post): static|null
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

    public function store(CreatePostData $data): Post
    {
        $data['thumbnail'] = $this->fileUploadService
            ->uploadFile($request->file['thumbnail'] ?? null, 'public', 'thumbnails');


        return $this->postRepository->createForUser(auth()->id(), $data->toArray());
    }


    public function deletePost(Post $post): bool
    {
        return $this->postRepository->delete($post);
    }


    public function createComment(Post $post, Request $request): Post
    {
        return $this->postRepository->createComment(
            $post,
            auth()->id(),
            $request->input('text')
        );
    }
}
