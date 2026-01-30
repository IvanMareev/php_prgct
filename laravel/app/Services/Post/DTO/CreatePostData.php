<?php

namespace App\Services\Post\DTO;

use Illuminate\Http\UploadedFile;

class CreatePostData
{
    public function __construct(
        public readonly int $category_id,
        public readonly string $title,
        public readonly string $body,
        public readonly ?UploadedFile $thumbnail,
        public readonly string $status,
        public readonly int $views,
        public readonly int $user_id,
    ) {}

    public function toArray(): array
    {
        return [
            'category_id' => $this->category_id,
            'title'       => $this->title,
            'body'        => $this->body,
            'thumbnail'   => $this->thumbnail,
            'status'      => $this->status,
            'views'       => $this->views,
            'user_id'     => $this->user_id,
        ];
    }
}
