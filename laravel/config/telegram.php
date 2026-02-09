<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_id' => env('CONTEXTIFY_TELEGRAM_CHAT_ID'),
    'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org'),
];
