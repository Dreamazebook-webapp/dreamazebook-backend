<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 从请求中获取语言设置
        $locale = $request->header('Accept-Language') ?? $request->input('locale') ?? config('app.default');

        $supported = explode(',', config('app.supported_locales'));
        // 确保语言代码是有效的
        $locale = in_array($locale, $supported) ? $locale : config('locale.default');
        // 设置应用语言
        App::setLocale($locale);
        
        return $next($request);
    }
}