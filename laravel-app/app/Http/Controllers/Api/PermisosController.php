<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;

// Matriz de permisos por rol, para la pantalla de administración
// (selector de roles y tabla de referencia). Requiere gestionar_usuarios.
class PermisosController extends Controller
{
    public function index()
    {
        $matriz = Permiso::matriz();

        return response()->json([
            'roles' => array_keys($matriz),
            'matriz' => $matriz,
        ]);
    }
}
