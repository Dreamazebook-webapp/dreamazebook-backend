<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    public function register()
    {
        parent::register();

        // 设置默认的API响应格式
        config(['app.debug_blacklist' => [
            '_ENV' => [
                'APP_KEY',
                'DB_PASSWORD',
            ],
            '_SERVER' => [
                'APP_KEY',
                'DB_PASSWORD',
            ],
            '_POST' => [
                'password',
            ],
        ]]);
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // 设置默认的请求头
        Request::macro('expectsJson', function () {
            return true;
        });

        // 配置 API 限流器
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware(['api', 'throttle:api'])
                ->group(base_path('routes/api.php'));
        });

        // 添加全局的Accept头，使所有响应默认为JSON
        Route::matched(function ($route, $request) {
            $request->headers->set('Accept', 'application/json');
        });
    }
} 