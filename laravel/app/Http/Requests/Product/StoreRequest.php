<?php
declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductStatus;
use App\Http\Requests\ApiRequest;
use App\Services\Product\DTO\CreateProductData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Enum;

class StoreRequest extends ApiRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'count' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['required', new Enum(ProductStatus::class)],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }


    public function data(): CreateProductData
    {
        return CreateProductData::from($this->validated());
    }
}
