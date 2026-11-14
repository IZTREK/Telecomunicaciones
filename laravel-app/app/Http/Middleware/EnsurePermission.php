<?php

namespace App\Http\Middleware;

use App\Models\Permiso;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorización por PERMISO (no por nombre de rol). Se usa como:
 *   ->middleware('permiso:gestionar_usuarios')
 *
 * Corre después de 'auth:api', así que el JWT ya es válido. El rol se lee
 * de la base en cada petición, por lo que un cambio de rol aplica de
 * inmediato aunque el token se haya emitido antes.
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $usuario = $request->user();

        if (!$usuario || !Permiso::tiene($usuario->rol, $permiso)) {
            return response()->json(['mensaje' => 'No tienes permiso para acceder a este recurso.'], 403);
        }

        return $next($request);
    }
}
