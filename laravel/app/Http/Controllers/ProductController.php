<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\StoreReviewRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\MinifyProductResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\Product\DTO\CreateProductData;
use App\Services\Product\DTO\UpdateProductData;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('product.draft')->only(['show']);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index(): AnonymousResourceCollection
    {
        return MinifyProductResource::collection(
            $this->productService->published()
        );
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function store(StoreRequest $request): ProductResource
    {
        $images = $request->file('images');

        if ($images && !is_array($images)) {
            $images = [$images];
        }

        $dto = new CreateProductData(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (float)$request->validated('price'),
            count: (int)$request->validated('count'),
            images: $images,
            status: ProductStatus::from($request->validated('status')),
        );

        $product = $this->productService->store($dto);

        return new ProductResource($product);
    }


    public function review(StoreReviewRequest $request, Product $product)
    {
        return $this->productService->setProduct($product)->addReview($request);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $dto = new UpdateProductData(
            name: $request->validated('name'),
            price: $request->validated('price'),
            description: $request->validated('description'),
            images: $request->file('images'),
        );

        $product = $this->productService->setProduct($product)->update($dto);

        return new ProductResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($this->productService->deleteProduct($product)) {
            return resOk();
        } else {
            return responseFailed("Не удалось удалить продукт");
        }
    }
}
