<?php

declare(strict_types=1);
namespace App\Http\Resources\Post;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 * **/
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'thumbnail' => $this->thumbnail,
            'views' => $this->views,
            'createdAt' => $this->created_at,
            'authorName' => $this->user?->name,
            'categoryName' => $this->category?->name,
            'comments' => CommentResource::collection($this->comments),
        ];
    }
}
