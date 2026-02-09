<?php

declare(strict_types=1);

namespace App\Adapters\Interfaces;


use Throwable;

interface TelegramInterface
{
    public function telegram_log(string $message, array $context = []): void;

    public function notify_telegram(string $message, array $context = []): void;

    public function notify_exception(Throwable $e): void;
}
