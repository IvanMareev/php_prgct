<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Adapters\SendNotifyTelegramAdapter;
use App\Exceptions\Product\ProductNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'message' => __('messages.not_deleted')
            ], Response::HTTP_NOT_FOUND);
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            return response()->json([
                'message' => transMessage('AuthorizationException')
            ], Response::HTTP_BAD_REQUEST);
        });

        $this->renderable(function (ProductNotFoundException $e, $request) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        });
    }

    /**
     * Report the exception to the application logs and Telegram.
     */
    public function report(Throwable $e): void
    {
        // Отправляем критические ошибки в Telegram
        if ($this->shouldReport($e) && !$this->isIgnoredException($e)) {
            try {
                $this->sendToTelegram($e);
            } catch (Throwable $telegramException) {
                Log::error('Ошибка при отправке в Telegram', [
                    'original_error' => $e->getMessage(),
                    'telegram_error' => $telegramException->getMessage(),
                ]);
            }
        }

        parent::report($e);
    }

    /**
     * Отправить ошибку в Telegram
     */
    private function sendToTelegram(Throwable $e): void
    {
        // Получаем информацию об окружении
        $environment = env('APP_ENV', 'unknown');
        $appName = env('APP_NAME', 'Laravel App');
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('CONTEXTIFY_TELEGRAM_CHAT_ID');

        // Проверка конфигурации
        if (empty($token) || empty($chatId)) {
            return;
        }

        // Формируем сообщение об ошибке
        $message = sprintf(
            '<b>❌ Критическая ошибка</b>\n\n' .
            '<b>Приложение:</b> %s (%s)\n' .
            '<b>Тип:</b> <code>%s</code>\n' .
            '<b>Сообщение:</b> <code>%s</code>\n' .
            '<b>Файл:</b> <code>%s:%d</code>\n' .
            '<b>Время:</b> %s',
            $appName,
            $environment,
            get_class($e),
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
            str_replace(base_path(), '', $e->getFile()),
            $e->getLine(),
            now()->format('Y-m-d H:i:s')
        );

        // Добавляем первые несколько строк stacktrace
        $trace = $this->getRelevantStackTrace($e);
        if (!empty($trace)) {
            $message .= "\n\n<b>Стек вызовов:</b>\n<pre>" . htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') . "</pre>";
        }

        // Отправляем через Telegram Bot API
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $data = [
            'chat_id' => (int) $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];

        // Используем curl без ожидания ответа
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);

        @curl_exec($ch);
        @curl_close($ch);
    }

    /**
     * Получить релевантные строки стека вызовов
     */
    private function getRelevantStackTrace(Throwable $e): string
    {
        $trace = array_slice($e->getTrace(), 0, 3); // Только первые 3 уровня
        $output = '';

        foreach ($trace as $index => $frame) {
            $file = str_replace(base_path(), '', $frame['file'] ?? 'unknown');
            $line = $frame['line'] ?? 0;
            $function = $frame['function'] ?? 'unknown';
            $class = $frame['class'] ?? '';

            $output .= sprintf("#%d %s:%d %s%s()\n", 
                $index,
                $file,
                $line,
                $class ? $class . '::' : '',
                $function
            );
        }

        return trim($output);
    }

    /**
     * Проверить, является ли исключение игнорируемым для Telegram
     */
    private function isIgnoredException(Throwable $e): bool
    {
        // Не отправляем 404 и другие HTTP исключения в Telegram
        return $e instanceof NotFoundHttpException ||
               $e instanceof AuthorizationException;
    }
}
