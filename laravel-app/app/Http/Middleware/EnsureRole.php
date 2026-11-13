<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = $request->user();

        // Si no hay usuario autenticado o su rol no se encuentra en la lista permitida
        if (!$usuario || !in_array($usuario->rol, $roles)) {
            return response()->json([
                'mensaje' => 'No tienes permiso para acceder a este recurso.'
            ], 403);
        }

        return $next($request);
    }
}