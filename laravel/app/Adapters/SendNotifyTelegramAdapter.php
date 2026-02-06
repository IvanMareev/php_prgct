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

    public function notify_exception(\Throwable $e): void
    {
        $environment = env('APP_ENV', 'unknown');
        $appName = env('APP_NAME', 'Laravel App');

        $timestamp = date('Y-m-d H:i:s');
        $basePath = base_path();
        $basePathLen = strlen($basePath);
        $filePath = $e->getFile() ? substr($e->getFile(), $basePathLen) : 'unknown';

        $message = sprintf(
            '<b>❌ Критическая ошибка</b>\n\n' .
            '<b>Приложение:</b> %s (%s)\n' .
            '<b>Тип:</b> <code>%s</code>\n' .
            '<b>Сообщение:</b> <code>%s</code>\n' .
            '<b>Файл:</b> <code>%s:%d</code>\n' .
            '<b>Время:</b> %s',
            htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($environment, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8'),
            $e->getLine(),
            $timestamp
        );

        $trace = $this->getRelevantStackTrace($e);
        if (! empty($trace)) {
            $message .= "\n\n<b>Стек вызовов:</b>\n<pre>" . htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') . "</pre>";
        }

        // use existing telegram_log to send
        $this->telegram_log($message, []);
    }

    private function getRelevantStackTrace(\Throwable $e): string
    {
        $trace = array_slice($e->getTrace(), 0, 3);
        $output = '';
        $basePathLen = strlen(base_path() ?? '');

        foreach ($trace as $index => $frame) {
            $frameFile = $frame['file'] ?? 'unknown';
            $file = $basePathLen > 0 ? substr($frameFile, $basePathLen) : $frameFile;
            $line = $frame['line'] ?? 0;
            $function = $frame['function'] ?? 'unknown';
            $class = $frame['class'] ?? '';

            $output .= sprintf(
                "#%d %s:%d %s%s()\n",
                $index,
                htmlspecialchars($file, ENT_QUOTES, 'UTF-8'),
                $line,
                $class ? htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '::' : '',
                htmlspecialchars($function, ENT_QUOTES, 'UTF-8')
            );
        }

        return trim($output);
    }
}