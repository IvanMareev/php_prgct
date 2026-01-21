<?php

namespace App\Http\Requests\Product;

use App\Enums\ProductStatus;
use App\Services\Product\DTO\CreateProductData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\ApiRequest;

class StoreRequest extends ApiRequest
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
            'description' => [ 'nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'count' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['required', new Enum(ProductStatus::class)],
            'images' => ['array'],
            'images.*' => ['image'],
        ];
    }


    public function data(): CreateProductData
    {
        return CreateProductData::from($this->validated());
    }
}
