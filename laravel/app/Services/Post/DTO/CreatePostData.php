<?php

namespace App\Services\Post\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class CreatePostData
{
    public function __construct(
        public int $category_id,
        public string $title,
        public string $body,
        public ?UploadedFile $thumbnail,
        public string $status,
        public int $views,
        public int $user_id,
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
