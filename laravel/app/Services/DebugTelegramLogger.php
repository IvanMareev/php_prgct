<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;

class DebugTelegramLogger
{
    private static string $logFile = '/var/www/storage/logs/telegram-debug.log';

    public static function log(string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] $message$contextStr\n";

        // Пишем в файл логов
        @error_log($logMessage, 3, self::$logFile);

        // Также выводим в стандартный лог PHP
        @error_log($message . $contextStr);
    }

    public static function logException(Throwable $e, string $stage): void
    {
        self::log("=== EXCEPTION AT STAGE: $stage ===", [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
        ]);
    }

    public static function logStep(string $step, array $data = []): void
    {
        self::log("📍 STEP: $step", $data);
    }

    public static function logSuccess(string $message, array $data = []): void
    {
        self::log("✅ SUCCESS: $message", $data);
    }

    public static function logError(string $message, array $data = []): void
    {
        self::log("❌ ERROR: $message", $data);
    }

    public static function logConfig(): void
    {
        self::log("=== CONFIGURATION CHECK ===", [
            'token_exist' => !empty(env('TELEGRAM_BOT_TOKEN')),
            'token_length' => strlen(env('TELEGRAM_BOT_TOKEN') ?? ''),
            'chat_id_exist' => !empty(env('CONTEXTIFY_TELEGRAM_CHAT_ID')),
            'chat_id_value' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
            'app_env' => env('APP_ENV'),
            'app_name' => env('APP_NAME'),
        ]);
    }

    public static function clearLog(): void
    {
        @file_put_contents(self::$logFile, '');
    }
}
