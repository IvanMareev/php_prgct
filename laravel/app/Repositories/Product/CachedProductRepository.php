<?php
declare(strict_types=1);

namespace App\Repositories\Product;

use App\Models\Product;
use App\Models\User;
use App\Services\Product\DTO\UpdateProductData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CachedProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository)
    {
    }

    public function createProduct(User $user, array $data, array $imagePaths = []): Product
    {
        $product = $this->repository->createProduct($user, $data, $imagePaths);
        Cache::tags(['products'])->flush();
        return $product;
    }

    public function updateProduct(Product $product, UpdateProductData $data, array $imagePaths = []): Product
    {
        $updated = $this->repository->updateProduct($product, $data, $imagePaths);
        Cache::tags(['products'])->flush();
        return $updated;
    }

    public function getAllPublishedProduct(array $fields = ['*']): Collection|array
    {
        $cacheKey = 'product.published' . md5(json_encode($fields));

        return Cache::tags(['products'])->remember(
            $cacheKey,
            600,
            fn() => $this->repository->getAllPublishedProduct($fields)
        );
    }
}
