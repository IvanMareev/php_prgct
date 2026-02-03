<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Adapters\SendNotifyTelegramAdapter;
use App\Enums\ProductStatus;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\StoreReviewRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\MinifyProductResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\Product\DTO\CreateProductData;
use App\Services\Product\DTO\UpdateProductData;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{

    public function __construct(private readonly ProductService $productService, private readonly SendNotifyTelegramAdapter $telegramAdapter)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        $this->telegramAdapter->telegram_log('📦 Запрос списка опубликованных товаров');

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

        $dto = new CreateProductData(
            name: $request->validated('name'),
            description: $request->validated('description'),
            price: (float)$request->validated('price'),
            count: (int)$request->validated('count'),
            images: $images,
            status: ProductStatus::from($request->validated('status')),
        );

        $product = $this->productService->store($dto, $request->user());

        return new ProductResource($product);
    }


    public function review(StoreReviewRequest $request, Product $product): ProductReview
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
            return response()->json([
                'message' => __('posts.deleted'),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('messages.not_deleted')
        ], Response::HTTP_BAD_REQUEST);
    }
}
