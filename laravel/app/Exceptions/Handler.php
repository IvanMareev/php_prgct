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
                /** @var SendNotifyTelegramAdapter $adapter */
                $adapter = app(SendNotifyTelegramAdapter::class);
                $adapter->notify_exception($e);
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
    // sendExceptionViaAdapter removed; adapter handles exception formatting and sending

    /**
     * Отправить сообщение через Telegram Bot API
     */
    // sendViaTelegram removed: sending is handled by adapter

    /**
     * Получить релевантные строки стека вызовов
     */
    // stack trace helper moved to adapter

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
