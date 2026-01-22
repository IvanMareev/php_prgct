<?php


use App\Http\Requests\Post\PostRequest;
use App\Http\Requests\Post\PostUpdatePostRequect;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Post\PostRecource;
use App\Models\Post;
use App\Models\Product;
use App\Services\Post\DTO\CreatePostData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

final class PostService
{
    public function update(PostUpdatePostRequect $request, Post $post): PostRecource
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            if ($post->thumbnail) {
                Storage::disk('public')->delete($post->thumbnail);
            }

            $data['thumbnail'] = $request
                ->file('thumbnail')
                    ?->store('thumbnails', 'public');
        }

        $post->update($data);

        return new PostRecource($post->fresh());
    }

    public function store(CreatePostData $request): JsonResponse
    {
        $data = $request->validated();

        $post = auth()->user()?->posts()->create($data->only([
            'category_id',
            'title',
            'body',
            'thumbnail',
            'status',
            'views',
        ]));

        $savedFiles = [];
        if ($data->hasFile('thumbnail')) {
            $path = $data->file('thumbnail')?->store('thumbnails', 'public');
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
}
