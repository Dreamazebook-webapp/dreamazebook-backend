<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class CheckUserType
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type = null): Response
    {
        if (!auth()->check()) {
            return $this->error(
                $type === 'admin' ? __('auth.admin_login_required') : __('auth.login_required'),
                ['login_url' => $type === 'admin' ? '/admin/login' : '/login'],
                401
            );
        }

        if ($type === 'admin' && !auth()->user() instanceof \App\Models\Admin) {
            return $this->error(
                __('auth.unauthorized'),
                ['login_url' => '/admin/login'],
                403
            );
        }

        return $next($request);
    }
}
