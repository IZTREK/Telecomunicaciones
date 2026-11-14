<?php

use App\Http\Middleware\EnsureCuentaActiva;
use App\Http\Middleware\EnsurePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Laravel 11 no registra routes/api.php automáticamente — hay que
        // decírselo explícitamente aquí.
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Solo se confía en X-Forwarded-For cuando la petición llega desde
        // una red privada (nginx dentro de Docker, o el agente de ngrok en
        // la máquina anfitriona). Así $request->ip() es la IP real del
        // visitante y nadie puede falsificarla mandando el header a mano.
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ]);

        $middleware->alias([
            'permiso' => EnsurePermission::class,
            'cuenta.activa' => EnsureCuentaActiva::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Cualquier ruta /api/* responde siempre en JSON (nunca una página
        // HTML de error), tal como espera assets/js/api.js.
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*');
        });
    })->create();
