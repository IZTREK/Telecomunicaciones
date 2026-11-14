<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Permiso;
use App\Models\Usuario;
use Illuminate\Http\Request;

// Los permisos de cada acción se revisan en routes/api.php. Aquí solo van
// las reglas que dependen de QUIÉN modifica a QUIÉN.
class UsuariosController extends Controller
{
    // Requiere gestionar_usuarios.
    public function index()
    {
        $usuarios = Usuario::orderBy('id')->get();
        return response()->json($usuarios->map->toPublicArray());
    }

    // Requiere editar_roles.
    public function actualizarRol(Request $request, int $id)
    {
        $data = $request->validate([
            'rol' => ['required', 'string'],
        ]);

        if (!in_array($data['rol'], Permiso::roles(), true)) {
            return response()->json(['mensaje' => 'Rol inválido.'], 400);
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        if ($error = $this->validarQuePuedeModificar($request->user(), $usuario)) {
            return $error;
        }

        $usuario->rol = $data['rol'];
        $usuario->save();

        Bitacora::create([
            'fecha' => now(),
            'tipo' => 'ok',
            'mensaje' => "Cambio de rol a '{$data['rol']}' aplicado por {$request->user()->codigo_usuario}",
            'usuario_id' => $usuario->id,
        ]);

        return response()->noContent();
    }

    // Requiere gestionar_usuarios.
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

        if ($error = $this->validarQuePuedeModificar($request->user(), $usuario)) {
            return $error;
        }

        $usuario->estado = $data['estado'];

        // Al reactivar una cuenta también se limpian los contadores de intentos fallidos.
        if ($data['estado'] === 'activo') {
            $usuario->intentos_fallidos = 0;
            $usuario->bloqueado_hasta = null;
        }

        $usuario->save();

        $accion = $data['estado'] === 'activo' ? 'desbloqueada' : 'bloqueada';
        Bitacora::create([
            'fecha' => now(),
            'tipo' => $data['estado'] === 'activo' ? 'ok' : 'warn',
            'mensaje' => "Cuenta {$accion} por {$request->user()->codigo_usuario}",
            'usuario_id' => $usuario->id,
        ]);

        return response()->noContent();
    }

    /**
     * - Nadie puede cambiar su propio rol ni bloquearse a sí mismo.
     * - Solo un administrador puede modificar la cuenta de otro administrador.
     *
     * Juntas garantizan que siempre quede al menos un administrador activo:
     * para quitar o bloquear a uno hace falta OTRO administrador activo.
     */
    private function validarQuePuedeModificar(Usuario $actor, Usuario $objetivo)
    {
        if ($actor->id === $objetivo->id) {
            return response()->json(['mensaje' => 'No puedes modificar tu propia cuenta.'], 422);
        }

        if ($objetivo->esAdministrador() && !$actor->esAdministrador()) {
            return response()->json(['mensaje' => 'Solo un administrador puede modificar a otro administrador.'], 403);
        }

        return null;
    }
}
