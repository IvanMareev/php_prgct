<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Adapters\SendNotifyTelegramAdapter;
use App\Exceptions\Product\ProductNotFoundException;
use App\Services\DebugTelegramLogger;
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
        DebugTelegramLogger::log("=== EXCEPTION FLOW START ===");
        DebugTelegramLogger::logException($e, 'render()');
        
        // Проверяем конфигурацию на старте
        DebugTelegramLogger::logConfig();
        
        // Определяем, нужно ли отправлять в Telegram
        $isIgnored = $this->isIgnoredException($e);
        DebugTelegramLogger::logStep('render()', [
            'exception_class' => get_class($e),
            'is_ignored' => $isIgnored,
            'should_send_to_telegram' => !$isIgnored,
        ]);
        
        // Отправляем ошибку в Telegram ПЕРЕД обработкой
        if (!$isIgnored) {
            DebugTelegramLogger::logStep('Attempting sendToTelegram() from render()');
            try {
                $this->sendToTelegram($e);
                DebugTelegramLogger::logSuccess('sendToTelegram() from render()');
            } catch (Throwable $telegramException) {
                DebugTelegramLogger::logError('sendToTelegram() from render()', [
                    'error' => $telegramException->getMessage(),
                    'exception_class' => get_class($telegramException),
                ]);
            }
        } else {
            DebugTelegramLogger::logStep('Exception is ignored, skipping Telegram');
        }

        return parent::render($request, $e);
    }

    /**
     * Report the exception to the application logs and Telegram.
     */
    public function report(Throwable $e): void
    {
        DebugTelegramLogger::logException($e, 'report()');
        
        $isIgnored = $this->isIgnoredException($e);
        DebugTelegramLogger::logStep('report()', [
            'exception_class' => get_class($e),
            'is_ignored' => $isIgnored,
        ]);
        
        parent::report($e);
        
        DebugTelegramLogger::log("=== EXCEPTION FLOW END ===");
    }

    /**
     * Отправить ошибку в Telegram
     */
    private function sendToTelegram(Throwable $e): void
    {
        DebugTelegramLogger::logStep('sendToTelegram() START', [
            'exception' => get_class($e),
        ]);

        // Получаем информацию об окружении
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('CONTEXTIFY_TELEGRAM_CHAT_ID');

        DebugTelegramLogger::logStep('Checking credentials', [
            'token_exists' => !empty($token),
            'token_length' => strlen($token ?? ''),
            'chat_id_exists' => !empty($chatId),
            'chat_id' => $chatId,
        ]);

        // Проверка конфигурации
        if (empty($token) || empty($chatId)) {
            DebugTelegramLogger::logError('Missing token or chatId', [
                'token' => empty($token) ? 'EMPTY' : 'OK',
                'chatId' => empty($chatId) ? 'EMPTY' : 'OK',
            ]);
            return;
        }

        $environment = env('APP_ENV', 'unknown');
        $appName = env('APP_NAME', 'Laravel App');
        
        DebugTelegramLogger::logStep('Environment info', [
            'env' => $environment,
            'app_name' => $appName,
        ]);

        // Используем встроенные функции PHP вместо Laravel функций
        $timestamp = date('Y-m-d H:i:s');
        $basePath = base_path();
        $basePathLen = strlen($basePath);
        $filePath = substr($e->getFile(), $basePathLen);

        DebugTelegramLogger::logStep('File path processing', [
            'base_path' => $basePath,
            'base_path_len' => $basePathLen,
            'original_file' => $e->getFile(),
            'processed_file' => $filePath,
        ]);

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

        DebugTelegramLogger::logStep('Message formatted', [
            'message_length' => strlen($message),
        ]);

        // Добавляем первые несколько строк stacktrace
        $trace = $this->getRelevantStackTrace($e);
        if (!empty($trace)) {
            $message .= "\n\n<b>Стек вызовов:</b>\n<pre>" . htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') . "</pre>";
            DebugTelegramLogger::logStep('Stack trace added', [
                'trace_length' => strlen($trace),
            ]);
        }

        DebugTelegramLogger::logStep('Calling sendViaTelegram()', [
            'final_message_length' => strlen($message),
            'token_exists' => !empty($token),
            'chat_id' => $chatId,
        ]);
        
        // Отправляем через Telegram Bot API
        $this->sendViaTelegram($token, $chatId, $message);
        
        DebugTelegramLogger::logSuccess('sendToTelegram() COMPLETE');
    }

    /**
     * Отправить сообщение через Telegram Bot API
     */
    private function sendViaTelegram(string $token, string $chatId, string $message): void
    {
        DebugTelegramLogger::logStep('sendViaTelegram() START');

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        DebugTelegramLogger::logStep('Building payload', [
            'url' => $url,
            'chat_id' => $chatId,
            'message_length' => strlen($message),
        ]);

        $payload = [
            'chat_id' => (int) $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ];
        
        $data = json_encode($payload);
        
        if ($data === false) {
            DebugTelegramLogger::logError('JSON encode failed', [
                'last_json_error' => json_last_error_msg(),
            ]);
            return;
        }

        DebugTelegramLogger::logStep('JSON encoded successfully', [
            'json_length' => strlen($data),
        ]);

        // Используем curl для отправки
        $ch = curl_init($url);
        if ($ch === false) {
            DebugTelegramLogger::logError('curl_init failed');
            return;
        }

        DebugTelegramLogger::logStep('curl initialized');

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        DebugTelegramLogger::logStep('curl options set, executing...');

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        DebugTelegramLogger::logStep('curl_exec completed', [
            'http_code' => $httpCode,
            'curl_errno' => $curlErrno,
            'curl_error' => $curlError,
            'response_length' => strlen($response ?? ''),
            'response_preview' => substr($response ?? '', 0, 300),
        ]);

        if ($httpCode === 200) {
            DebugTelegramLogger::logSuccess('Message sent to Telegram', [
                'http_code' => $httpCode,
            ]);
        } else {
            DebugTelegramLogger::logError('Telegram API returned error', [
                'http_code' => $httpCode,
                'curl_error' => $curlError,
                'curl_errno' => $curlErrno,
                'response' => $response,
            ]);
        }

        DebugTelegramLogger::logSuccess('sendViaTelegram() COMPLETE');
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
        $isNotFound = $e instanceof NotFoundHttpException;
        $isAuth = $e instanceof AuthorizationException;
        
        $result = $isNotFound || $isAuth;
        
        DebugTelegramLogger::logStep('isIgnoredException check', [
            'exception_class' => get_class($e),
            'is_not_found_exception' => $isNotFound,
            'is_auth_exception' => $isAuth,
            'result_is_ignored' => $result,
        ]);
        
        return $result;
    }
}
