<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;

// Catálogo público: no requiere autenticación (se usa en la página de inicio).
class PlanesController extends Controller
{
    public function index()
    {
        $planes = Plan::all()->map(fn (Plan $p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'velocidad' => $p->velocidad,
            'datosMoviles' => $p->datos_moviles,
            'precio' => $p->precio,
        ]);

        return response()->json($planes);
    }
}
