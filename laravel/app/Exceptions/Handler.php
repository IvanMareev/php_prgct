<?php
declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Product\ProductNotFoundExeption;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            return responseFailed(
                transMessage('route_not_found'),
                404
            );
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            return responseFailed(
                transMessage('AuthorizationException')
            );
        });

        $this->renderable(function (ProductNotFoundExeption $e, $request) {
            return responseFailed($e->getMessage(), 404);
        });
    }
}
