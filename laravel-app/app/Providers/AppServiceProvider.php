<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Límite de peticiones a POST /api/auth/login por dirección IP.
        // Complementa el bloqueo por cuenta de AuthController: aquél frena
        // la fuerza bruta contra UNA cuenta; éste frena a quien prueba
        // muchas cuentas distintas (o correos inexistentes) desde la misma IP.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip())->response(function () {
                return response()->json([
                    'mensaje' => 'Demasiadas solicitudes de inicio de sesión. Espera un minuto e inténtalo de nuevo.',
                ], 429);
            });
        });
    }
}
