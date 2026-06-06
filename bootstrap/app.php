<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/install', [\App\Http\Controllers\InstallController::class, 'index']);
            Route::post('/install', [\App\Http\Controllers\InstallController::class, 'run']);
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->web(prepend: [
            \App\Http\Middleware\EnsureInstalled::class,
            \App\Http\Middleware\DomainRedirect::class,
        ]);
        $middleware->alias([
            'role.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\TrackPageView::class,
            \App\Http\Middleware\Maintenance::class,
        ]);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
