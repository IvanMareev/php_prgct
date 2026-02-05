<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/groups/user.php';
require __DIR__ . '/groups/products.php';
require __DIR__ .'/groups/posts.php';

// Тестовый маршрут для проверки отправки ошибок в Telegram
Route::get('/test-error', function () {
    throw new RuntimeException('HANDLER TEST ERROR');
});
