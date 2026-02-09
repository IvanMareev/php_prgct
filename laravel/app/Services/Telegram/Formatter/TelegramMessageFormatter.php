<?php
declare(strict_types=1);

namespace App\Services\Telegram\Formatter;

use JsonException;
use Throwable;

final class TelegramMessageFormatter implements TelegramMessageFormatterInterface
{
    private const TRACE_LIMIT = 3;
    private const TELEGRAM_MAX_LENGTH = 4096;
    private const TELEGRAM_SAFE_LENGTH = 3900;

    /**
     * @throws JsonException
     */
    public function formatText(string $message, array $context = []): string
    {
        $text = $this->escape($message);

        if ($context !== []) {
            $text .= "\n<pre><code>" . $this->escape(
                    json_encode(
                        $context,
                        JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    )
                ) . '</code></pre>';
        }

        return $this->limit($text);
    }

    public function formatException(Throwable $error): string
    {
        $header = '<b>❌ Критическая ошибка</b>';

        $meta = [
            'Приложение' => sprintf('%s (%s)', config('app.name', 'Laravel App'), config('app.env', 'unknown')),
            'Тип' => get_class($error),
            'Сообщение' => $error->getMessage(),
            'Файл' => $this->relativePath($error->getFile()) . ':' . $error->getLine(),
            'Время' => now()->format('Y-m-d H:i:s'),
        ];

        $body = collect($meta)
            ->map(fn($value, $key) => sprintf('<b>%s:</b> <code>%s</code>', $key, $this->escape((string)$value)))
            ->implode("\n");

        $trace = $this->formatTrace($error);

        $message = trim(
            $header . "\n\n" . $body
            . ($trace !== ''
                ? "\n\n<b>Стек вызовов:</b>\n<pre><code>{$this->escape($trace)}</code></pre>"
                : ''
            )
        );

        return $this->limit($message);
    }

    private function formatTrace(Throwable $error): string
    {
        return collect(array_slice($error->getTrace(), 0, self::TRACE_LIMIT))
            ->map(fn(array $frame, int $i) => sprintf(
                '#%d %s:%d %s%s()',
                $i,
                $this->relativePath($frame['file'] ?? 'unknown'),
                $frame['line'] ?? 0,
                $frame['class'] ?? '',
                $frame['function'] ?? 'unknown'
            ))
            ->implode("\n");
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function relativePath(?string $path): string
    {
        if ($path === null) {
            return 'unknown';
        }

        $base = base_path();

        return str_starts_with($path, $base)
            ? substr($path, strlen($base))
            : $path;
    }

    /**
     * Ограничение длины для Telegram
     */
    private function limit(string $message): string
    {
        if (mb_strlen($message) <= self::TELEGRAM_MAX_LENGTH) {
            return $message;
        }

        return mb_substr($message, 0, self::TELEGRAM_SAFE_LENGTH) . "\n…";
    }
}
