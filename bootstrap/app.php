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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'operator.redirect' => \App\Http\Middleware\OperatorRedirect::class,
            // Disallow staff roles (operator, approval_officer, finance_officer) from personal bookings & tickets
            'restrict_staff_personal' => \App\Http\Middleware\RestrictStaffPersonal::class,
            'check.user.active' => \App\Http\Middleware\CheckUserActive::class,
            'profile.completed' => \App\Http\Middleware\EnsureProfileCompleted::class,
        ]);

        // Apply the active user check and profile completion check to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserActive::class,
            \App\Http\Middleware\EnsureProfileCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
