<?php


use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;


require __DIR__ . '/groups/user.php';
require __DIR__ . '/groups/products.php';


//Route::controller(ProductController::class)
//    ->prefix('products')
//    ->group(function () {
//        Route::get('/', 'index')->name('products.index');
//        Route::get('/{product}', 'show')->name('products.show');
//        Route::post('/', 'store')->name('products.store');
//        Route::post('/{product}/review', 'review')->name('products.review.store');
//        Route::put('/{product}', 'update')->name('products.update');
//        Route::patch('/{product}', 'update')->name('products.update');
//        Route::delete('/{product}', 'destroy')->name('products.destroy');
//    });


Route::controller(PostController::class)
    ->prefix('posts')
    ->group(function () {
        Route::get('/', 'index')->name('posts.index');
        Route::get('/{post}', 'show')->name('posts.show');
        Route::post('/', 'store')->name('posts.store');
        Route::post('/{post}/comment', 'comment')->name('posts.comment.store');
        Route::patch('/{post}', 'update')->name('posts.update');
        Route::post('/{post}', 'update')->name('posts.update');
        Route::delete('/{post}', 'destroy')->name('posts.destroy');
    });


