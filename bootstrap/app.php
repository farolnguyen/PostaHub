<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            $request = app(Request::class);
            if (! $request->is('mypage') && ! $request->is('mypage/*')) {
                return;
            }

            $safeInput = $request->except([
                'password',
                'password_confirmation',
                '_token',
            ]);

            Log::channel('log_mypage')->error('Mypage exception', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'user_id' => optional($request->user('web'))->id,
                'route' => optional($request->route())->getName(),
                'path' => $request->path(),
                'method' => $request->method(),
                'input_keys' => array_keys($safeInput),
            ]);
        });
    })->create();
