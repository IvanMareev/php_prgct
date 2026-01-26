<?php

namespace App\Http\Controllers;

use App\Facades\ProductFacade;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\StoreReviewRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\MinifyProductResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('product.draft')->only(['show']);
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index(): AnonymousResourceCollection
    {
        return MinifyProductResource::collection(
            ProductFacade::published()
        );
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    public function store(StoreRequest $request): ProductResource
    {
        $product = ProductFacade::store($request->data());
        return new ProductResource($product);
    }


    public function review(StoreReviewRequest $request, Product $product)
    {
        return ProductFacade::setProduct($product)->addReview($request);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = ProductFacade::setProduct($product)->update($request);

        return new ProductResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        return  ProductFacade::deleteProduct($product);
    }
}
