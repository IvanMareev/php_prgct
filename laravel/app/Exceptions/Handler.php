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
                'message' => 'Unauthorized'
            ], Response::HTTP_BAD_REQUEST);
        });

        $this->renderable(function (ProductNotFoundException $e, $request) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Отправляем ошибку в Telegram ПЕРЕД обработкой
        error_log("Handler::render() вызван для: " . get_class($e));
        
        if (!$this->isIgnoredException($e)) {
            try {
                $this->sendToTelegram($e);
                error_log("render() sendToTelegram успешен");
            } catch (Throwable $telegramException) {
                error_log("render() Ошибка sendToTelegram: " . $telegramException->getMessage());
            }
        }

        return parent::render($request, $e);
    }

    /**
     * Report the exception to the application logs and Telegram.
     */
    public function report(Throwable $e): void
    {
        // Логируем для отладки (удалить позже)
        error_log("Handler::report() вызван для: " . get_class($e) . " - " . $e->getMessage());
        
        // Отправляем все критические ошибки в Telegram
        // shouldReport проверяет только исключения - для Error может вернуть false
        // поэтому проверяем тип напрямую
        $shouldSendToTelegram = !$this->isIgnoredException($e);
        
        error_log("shouldSendToTelegram: " . ($shouldSendToTelegram ? 'true' : 'false'));
        
        if ($shouldSendToTelegram) {
            try {
                $this->sendToTelegram($e);
                error_log("sendToTelegram успешен");
            } catch (Throwable $telegramException) {
                // Молча игнорируем ошибки Telegram отправки
                error_log("Ошибка sendToTelegram: " . $telegramException->getMessage());
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
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('CONTEXTIFY_TELEGRAM_CHAT_ID');

        // Проверка конфигурации
        if (empty($token) || empty($chatId)) {
            error_log("sendToTelegram: Нет токена или chatId");
            return;
        }

        $environment = env('APP_ENV', 'unknown');
        $appName = env('APP_NAME', 'Laravel App');
        
        // Используем встроенные функции PHP вместо Laravel функций
        $timestamp = date('Y-m-d H:i:s');
        $basePath = base_path();
        $basePathLen = strlen($basePath);
        $filePath = substr($e->getFile(), $basePathLen);

        // Формируем сообщение об ошибке
        $message = sprintf(
            '<b>❌ Критическая ошибка</b>\n\n' .
            '<b>Приложение:</b> %s (%s)\n' .
            '<b>Тип:</b> <code>%s</code>\n' .
            '<b>Сообщение:</b> <code>%s</code>\n' .
            '<b>Файл:</b> <code>%s:%d</code>\n' .
            '<b>Время:</b> %s',
            htmlspecialchars($appName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($environment, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8'),
            $e->getLine(),
            $timestamp
        );

        // Добавляем первые несколько строк stacktrace
        $trace = $this->getRelevantStackTrace($e);
        if (!empty($trace)) {
            $message .= "\n\n<b>Стек вызовов:</b>\n<pre>" . htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') . "</pre>";
        }

        error_log("sendToTelegram: Готово к отправке сообщения: " . strlen($message) . " символов");
        
        // Отправляем через Telegram Bot API
        $this->sendViaTelegram($token, $chatId, $message);
    }

    /**
     * Отправить сообщение через Telegram Bot API
     */
    private function sendViaTelegram(string $token, string $chatId, string $message): void
    {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => (int) $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];
        
        $data = json_encode($payload);
        
        if ($data === false) {
            error_log("sendViaTelegram: JSON encode failed");
            return;
        }

        error_log("sendViaTelegram: Отправка на $url");
        
        // Используем curl для отправки
        $ch = curl_init($url);
        if ($ch === false) {
            error_log("sendViaTelegram: curl_init failed");
            return;
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        error_log("sendViaTelegram: HTTP Code: $httpCode, Response: " . substr($response, 0, 200));
        if (!empty($curlError)) {
            error_log("sendViaTelegram: Curl Error: $curlError");
        }
    }

    /**
     * Получить релевантные строки стека вызовов
     */
    private function getRelevantStackTrace(Throwable $e): string
    {
        $trace = array_slice($e->getTrace(), 0, 3); // Только первые 3 уровня
        $output = '';
        $basePathLen = strlen(base_path() ?? '');

        foreach ($trace as $index => $frame) {
            $frameFile = $frame['file'] ?? 'unknown';
            $file = $basePathLen > 0 ? substr($frameFile, $basePathLen) : $frameFile;
            $line = $frame['line'] ?? 0;
            $function = $frame['function'] ?? 'unknown';
            $class = $frame['class'] ?? '';

            $output .= sprintf("#%d %s:%d %s%s()\n", 
                $index,
                htmlspecialchars($file, ENT_QUOTES, 'UTF-8'),
                $line,
                $class ? htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '::' : '',
                htmlspecialchars($function, ENT_QUOTES, 'UTF-8')
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
