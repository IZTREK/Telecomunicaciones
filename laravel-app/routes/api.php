<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BitacoraController;
use App\Http\Controllers\Api\ContabilidadController;
use App\Http\Controllers\Api\PermisosController;
use App\Http\Controllers\Api\PlanesController;
use App\Http\Controllers\Api\UsuariosController;
use Illuminate\Support\Facades\Route;

// --- Público: no requiere token ---
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('/planes', [PlanesController::class, 'index']);

// --- Requiere JWT válido y cuenta activa. Cada ruta exige además un
//     permiso de la matriz (database/data/permisos.json). ---
Route::middleware(['auth:api', 'cuenta.activa'])->group(function () {

    Route::middleware('permiso:gestionar_usuarios')->group(function () {
        Route::get('/usuarios', [UsuariosController::class, 'index']);
        Route::get('/permisos', [PermisosController::class, 'index']);
        Route::put('/usuarios/{id}/estado', [UsuariosController::class, 'actualizarEstado'])->whereNumber('id');
    });

    Route::put('/usuarios/{id}/rol', [UsuariosController::class, 'actualizarRol'])
        ->middleware('permiso:editar_roles')
        ->whereNumber('id');

    Route::get('/bitacora', [BitacoraController::class, 'index'])->middleware('permiso:ver_bitacora');

    Route::get('/contabilidad', [ContabilidadController::class, 'index'])->middleware('permiso:ver_contabilidad');
});
