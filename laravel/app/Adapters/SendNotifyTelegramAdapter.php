<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Adapters\Interfaces\TelegramInterface;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

final class SendNotifyTelegramAdapter implements TelegramInterface
{
    private const TRACE_LIMIT = 3;

    /**
     * @throws JsonException
     */
    public function telegram_log(string $message, array $context = []): void
    {
        $token  = config('telegram.bot_token');
        $chatId = config('telegram.chat_id');


        if (!$token || !$chatId) {
            return;
        }

        if ($context !== []) {
            $message .= "<pre>" . $this->escape(
                    json_encode($context, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ) . '</pre>';
        }

        Http::timeout(5)
            ->connectTimeout(2)
            ->post("https://api.telegram.org/bot$token/sendMessage", [
                'chat_id' => (int)$chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
    }

    /**
     * @throws JsonException
     */
    public function notify_telegram(string $message, array $context = []): void
    {
        $this->telegram_log($message, $context);
    }

    /**
     * @throws JsonException
     */
    public function notify_exception(Throwable $e): void
    {
        $this->telegram_log(
            $this->formatExceptionMessage($e)
        );
    }

    private function formatExceptionMessage(Throwable $e): string
    {
        $header = '<b>❌ Критическая ошибка</b>';

        $meta = [
            'Приложение' => sprintf(
                '%s (%s)',
                config('app.name', 'Laravel App'),
                config('app.env', 'unknown')
            ),
            'Тип' => get_class($e),
            'Сообщение' => $e->getMessage(),
            'Файл' => $this->relativePath($e->getFile()) . ':' . $e->getLine(),
            'Время' => now()->format('Y-m-d H:i:s'),
        ];

        $body = collect($meta)
            ->map(fn($value, $key) => sprintf('<b>%s:</b> <code>%s</code>', $key, $this->escape($value))
            )
            ->implode("\n");

        $trace = $this->formatTrace($e);

        return trim(
            $header . "\n\n" .
            $body .
            ($trace ? "\n\n<b>Стек вызовов:</b>\n<pre>$trace</pre>" : '')
        );
    }

    private function formatTrace(Throwable $e): string
    {
        return collect(array_slice($e->getTrace(), 0, self::TRACE_LIMIT))
            ->map(function (array $frame, int $i) {
                return sprintf(
                    '#%d %s:%d %s%s()',
                    $i,
                    $this->relativePath($frame['file'] ?? 'unknown'),
                    $frame['line'] ?? 0,
                    $frame['class'] ?? '',
                    $frame['function'] ?? 'unknown'
                );
            })
            ->map($this->escape(...))
            ->implode("\n");
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function relativePath(?string $path): string
    {
        if (!$path) {
            return 'unknown';
        }

        $base = base_path();

        return str_starts_with($path, $base)
            ? substr($path, strlen($base))
            : $path;
    }
}
