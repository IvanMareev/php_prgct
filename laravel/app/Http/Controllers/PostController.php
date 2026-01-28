<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\PostRequest;
use App\Http\Requests\Post\PostUpdatePostRequect;
use App\Http\Resources\Post\MinifyPostResource;
use App\Http\Resources\Post\PostRecource;
use App\Models\Post;
use App\Services\Post\DTO\CreatePostData;
use App\Services\Post\PostService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function __construct(private readonly PostService $service)
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
        $this->middleware('post.published')->only(['show']);
    }


    public function index(): AnonymousResourceCollection
    {
        $posts = $this->service->getAllPosts();

        return MinifyPostResource::collection($posts);
    }


    public function show(Post $post): PostRecource
    {
        return new PostRecource($post);
    }


    public function update(PostUpdatePostRequect $request, Post $post): PostRecource
    {
        return $this->service->update($request, $post);
    }


    public function store(PostRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = new CreatePostData(
            category_id: (int)$validated['category_id'],
            title: $validated['title'],
            body: $validated['body'],
            thumbnail: $request->file('thumbnail'),
            status: $validated['status'],
            views: (int)($validated['views'] ?? 0),
            user_id: (int)$request->user()->id,
        );

        $post = $this->service->store($dto);

        return response()->json([
            'message' => 'Post created successfully',
            'postId' => $post->id,
            'savedFiles' => $post->thumbnail ? [$post->thumbnail] : [],
        ], 201);
    }


    public function destroy(Post $post): JsonResponse
    {
        return $this->service->deletePost($post);
    }

    public function comment(Request $request, Post $post): Model
    {
        return $this->service->createComment($post, $request);
    }
}
