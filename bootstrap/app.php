<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\ApiExceptionHandler;
use App\Providers\RouteServiceProvider;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\CheckUserType;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            ForceJsonResponse::class,
            SetLocale::class,
            // CheckUserType::class,
        ]);
        $middleware->alias([
            'check.user.type' => CheckUserType::class
        ]);

        $middleware->group('api', [
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $handler = new ApiExceptionHandler();
        
        $exceptions->render(function (\Throwable $e, $request) use ($handler) {
            return $handler->render($e, $request);
        });
    })->create();
