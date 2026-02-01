<?php

declare(strict_types=1);
namespace App\Http\Resources\Post;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 * **/
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'userName' => $this->user->name,
            'text' => $this->text,
        ];
    }
}
