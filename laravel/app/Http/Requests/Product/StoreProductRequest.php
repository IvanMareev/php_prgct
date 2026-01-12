<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => [ 'string'],
            'price' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'count' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['required', new Enum(ProductStatus::class)],
            'images*.' => ['image'],
        ];
    }
}
