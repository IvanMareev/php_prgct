<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\PostRequest;
use App\Http\Requests\Post\PostUpdatePostRequect;
use App\Http\Resources\Post\MinifyPostResource;
use App\Http\Resources\Post\PostRecource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PostService;

class PostController extends Controller
{
    public PostService $service;
    public function __construct()
    {
        $this->service = new PostService();
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
        $this->middleware('post.published')->only(['show']);
    }


    public function index(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->select(['id', 'title', 'thumbnail', 'views', 'created_at',])
            ->get();

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
        return $this->service->store($request->data());
    }


    public function destroy(Post $post): JsonResponse
    {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function comment(Request $request, Post $post): Model
    {
        return $post->comments()->create([
            'user_id' => auth()->id(),
            'text' => $request->string('text'),
        ]);
    }
}
