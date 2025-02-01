<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 优先使用 session 中保存的语言设置
        if (session()->has('locale')) {
            $locale = session('locale');
        } 
        // 其次使用请求中的语言参数
        elseif ($request->has('language')) {
            $locale = $request->input('language');
        }
        // 最后使用浏览器首选语言
        else {
            $locale = substr($request->getPreferredLanguage(config('app.supported_locales', ['zh', 'en'])), 0, 2);
        }

        // 确保语言在支持列表中
        if (!in_array($locale, config('app.supported_locales', ['zh', 'en']))) {
            $locale = config('app.fallback_locale', 'en');
        }

        // 设置应用语言
        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
} 