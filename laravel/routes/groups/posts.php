<?php

namespace App\routes\groups;

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::apiResource('posts', PostController::class);


Route::post('posts/{post}/comment', [PostController::class, 'posts.comment.store']);