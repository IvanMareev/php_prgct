<?php
declare(strict_types=1);

namespace App\Services\Telegram\UrlGenerator;

final class TelegramUrlGenerator implements TelegramUrlGeneratorInterface
{


    public function __construct(private readonly string $baseUrl)
    {

    }

    public function sendMessage(string $token): string
    {
        return sprintf('%s/bot%s/sendMessage', rtrim($this->baseUrl, '/'), $token);
    }
}
