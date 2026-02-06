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
    <?php
    declare(strict_types=1);

    namespace App\Exceptions;

    use App\Jobs\SendTelegramErrorJob;
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
            return parent::render($request, $e);
        }

        /**
         * Report the exception to the application logs and send a notification.
         */
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
    {
