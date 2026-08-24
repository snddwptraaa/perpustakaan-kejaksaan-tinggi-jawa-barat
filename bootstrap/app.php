<?php

use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureSuperadminRole;
use App\Http\Middleware\EnsureVisitorHasCheckedIn;
use App\Http\Middleware\SecurityHeaders;
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
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'visitor.checked' => EnsureVisitorHasCheckedIn::class,
            'admin' => EnsureAdminRole::class,
            'superadmin' => EnsureSuperadminRole::class,
        ]);

        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
