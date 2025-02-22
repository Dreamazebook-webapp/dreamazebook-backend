<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 强制所有请求接受JSON响应
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // 如果响应不是JSON格式，转换为JSON，仅限api路由
        if (!$this->isJsonResponse($response) && $request->is('api/*')) {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

            return $statusCode >= 400
                ? $this->error($content, null, $statusCode)
                : $this->success(['content' => $content], 'Success', $statusCode);
        }

        return $response;
    }

    protected function isJsonResponse($response): bool
    {
        return $response->headers->has('Content-Type') &&
            str_contains($response->headers->get('Content-Type'), 'application/json');
    }
}
