<?php

declare(strict_types=1);

namespace App\Repositories\Product;
use App\Models\Product;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\ProductStatus;
use Illuminate\Support\Facades\Storage;
use App\Services\Product\DTO\UpdateProductData;


final class EloquentProductRepository implements ProductRepositoryInterface
{

    public function getAllPublishedProduct(array $fields = ['*']): Collection|array
    {
        return Product::query()
            ->select($fields)
            ->whereStatus(ProductStatus::PUBLISHED)
            ->get();
    }

    public function createProduct(array $data, array $imagePaths = []): Product
    {
        $product = auth()->user()?->products()->create($data);

        foreach ($imagePaths as $path) {
            $product->images()->create([
                'url' => config('app.url') . Storage::url($path) 
            ]);
        }

        return $product;
    }

    public function updateProduct(Product $product, UpdateProductData $data, array $imagePaths = []): Product
    {
        $product->update($data->except('images')->toArray());

        foreach ($imagePaths as $path) {
            $product->images()->create([
                'url' => config('app.url') . Storage::url($path)
            ]);
        }

        return $product;
    }
}