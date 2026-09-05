<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'=>RoleMiddleware::class,
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function(QueryException $e, Request $request) {
            $code = $e->errorInfo[1] ?? null;
            return match($code){
                1062 => response()->error($request->email . ' is already in use. Try Loging in instead of registering', 409),
                1048 => response()->error($e->errorInfo[2], 422),
                default => response()->error($e->errorInfo[2], 500)
            };
        });
        $exceptions->render(function (Throwable $e) {
            return response()->error($message = $e->getMessage(), $code = 500);
        });
    })->create();
