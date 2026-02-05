<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Jobs\SendTelegramErrorJob;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
    }

    public function report(Throwable $e): void
    {
        parent::report($e);

        SendTelegramErrorJob::dispatch(
            '🔥 Ошибка в приложении',
            [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => request()?->fullUrl(),
            ]
        );
    }
}
