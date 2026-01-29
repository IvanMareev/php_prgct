<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Http\Requests\Product\StoreRequest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Repositories\Product\EloquentProductRepository;
use App\Services\Product\DTO\CreateProductData;
use App\Services\Product\DTO\UpdateProductData;
use App\Services\UploadFiles\FileUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class ProductService
{
    private Product $product;


    public function __construct(
        private readonly FileUploadService         $fileUploadService,
        private readonly EloquentProductRepository $eloquentProductRepository)
    {
    }


    public function published(array $fields = ['id', 'name', 'price']): Collection|array
    {
        return $this->eloquentProductRepository->getAllPublishedProduct($fields);
    }

    public function store(CreateProductData $data): Product
    {
        $imagePaths = [];

        if ($data->images()) {
            $imagePaths = $this->fileUploadService->uploadMultipleFiles(
                $data->images(),
                'public',
                'product_images'
            );
        }

        return $this->eloquentProductRepository->createProduct(
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
                'public',
                'product_images'
            );
        }

        return $this->eloquentProductRepository->updateProduct($this->product, $data, $paths);
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


    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }
}
