<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/rfid/*',
            'api/rfid',
            'api/assistant/*',
            'api/assistant',
            'assistant/chat',
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: function () {
                $user = auth()->user();
                if ($user && $user->is_super_admin) {
                    return route('superadmin.dashboard');
                }
                return route('dashboard');
            }
        );

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'superadmin' => SuperAdminMiddleware::class,
            'device.token' => \App\Http\Middleware\VerifyDeviceToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();