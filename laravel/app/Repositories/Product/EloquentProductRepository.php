<?php

declare(strict_types=1);

namespace App\Repositories\Product;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\Product\DTO\UpdateProductData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;


final class EloquentProductRepository implements ProductRepositoryInterface
{

    public function getAllPublishedProduct(array $fields = ['*']): Collection|array
    {
        return Product::query()
            ->select($fields)
            ->whereStatus(ProductStatus::PUBLISHED)
            ->get();
    }

    public function createProduct(User $user, array $data, array $imagePaths = []): Product
    {
        $product = $user->products()->create($data);

        foreach ($imagePaths as $path) {
            $product->images()->create([
                'url' => config('app.url') . Storage::url($path)
            ]);
        }

        return $product;
    }

    public function updateProduct(
        Product           $product,
        UpdateProductData $data,
        array             $imagePaths = []
    ): Product
    {
        $product->update($data->toArray());

        foreach ($imagePaths as $path) {
            $product->images()->create([
                'url' => config('app.url') . Storage::url($path),
            ]);
        }

        return $product->fresh();
    }
}
