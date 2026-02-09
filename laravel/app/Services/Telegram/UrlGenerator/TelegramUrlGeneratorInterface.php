<?php

declare(strict_types=1);

namespace App\Services\Telegram\UrlGenerator;


interface TelegramUrlGeneratorInterface
{
    public function sendMessage(string $token): string;
}
