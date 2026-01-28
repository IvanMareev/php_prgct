<?php

namespace App\Services\Post;

use App\Http\Requests\Post\PostUpdatePostRequect;
use App\Http\Resources\Post\PostRecource;
use App\Models\Post;
use App\Repositories\PostRepositoryInterface;
use App\Services\Post\DTO\CreatePostData;
use App\Services\UploadFiles\FileUploadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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


    public function update(PostUpdatePostRequect $request, Post $post): PostRecource
    {
        $data = $request->validated();

        $data['thumbnail'] = $this->fileUploadService->uploadFile(
            $request->file('thumbnail'),
            'public',
            'thumbnails',
            $post->thumbnail
        );

        $this->postRepository->update($post, $data);
        return new PostRecource($post->fresh());
    }

    public function store(CreatePostData $data): Post
    {
        $data['thumbnail'] = $this->fileUploadService
            ->uploadFile($request->file['thumbnail'] ?? null, 'public', 'thumbnails');

//        $postData = $data->only(['category_id', 'title', 'body', 'thumbnail', 'status', 'views']);

        return $this->postRepository->createForUser(auth()->id(), $data->toArray());
    }


    public function deletePost(Post $post): JsonResponse
    {
        if ($this->postRepository->delete($post)) {
            return resOk();
        } else {
            return responseFailed("Не удалось удалить пост");
        }
    }


    public function createComment(Post $post, Request $request): Model
    {
        return $this->postRepository->createComment(
            $post,
            auth()->id(),
            $request->input('text')
        );
    }
}
