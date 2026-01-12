<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\ApiRequest;
use App\Enums\PostStatus;
use Illuminate\Validation\Rules\Enum;

class StorePostRequest extends ApiRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string:max:150',
            'thumbnail' => 'image|max',
            'body' => 'required|string',
            'status' => [new Enum(PostStatus::class)],
            'views' => 'integer|min:0',
        ];
    }
}
