<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contabilidad;

// Protegido por EnsureRole en routes/api.php — solo administrador,
// encargado y contabilidad pueden llegar aquí (el backend lo revalida,
// no solo el frontend).
class ContabilidadController extends Controller
{
    public function index()
    {
        $totalIngresos = (float) Contabilidad::where('tipo', 'ingreso')->sum('monto');
        $totalGastos = (float) Contabilidad::where('tipo', 'gasto')->sum('monto');

        $movimientos = Contabilidad::orderByDesc('fecha')->get()->map(fn (Contabilidad $m) => [
            'id' => $m->id,
            'fecha' => $m->fecha->toDateString(),
            'concepto' => $m->concepto,
            'tipo' => $m->tipo,
            'monto' => (float) $m->monto,
            'categoria' => $m->categoria,
        ]);

        return response()->json([
            'resumen' => [
                'totalIngresos' => $totalIngresos,
                'totalGastos' => $totalGastos,
                'balance' => $totalIngresos - $totalGastos,
            ],
            'movimientos' => $movimientos,
        ]);
    }
}