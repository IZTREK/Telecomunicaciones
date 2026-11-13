<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    // Debe coincidir con el catálogo de assets/js/auth.js (MAPA_PERMISOS)
    // y con los permisos sembrados en la migración.
    private const ROLES_VALIDOS = ['cliente', 'soporte', 'administrador', 'redes', 'encargado', 'contabilidad'];

    // Cualquier usuario autenticado puede ver el directorio (se usa en el dashboard).
    public function index()
    {
        $usuarios = Usuario::orderBy('id')->get();
        return response()->json($usuarios->map->toPublicArray());
    }

    // Solo administradores (revisado por el middleware EnsureRole en la ruta).
    public function actualizarRol(Request $request, int $id)
    {
        $data = $request->validate([
            'rol' => ['required', 'string'],
        ]);

        if (!in_array($data['rol'], self::ROLES_VALIDOS, true)) {
            return response()->json(['mensaje' => 'Rol inválido.'], 400);
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        $usuario->rol = $data['rol'];
        $usuario->save();

        $adminCodigo = $request->user()->codigo_usuario ?? 'desconocido';
        Bitacora::create([
            'fecha' => now(),
            'tipo' => 'ok',
            'mensaje' => "Cambio de rol a '{$data['rol']}' aplicado por {$adminCodigo}",
            'usuario_id' => $usuario->id,
        ]);

        return response()->noContent();
    }

    // Solo administradores.
    public function actualizarEstado(Request $request, int $id)
    {
        $data = $request->validate([
            'estado' => ['required', 'string'],
        ]);

        if (!in_array($data['estado'], ['activo', 'bloqueado'], true)) {
            return response()->json(['mensaje' => 'Estado inválido.'], 400);
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        $usuario->estado = $data['estado'];

        if ($data['estado'] === 'activo') {
            $usuario->intentos_fallidos = 0;
            $usuario->bloqueado_hasta = null;
        }

        $usuario->save();

        $adminCodigo = $request->user()->codigo_usuario ?? 'desconocido';
        $accion = $data['estado'] === 'activo' ? 'desbloqueada' : 'bloqueada';
        Bitacora::create([
            'fecha' => now(),
            'tipo' => $data['estado'] === 'activo' ? 'ok' : 'warn',
            'mensaje' => "Cuenta {$accion} por {$adminCodigo}",
            'usuario_id' => $usuario->id,
        ]);

        return response()->noContent();
    }
}