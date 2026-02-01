<?php

declare(strict_types=1);
namespace App\Http\Requests\Product;

use App\Http\Requests\ApiRequest;

class StoreReviewRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5']
        ];
    }
}
