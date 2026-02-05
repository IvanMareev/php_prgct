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
                // Telegram isn't configured — silently skip
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

            // Используем curl для отправки
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // silent: do not log result

        } catch (\Throwable $e) {
            // swallow exceptions — do not log
        }
    }

    public function notify_telegram(string $message, array $context = []): void
    {
        self::telegram_log($message, $context);
    }
}