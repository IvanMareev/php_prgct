<?php

namespace App\Http\Requests\Post;

use App\Enums\PostStatus;
use App\Http\Requests\ApiRequest;
use App\Models\Category;
use Illuminate\Validation\Rules\Enum;

class PostRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


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
            'state' => [new Enum(PostStatus::class)],
            'categoryId' => [new Enum(Category::class)]
        ];
    }
}
