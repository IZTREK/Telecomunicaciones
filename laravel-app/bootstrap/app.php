<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Laravel 11 no registra routes/api.php automáticamente como sí lo
        // hacía Laravel 10 — hay que decírselo explícitamente aquí. Esta
        // línea es el equivalente a app.MapControllers() en Program.cs.
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sin esto, un 401/403 en /api/* podría intentar redirigir a una
        // página de login HTML que no existe. Con esto, cualquier ruta que
        // empiece con /api/ siempre responde en JSON, tal como espera
        // assets/js/api.js del frontend.
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*');
        });
    })->create();
