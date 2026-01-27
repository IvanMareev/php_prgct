<?php

declare(strict_types=1);

namespace App\Repositories\Product;

use App\Models\Product;
use App\Services\Product\DTO\UpdateProductData;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function getAllPublishedProduct(array $fields = ['*']): Collection|array;

    public function createProduct(array $data, array $imagePaths = []): Product;

    public function updateProduct(Product $product, UpdateProductData $data, array $imagePaths = []): Product;
}