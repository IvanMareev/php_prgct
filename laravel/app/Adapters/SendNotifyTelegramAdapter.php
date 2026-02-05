<?php

declare(strict_types=1);

namespace App\Adapters;
use App\Adapters\Interfaces\TelegramInterface;

final class SendNotifyTelegramAdapter implements TelegramInterface
{
    public function telegram_log(string $message, array $context = []): void
    {
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('CONTEXTIFY_TELEGRAM_CHAT_ID');

            // Проверка конфигурации
            if (empty($token) || empty($chatId)) {
                Log::warning('Telegram не настроен: отсутствует токен или Chat ID');
                return;
            }

            // Формируем текст сообщения
            $text = $message;
            if (!empty($context)) {
                $text .= "\n\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            // Отправляем напрямую через Telegram Bot API
            $url = "https://api.telegram.org/bot{$token}/sendMessage";

            $data = [
                'chat_id' => (int) $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];

            // Используем curl для асинхронной отправки (не ждём ответа)
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Быстрый timeout
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_exec($ch);
            curl_close($ch);

        } catch (\Throwable $e) {
            // Логируем ошибку, но не прерываем запрос
            \Illuminate\Support\Facades\Log::error('Ошибка отправки в Telegram', [
                'message' => $message,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notify_telegram(string $message, array $context = []): void
    {
        self::telegram_log($message, $context);
    }
}