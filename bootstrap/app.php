<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'authorize.user' => \App\Http\Middleware\AuthorizeUserAccess::class,
            'throttle' => \App\Http\Middleware\ThrottleRequests::class,
        ]);
        
        // Apply security headers, user activity tracking and session validation to all web routes
        $middleware->web([
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\UpdateUserActivity::class,
            \App\Http\Middleware\ValidateSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
