<?php

use App\Services\DebugTelegramLogger;
use Illuminate\Support\Facades\Route;

// Тестовые маршруты для проверки отправки ошибок в Telegram
Route::get('/test-error', function () {
    DebugTelegramLogger::log("=== TEST ERROR TRIGGERED ===");
    throw new \RuntimeException('Тестовая ошибка для проверки Telegram отправки');
});

Route::get('/test-null-error', function () {
    DebugTelegramLogger::log("=== TEST NULL ERROR TRIGGERED ===");
    $user = null;
    return $user->getName(); // Ошибка: Call to a member function on null
});

// Роут для просмотра логов отладки
Route::get('/telegram-debug-logs', function () {
    $logFile = storage_path('logs/telegram-debug.log');
    
    if (!file_exists($logFile)) {
        return response()->json([
            'message' => 'Log file not found',
            'path' => $logFile,
        ], 404);
    }
    
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    
    return response()->json([
        'log_file' => $logFile,
        'total_lines' => count($lines),
        'size_bytes' => strlen($logs),
        'last_50_lines' => array_slice($lines, -50),
    ]);
});

// Роут для очистки логов
Route::post('/telegram-debug-clear', function () {
    DebugTelegramLogger::clearLog();
    DebugTelegramLogger::log("=== LOGS CLEARED ===");
    
    return response()->json([
        'message' => 'Logs cleared',
        'timestamp' => date('Y-m-d H:i:s'),
    ]);
});

// Роут для проверки конфигурации
Route::get('/telegram-config-check', function () {
    DebugTelegramLogger::logConfig();
    
    return response()->json([
        'message' => 'Configuration check logged to telegram-debug.log',
        'token_exists' => !empty(env('TELEGRAM_BOT_TOKEN')),
        'token_preview' => substr(env('TELEGRAM_BOT_TOKEN') ?? '', 0, 10) . '...',
        'chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
        'app_env' => env('APP_ENV'),
        'app_name' => env('APP_NAME'),
    ]);
});

// Роут для просмотра последних 20 уникальных ошибок из логов
Route::get('/telegram-debug-summary', function () {
    $logFile = storage_path('logs/telegram-debug.log');
    
    if (!file_exists($logFile)) {
        return response()->json(['message' => 'Log file not found'], 404);
    }
    
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    
    // Найдем все "EXCEPTION AT STAGE" строки
    $exceptions = [];
    foreach ($lines as $line) {
        if (strpos($line, 'EXCEPTION AT STAGE') !== false) {
            $exceptions[] = $line;
        }
    }
    
    // Найдем все SUCCESS строки
    $successes = [];
    foreach ($lines as $line) {
        if (strpos($line, 'SUCCESS:') !== false) {
            $successes[] = $line;
        }
    }
    
    // Найдем все ERROR строки
    $errors = [];
    foreach ($lines as $line) {
        if (strpos($line, 'ERROR:') !== false) {
            $errors[] = $line;
        }
    }
    
    return response()->json([
        'total_lines' => count($lines),
        'exceptions_count' => count($exceptions),
        'success_count' => count($successes),
        'error_count' => count($errors),
        'last_exceptions' => array_slice($exceptions, -5),
        'last_successes' => array_slice($successes, -5),
        'last_errors' => array_slice($errors, -5),
    ]);
});
