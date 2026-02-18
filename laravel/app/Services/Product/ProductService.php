<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Http\Requests\Product\StoreReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Services\Product\DTO\CreateProductData;
use App\Services\Product\DTO\UpdateProductData;
use App\Services\UploadFiles\FileUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

final class ProductService
{
    private $product;

    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function published(array $fields = ['id', 'name', 'price']): Collection|array
    {
        return $this->productRepository->getAllPublishedProduct($fields);
    }

    public function store(CreateProductData $data, User $user): Product
    {
        $imagePaths = [];

        if ($data->images()) {
            $imagePaths = $this->fileUploadService->uploadMultipleFiles(
                $data->images(),
                config('uploads.products.disk'),
                config('uploads.products.images_dir')
            );
        }

        return $this->productRepository->createProduct(
            $user,
            $data->toArray(),
            $imagePaths
        );
    }

    public function update(UpdateProductData $data): Product
    {
        $images = Arr::get($data->toArray(), 'images');
        $paths = [];

        if ($images) {
            $paths = $this->fileUploadService->uploadMultipleFiles(
                $images,
                config('uploads.products.disk'),
                config('uploads.products.images_dir')
            );
        }

        return $this->productRepository->updateProduct($this->product, $data, $paths);
    }

    public function setProduct(Product $product): ProductService
    {
        $this->product = $product;

        return $this;
    }

    public function addReview(StoreReviewRequest $request): ProductReview
    {
        return $this->product->reviews()->create([
            'user_id' => $request->user->id(),
            'text' => $request->string('text'),
            'rating' => $request->integer('rating'),
        ]);
    }

    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }
}
