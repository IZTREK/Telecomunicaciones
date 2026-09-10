<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;

// Solo administrador/soporte (ver rutas/api.php) — auditoría de seguridad.
class BitacoraController extends Controller
{
    public function index()
    {
        $eventos = Bitacora::query()
            ->orderByDesc('fecha')
            ->limit(20)
            ->get(['id', 'fecha', 'tipo', 'mensaje']);

        return response()->json($eventos);
    }
}
