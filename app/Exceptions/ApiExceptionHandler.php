<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiExceptionHandler
{
    use ApiResponse;

    public function render(\Throwable $e, Request $request)
    {
        // 验证异常
        if ($e instanceof ValidationException) {
            return $this->error(
                __('messages.validation_error'),
                $e->errors(),
                422
            );
        }

        // 认证异常
        if ($e instanceof AuthenticationException) {
            $isAdmin = str_starts_with($request->path(), 'api/admin');
            return $this->error(
                $isAdmin ? __('auth.admin_login_required') : __('auth.login_required'),
                ['login_url' => $isAdmin ? '/admin/login' : '/login'],
                401
            );
        }

        // 授权异常
        if ($e instanceof AuthorizationException) {
            return $this->error(
                __('auth.unauthorized'),
                null,
                403
            );
        }

        // 模型未找到异常
        if ($e instanceof ModelNotFoundException) {
            return $this->error(
                __('messages.model_not_found', ['model' => class_basename($e->getModel())]),
                null,
                404
            );
        }

        // 请求频率限制异常
        if ($e instanceof ThrottleRequestsException) {
            return $this->error(
                __('messages.too_many_requests'),
                [
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ],
                429
            );
        }

        // 方法不允许异常
        if ($e instanceof MethodNotAllowedHttpException) {
            $route = $request->path();
            $method = $request->method();
            $allowedMethods = $e->getHeaders()['Allow'] ?? '';

            return $this->error(
                __('messages.method_not_allowed', [
                    'method' => $method,
                    'route' => $route,
                    'allowed_methods' => $allowedMethods,
                ]),
                [
                    'allowed_methods' => explode(', ', $allowedMethods),
                    'current_method' => $method,
                    'route' => $route,
                ],
                405
            );
        }

        // 路由未找到异常
        if ($e instanceof NotFoundHttpException) {
            return $this->error(
                __('messages.route_not_found'),
                null,
                404
            );
        }

        // HTTP 异常
        if ($e instanceof HttpException) {
            return $this->error(
                $e->getMessage() ?: __('messages.http_error'),
                null,
                $e->getStatusCode()
            );
        }

        // 其他所有异常
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        
        // 如果是调试模式，返回详细信息
        $errors = config('app.debug') ? [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
            'trace' => $e->getTrace()
        ] : null;

        // 获取错误消息
        $message = $e->getMessage();
        if (empty($message) || $message === 'Server Error') {
            $message = __('messages.server_error');
        }

        return $this->error($message, $errors, $statusCode);
    }

    public function handle(Throwable $e)
    {
        // 记录错误日志
        Log::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        // 根据环境返回不同的错误信息
        if (config('app.debug')) {
            // 开发环境：返回详细错误信息
            return $this->error(
                $e->getMessage(),
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ],
                500
            );
        }

        // 生产环境：返回友好的错误信息
        $message = match(true) {
            $e instanceof QueryException => __('messages.database_error'),
            $e instanceof \PDOException => __('messages.database_error'),
            $e instanceof \ErrorException => __('messages.server_error'),
            default => __('messages.unknown_error')
        };

        return $this->error($message, null, 500);
    }
} 