<?php

use App\Http\Middleware\CheckDriverStatus;
use App\Http\Middleware\CheckMerchantStatus;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsVaIssuer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'active' => CheckUserStatus::class,
            'driver_active' => CheckDriverStatus::class,
            'merchant_active' => CheckMerchantStatus::class,


            'is_admin' => IsAdmin::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
