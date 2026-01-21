<?php

namespace App\Http\Requests\Post;

use App\Enums\PostStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PostUpdatePostRequect extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['integer', 'exists:categories,id'],
            'title' => ['string', 'max:255'],
            'body' => ['string'],
            'status' => [new Enum(PostStatus::class)],
        ];
    }
}
