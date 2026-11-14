<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un JWT sigue siendo criptográficamente válido hasta que expira. Sin este
 * middleware, una cuenta bloqueada por un administrador podría seguir
 * usando el token que ya tenía; con él, el bloqueo aplica en la siguiente
 * petición.
 */
class EnsureCuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->estado !== 'activo') {
            return response()->json(['mensaje' => 'Tu cuenta está bloqueada. Contacta a un administrador.'], 401);
        }

        return $next($request);
    }
}
