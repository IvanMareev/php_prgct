<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Post\PostRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Post\StorePostRequest;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                $admin = User::where('role', UserRole::Admin)->firstOrFail();
                auth()->login($admin);
            }

            return $next($request);
        });
    }

    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::query()->select(['id', 'title', 'thumbnail', 'views', 'created_at',])->get();

        return $posts->map(fn (Post $post) => [
            'id' => $post->id,
            'title' => $post->title,
            'thumbnail' => $post->thumbnail,
            'views' => $post->views,
            'createdAt' => $post->created_at,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
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
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'body' => $post->body,
            'thumbnail' => $post->thumbnail,
            'views' => $post->views,
            'createdAt' => $post->created_at,
            'authorName' => $post->user?->name,
            'categoryName' => $post->category?->name,
            'comments' => $post->comments->map(fn($comment) => [
                'userName' => $comment->user->name,
                'text' => $comment->text,

            ]),
        ];
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);
        $post->update($request->only([
            'category_id',
            'title',
            'body',
            'thumbnail',
            'status',
            'views',
        ]));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json([
            'message' => 'Post deleted successfully',
        ], 200);
    }


    public function comment(Request $request, Post $post)
    {
        return $post->comments()->create([
            'user_id' => auth()->id(),
            'text' => $request->string('text'),
        ]);
    }
}
