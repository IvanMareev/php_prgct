<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class JsonExceptionHandler extends ExceptionHandler
{
    /**
     * Формат ответа для всех исключений
     */
    public function render($request, Throwable $e): Response
    {
        // Всегда возвращаем JSON для API запросов
        if ($request->expectsJson() || $this->isApiRequest($request)) {
            return $this->handleJsonException($e);
        }

        // Для web-запросов оставляем стандартное поведение
        return parent::render($request, $e);
    }

    /**
     * Обработка исключений для JSON API
     */
    protected function handleJsonException(Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);
        $response = [
            'success' => false,
            'message' => $this->getErrorMessage($e),
            'error_code' => $this->getErrorCode($e),
            'timestamp' => now()->toISOString(),
        ];

        // Добавляем детали ошибки только в debug режиме
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $this->getSafeTrace($e),
            ];
        }

        // Для валидации добавляем ошибки полей
        if ($e instanceof ValidationException) {
            $response['errors'] = $e->errors();
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Определяем код статуса HTTP
     */
    protected function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof HttpException => $e->getStatusCode(),
            $e instanceof AuthenticationException => 401,
            $e instanceof ModelNotFoundException => 404,
            $e instanceof NotFoundHttpException => 404,
            $e instanceof MethodNotAllowedHttpException => 405,
            $e instanceof ValidationException => 422,
            $e instanceof QueryException => 500,
            default => 500
        };
    }

    /**
     * Получаем понятное сообщение об ошибке
     */
    protected function getErrorMessage(Throwable $e): string
    {
        return match (true) {
            $e instanceof ModelNotFoundException => 'Запрашиваемый ресурс не найден.',
            $e instanceof NotFoundHttpException => 'Конечная точка API не найдена.',
            $e instanceof MethodNotAllowedHttpException => 'Метод не разрешен для этого эндпоинта.',
            $e instanceof AuthenticationException => 'Требуется аутентификация.',
            $e instanceof ValidationException => 'Ошибка валидации данных.',
            $e instanceof QueryException => 'Ошибка базы данных.',
            default => $e->getMessage() ?: 'Внутренняя ошибка сервера.'
        };
    }

    /**
     * Получаем код ошибки для клиента
     */
    protected function getErrorCode(Throwable $e): string
    {
        return match (true) {
            $e instanceof ModelNotFoundException => 'RESOURCE_NOT_FOUND',
            $e instanceof NotFoundHttpException => 'ENDPOINT_NOT_FOUND',
            $e instanceof MethodNotAllowedHttpException => 'METHOD_NOT_ALLOWED',
            $e instanceof AuthenticationException => 'UNAUTHENTICATED',
            $e instanceof ValidationException => 'VALIDATION_ERROR',
            $e instanceof QueryException => 'DATABASE_ERROR',
            default => 'SERVER_ERROR'
        };
    }

    /**
     * Безопасный стектрейс (без чувствительных данных)
     */
    protected function getSafeTrace(Throwable $e): array
    {
        $trace = $e->getTrace();
        
        // Очищаем trace от чувствительных данных
        foreach ($trace as &$item) {
            unset($item['args'], $item['object']);
        }
        
        return array_slice($trace, 0, 5); // Только первые 5 уровней
    }

    /**
     * Проверяем, является ли запрос API запросом
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || 
               $request->is('*/api/*') || 
               $request->header('Accept') === 'application/json';
    }
}