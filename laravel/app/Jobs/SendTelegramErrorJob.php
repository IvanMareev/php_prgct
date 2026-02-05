<?php

namespace App\Jobs;

use App\Adapters\SendNotifyTelegramAdapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramErrorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $message,
        private readonly array $context
    ) {
    }

    public function handle(SendNotifyTelegramAdapter $adapter): void
    {
        $adapter->notify_telegram($this->message, $this->context);
    }
}
