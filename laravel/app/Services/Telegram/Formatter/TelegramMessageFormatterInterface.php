<?php

declare(strict_types=1);

namespace App\Services\Telegram\Formatter;


use Throwable;

interface TelegramMessageFormatterInterface
{
    public function formatText(string $message, array $context = []): string;
    public function formatException(Throwable $error): string;
    function formatTrace(Throwable $error): string;
}
