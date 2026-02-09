<?php

declare(strict_types=1);

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::post('products/{product}/reviews', [ProductController::class, 'review']);
    Route::apiResource('products', ProductController::class)
        ->only(['index', 'show']);
});


Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy']);
});
