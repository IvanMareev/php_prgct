<?php

declare(strict_types=1);

namespace App\routes\groups;

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
});
