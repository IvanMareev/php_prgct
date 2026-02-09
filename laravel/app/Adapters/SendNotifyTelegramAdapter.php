<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Adapters\Interfaces\TelegramInterface;
use App\Services\Telegram\Formatter\TelegramMessageFormatterInterface;
use App\Services\Telegram\UrlGenerator\TelegramUrlGeneratorInterface;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

final class SendNotifyTelegramAdapter implements TelegramInterface
{
    private const TRACE_LIMIT = 3;


    public function __construct(
        private readonly TelegramUrlGeneratorInterface     $urlGenerator,
        private readonly TelegramMessageFormatterInterface $messageFormatter,
    )
    {

    }


    /**
     * @throws JsonException
     */
    public function telegram_log(string $message, array $context = []): void
    {
        $token = config('telegram.bot_token');
        $chatId = config('telegram.chat_id');


        if (!$token || !$chatId) {
            return;
        }


        Http::timeout(5)
            ->connectTimeout(2)
            ->post($this->urlGenerator->sendMessage($token), [
                'chat_id' => (int)$chatId,
                'text' => $this->messageFormatter->formatText($message, $context),
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
            $this->messageFormatter->formatException($e),
        );
    }
}
