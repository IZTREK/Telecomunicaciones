<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BitacoraController;
use App\Http\Controllers\Api\PlanesController;
use App\Http\Controllers\Api\UsuariosController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// --- Público: no requiere token ---
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/planes', [PlanesController::class, 'index']);

// --- Requiere sesión (JWT válido) ---
Route::middleware('auth:api')->group(function () {

    // Cualquier usuario autenticado puede ver el directorio (dashboard/admin).
    Route::get('/usuarios', [UsuariosController::class, 'index']);

    // Solo administrador.
    Route::middleware(EnsureRole::class . ':administrador')->group(function () {
        Route::put('/usuarios/{id}/rol', [UsuariosController::class, 'actualizarRol']);
        Route::put('/usuarios/{id}/estado', [UsuariosController::class, 'actualizarEstado']);
    });

    // Administrador y soporte.
    Route::middleware(EnsureRole::class . ':administrador,soporte')->group(function () {
        Route::get('/bitacora', [BitacoraController::class, 'index']);
    });
});
