<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Post\PostRequest;
use App\Http\Requests\Post\PostUpdatePostRequect;
use App\Http\Resources\Post\MinifyPostResource;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Services\Post\DTO\CreatePostData;
use App\Services\Post\DTO\UpdatePostData;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class PostController extends Controller
{
    public function __construct(private readonly PostService $service) {}

    public function index(): AnonymousResourceCollection
    {
        $posts = $this->service->getAllPosts();

        return MinifyPostResource::collection($posts);
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post);
    }

    public function update(PostUpdatePostRequect $request, Post $post): PostResource
    {
        $validated = $request->validated();
        $dto = new UpdatePostData(
            category_id: (int) $validated['category_id'],
            title: $validated['title'],
            body: $validated['body'],
            thumbnail: $request->file('thumbnail'),
            status: $validated['status'],
            views: (int) ($validated['views'] ?? $post->views),
        );
        $UpdatedPost = $this->service->update($dto, $post);

        return new PostResource($UpdatedPost);
    }

    public function store(PostRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new CreatePostData(
            category_id: (int) $validated['category_id'],
            title: $validated['title'],
            body: $validated['body'],
            thumbnail: $request->file('thumbnail'),
            status: $validated['status'],
            views: (int) ($validated['views'] ?? null),
            user_id: (int) $request->user()->id,
        );

        $post = $this->service->store($dto, $request->user());

        return response()->json([
            'message' => __('posts.created'),
            'postId' => $post->id,
            'savedFiles' => $post->thumbnail ? [$post->thumbnail] : [],
        ], Response::HTTP_CREATED);
    }

    public function destroy(Post $post): JsonResponse
    {
        if ($this->service->deletePost($post)) {
            return response()->json([
                'message' => __('posts.deleted'),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('messages.not_deleted'),
        ], Response::HTTP_BAD_REQUEST);
    }

    public function comment(Request $request, Post $post): JsonResponse
    {
        // DTO можно не создавать, так как тут всего одно поле
        $UpdatedPost = $this->service->createComment($post, $request);

        return response()->json([
            'message' => __('posts.created'),
            'postId' => $UpdatedPost->id,
            'savedFiles' => $UpdatedPost->thumbnail ? [$UpdatedPost->thumbnail] : [],
        ], Response::HTTP_CREATED);
    }
}
