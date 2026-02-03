<?php

namespace App\Adapters\Interfaces;
declare(strict_types=1);


interface TelegramInterface
{
    public function telegram_log(string $message, array $context = []): void;

    public function notify_telegram(string $message, array $context = []): void;
}