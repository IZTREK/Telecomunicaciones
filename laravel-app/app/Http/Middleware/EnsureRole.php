<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Equivalente a [Authorize(Roles = "...")] en ASP.NET Core.
 * Se usa como: ->middleware(EnsureRole::class . ':administrador')
 * o con varios roles separados por coma: ':administrador,soporte'
 *
 * Se aplica DESPUÉS de 'auth:api' en la ruta, así que cuando este
 * middleware corre ya sabemos que el JWT es válido; aquí solo se revisa
 * si el rol de ese usuario está en la lista permitida.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $rolesPermitidos): Response
    {
        $roles = array_map('trim', explode(',', $rolesPermitidos));
        $usuario = $request->user();

        if (!$usuario || !in_array($usuario->rol, $roles, true)) {
            return response()->json(['mensaje' => 'No tienes permiso para acceder a este recurso.'], 403);
        }

        return $next($request);
    }
}
