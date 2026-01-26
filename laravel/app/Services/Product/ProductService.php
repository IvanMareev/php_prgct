<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Enums\ProductStatus;
use App\Http\Requests\Product\StoreRequest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\Product\DTO\CreateProductData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class ProductService
{
    private Product $product;


    public function published(array $fields = ['id', 'name', 'price']): Collection|array
    {
        return Product::query()
            ->select($fields)
            ->whereStatus(ProductStatus::PUBLISHED)
            ->get();
    }

    public function store(CreateProductData $data): Product
    {
        $images = Arr::get($data->toArray(), 'images');

        $product = auth()->user()?->products()->create([
            $data->except('images')->toArray(),
        ]);


        if ($images) {
            foreach ($data?->file('images') as $image) {
                $path = $image->store('images', 'public');

                if ($path) {
                    $product->images()->create([
                        'url' => config('app.url') . Storage::url($path)
                    ]);
                }
            }
        }

        return $product;
    }

    public function update(StoreRequest $request, Product $product): Product
    {
        if ($request->method() === 'PUT') {
            $this->product->update([
                'name' => $request->string('name'),
                'description' => $request->string('description'),
                'price' => $request->float('price'),
                'count' => $request->integer('count', 0),
                'status' => $request->enum('status', ProductStatus::class),
            ]);
        } else {
            //TODO использовать DTO
            $this->product->update([
                'name' => $request->string('name'),
                'description' => $request->string('description'),
                'price' => $request->float('price'),
                'count' => $request->integer('count', 0),
                'status' => $request->enum('status', ProductStatus::class),
            ]);
        }

        return $this->product;
    }


    public function setProduct(Product $product): ProductService
    {
        $this->product = $product;
        return $this;
    }


    public function addReview(StoreRequest $request): ProductReview
    {
        return $this->product->reviews()->create([
            'user_id' => auth()->id(),
            'text' => $request->string('text'),
            'rating' => $request->integer('rating'),
        ]);
    }


    public function deleteProduct(Product $product): JsonResponse
    {
        if ($status = $product->delete()) {
            return resOk();
        } else {
            return responseFailed("Не удалось удалить продукт");
        }
    }
}