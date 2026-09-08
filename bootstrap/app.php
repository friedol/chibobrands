<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth'    => \App\Http\Middleware\AdminAuth::class,
            'admin'         => \App\Http\Middleware\AdminMiddleware::class,
            'admin.role'    => \App\Http\Middleware\AdminRole::class,
            'hikvision.auth' => \App\Http\Middleware\HikvisionTokenAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('customers:update-follow-ups')->dailyAt('00:01');
    })->create();
