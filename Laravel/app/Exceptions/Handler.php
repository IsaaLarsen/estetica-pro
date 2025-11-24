<?php

namespace App\Exceptions;

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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Se for uma requisição API, retorne JSON
        if ($request->expectsJson()) {
            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
                'type' => get_class($exception),
                'code' => $exception->getCode(),
            ], $this->getStatusCode($exception));
        }

        $statusCode = $this->getStatusCode($exception);
        
        // Verifica se existe uma view específica para o status code
        if (view()->exists("errors.{$statusCode}")) {
            return response()->view("errors.{$statusCode}", [
                'exception' => $exception,
                'statusCode' => $statusCode,
            ], $statusCode);
        }

        // Para outros erros, usa uma página genérica
        return $this->renderGenericError($exception, $statusCode);
    }

    /**
     * Renderiza uma página de erro genérica
     */
    protected function renderGenericError(Throwable $exception, $statusCode)
    {
        $showDetails = app()->environment('local') || app()->environment('testing');
        
        return response()->view('errors.generic', [
            'exception' => $exception,
            'statusCode' => $statusCode,
            'showDetails' => $showDetails,
        ], $statusCode);
    }

    /**
     * Obtém o status code HTTP apropriado para a exceção
     */
    protected function getStatusCode(Throwable $exception)
    {
        // Mapeia exceções comuns para status codes HTTP
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return 404;
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return 401;
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return 422;
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 404;
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return 405;
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}