<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    public const ROL_ADMINISTRADOR = 'administrador';

    protected $table = 'usuarios';

    protected $fillable = [
        'codigo_usuario', 'nombre', 'correo', 'rol', 'estado',
        'salt', 'hash_password', 'intentos_fallidos',
        'bloqueado_hasta', 'ultimo_acceso',
    ];

    // Nunca se envían al frontend.
    protected $hidden = [
        'salt', 'hash_password',
    ];

    protected $casts = [
        'bloqueado_hasta' => 'datetime',
        'ultimo_acceso' => 'datetime',
    ];

    // El trait Authenticatable busca por defecto una columna "password";
    // la nuestra se llama distinto, así que se lo indicamos explícitamente.
    public function getAuthPassword()
    {
        return $this->hash_password;
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    /**
     * Formato (camelCase) que espera el frontend: assets/js/*.js y las
     * páginas leen codigoUsuario, ultimoAcceso, etc.
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'codigoUsuario' => $this->codigo_usuario,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'rol' => $this->rol,
            'estado' => $this->estado,
            'ultimoAcceso' => optional($this->ultimo_acceso)->toIso8601String(),
        ];
    }

    // --- Requerido por el paquete JWT: qué va en el "sub" del token ---
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Claims extra dentro del JWT (informativos: la autorización siempre
    // se decide con el rol actual de la base, no con el del token).
    public function getJWTCustomClaims()
    {
        return [
            'codigo' => $this->codigo_usuario,
            'nombre' => $this->nombre,
            'rol' => $this->rol,
        ];
    }
}
