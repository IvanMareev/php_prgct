<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/groups/user.php';
require __DIR__ . '/groups/products.php';
require __DIR__ . '/groups/posts.php';
// Тестовые маршруты для проверки отправки ошибок в Telegram
Route::get('/test-error', function () {
    throw new \RuntimeException('Тестовая ошибка для проверки Telegram отправки');
});

Route::get('/test-null-error', function () {
    $user = null;
    return $user->getName(); // Ошибка: Call to a member function on null
});

Route::get('/test-log', function() {
    Log::error('Тестовое сообщение для ELK');
    return 'Лог записан';
});
