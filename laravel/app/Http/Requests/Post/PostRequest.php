<?php

namespace App\Http\Requests\Post;

use App\Enums\PostStatus;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rules\Enum;

class PostRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:150'],
            'body'        => ['required', 'string'],
            'thumbnail'   => ['nullable', 'image', 'max:2048'],
            'status'      => ['required', new Enum(PostStatus::class)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'views'       => ['nullable', 'integer', 'min:0'],
        ];
    }
}
