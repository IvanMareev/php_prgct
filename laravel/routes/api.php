<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/products', function () {

});


Route::controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::get('/', 'index')->name('products.index');
        Route::get('/{product}', 'show')->name('products.show');
        Route::post('/', 'store')->name('products.store');
        Route::post('/{product}/review', 'review')->name('products.review.store');
        Route::put('/{product}', 'update')->name('products.update');
        Route::patch('/{product}', 'update')->name('products.update');
        Route::delete('/{product}', 'destroy')->name('products.destroy');
    });

Route::controller(PostController::class)
    ->prefix('posts')
    ->group(function () {
        Route::get('/', 'index')->name('posts.index');
        Route::get('/{post}', 'show')->name('posts.show');
        Route::post('/', 'store')->name('posts.store');
        Route::post('/{post}/comment', 'comment')->name('posts.comment.store');
        Route::patch('/{post}', 'update')->name('posts.update');
        Route::put('/{post}', 'update')->name('posts.update');
    });
