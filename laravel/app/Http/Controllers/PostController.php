<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\PostRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Post\MinifyPostResource;
use App\Http\Resources\Post\PostRecource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
        $this->middleware('post.published')->only(['show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::query()
            ->select(['id', 'title', 'thumbnail', 'views', 'created_at',])
            ->get();

        return MinifyPostResource::collection($posts);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostRecource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Post $post): PostRecource
    {
        $data = $request->only([
            'category_id',
            'title',
            'body',
            'status',
            'views',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($post->thumbnail) {
                Storage::disk('public')->delete($post->thumbnail);
            }

            $data['thumbnail'] = $request
                ->file('thumbnail')
                ->store('thumbnails', 'public');
        }

        $post->update($data);

        return new PostRecource($post->fresh());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        $post = auth()->user()?->posts()->create($request->only([
            'category_id',
            'title',
            'body',
            'thumbnail',
            'status',
            'views',
        ]));

        $savedFiles = [];
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $post->thumbnail = $path;
            $post->save();
            $savedFiles[] = $path;
        }

        return response()->json([
            'message' => 'Post created successfully',
            'postId' => $post->id,
            'savedFiles' => $savedFiles,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function comment(Request $request, Post $post)
    {
        return $post->comments()->create([
            'user_id' => auth()->id(),
            'text' => $request->string('text'),
        ]);
    }
}
