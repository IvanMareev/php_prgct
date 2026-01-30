<?php

declare(strict_types=1);

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;


Route::apiResource('posts', PostController::class)
    ->only(['index', 'show'])
    ->middleware('post.published');


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts.comments', PostController::class)
        ->only('store');
});


Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('posts', PostController::class)
        ->only(['store', 'update', 'destroy']);
});
