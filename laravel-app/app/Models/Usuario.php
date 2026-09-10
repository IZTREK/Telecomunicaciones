<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;

    protected $table = 'usuarios';

    protected $fillable = [
        'codigo_usuario', 'nombre', 'correo', 'rol', 'estado',
        'salt', 'hash_password', 'intentos_fallidos',
        'bloqueado_hasta', 'ultimo_acceso',
    ];

    // Nunca se envían al frontend (equivalente a nuestro UsuarioDto en C#,
    // que a propósito nunca incluía Salt ni HashPassword).
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

    /**
     * Traduce el modelo al mismo formato (camelCase) que el frontend ya
     * esperaba de la API en ASP.NET Core — así assets/js/api.js y
     * assets/js/auth.js no necesitaron cambiar nada al migrar el backend.
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

    // Claims extra dentro del JWT: aquí viaja el rol, igual que en TokenService.cs
    public function getJWTCustomClaims()
    {
        return [
            'codigo' => $this->codigo_usuario,
            'nombre' => $this->nombre,
            'rol' => $this->rol,
        ];
    }
}
