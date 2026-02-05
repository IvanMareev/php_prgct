<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Adapters\SendNotifyTelegramAdapter;
use App\Exceptions\Product\ProductNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
        $isIgnored = $this->isIgnoredException($e);

        if (! $isIgnored) {
            try {
                $this->sendExceptionViaAdapter($e);
            } catch (Throwable $_) {
                // silently ignore adapter errors
            }
        }

        return parent::render($request, $e);
    }

    /**
     * Report the exception to the application logs and Telegram.
     */
    public function report(Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * Отправить ошибку в Telegram
     */
    private function sendExceptionViaAdapter(Throwable $e): void
    {
        $environment = env('APP_ENV', 'unknown');
        $appName = env('APP_NAME', 'Laravel App');

        $timestamp = date('Y-m-d H:i:s');
        $basePath = base_path();
        $basePathLen = strlen($basePath);
        $filePath = $e->getFile() ? substr($e->getFile(), $basePathLen) : 'unknown';

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

        $trace = $this->getRelevantStackTrace($e);
        if (! empty($trace)) {
            $message .= "\n\n<b>Стек вызовов:</b>\n<pre>" . htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') . "</pre>";
        }

        /** @var SendNotifyTelegramAdapter $adapter */
        $adapter = app(SendNotifyTelegramAdapter::class);
        $adapter->notify_telegram($message, []);
    }

    /**
     * Отправить сообщение через Telegram Bot API
     */
    // sendViaTelegram removed: sending is handled by adapter

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
        return $isNotFound || $isAuth;
    }
}
