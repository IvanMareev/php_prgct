<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Product\StoreReviewRequest;
use App\Http\Requests\Product\StoreProductRequest;

class ProductController extends Controller
{
    public function __construct()
    {
        $admin = User::query()->inRandomOrder()->whereIsAdmin()->first();
    
        // Проверяем, найден ли админ
        if ($admin) {
            auth()->login($admin);
        }
    }

    public function index() {
        $products = Product::query()
            ->select(['id', 'name','price'])
            ->whereStatus(ProductStatus::PUBLISHED)
            ->get();

        return $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'rating' => $product->rating(),
        ]);
    }

    public function show(Product $product)
    {
        if ($product->status === ProductStatus::DRAFT) {
            return response()->json([
                'message' => 'This product is currently out of stock.',
            ], 404);
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'rating' => $product->rating(),
            'description' => $product->description,
            'images' => $product->images->map(fn (ProductImage $image) => $image->url),
            'count' => $product->count,
            'reviews' => $product->reviews->map(fn(ProductReview $review) => [
                'id' => $review->id,
                'userName' => $review->user->name,
                'text' => $review->text,
                'rating' => $review->rating
            ]),
        ];
    }

    public function store(StoreProductRequest $request)
    {


        $product = auth()->user()->products()->create([
            'name' => $request->string('name'),
            'description' => $request->string('description'),
            'price' => $request->input('price'),
            'count' => $request->integer('count'),
            'status' => $request->enum('status', ProductStatus::class),
        ]);

        $savedFiles = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('images', 'public');

                if ($path) {
                    $product->images()->create([
                        'url' => config('app.url') . Storage::url($path)
                    ]);
                    $savedFiles[] = Storage::url($path);
                }
            }
        } else {
            //dd($request);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'id' => $product->id,
            'saved_files' => $savedFiles,
        ], 201);
    }


    public function review(StoreReviewRequest $request, Product $product)
    {
        return $product->reviews()->create([
            'user_id' => auth()->id(),
            'text' => $request->string('text'),
            'rating' => $request->integer('rating'),
        ])->only('text', 'rating');
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->only([
            'name',
            'description',
            'price',
            'count',
            'status',
        ]));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }

    
}
