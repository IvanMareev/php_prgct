<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiRequest;
use App\Services\Product\DTO\CreateProductData;

class UpdateProductRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:1'],
            'count' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes'],
        ];
    }



}
