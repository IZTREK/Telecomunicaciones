<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id'; // <-- El nombre real en tu base de datos es 'id'
    public $incrementing = true;
    protected $keyType = 'int';

    public function getKeyName()
    {
        return 'id';
    }

    // Requeridos por JWT
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Retornará el id (ej. 3 o el que corresponda en tu tabla)
    }

    public function getJWTCustomClaims()
    {
        return [
            'codigo' => $this->codigo_usuario,
            'nombre' => $this->nombre,
            'rol' => $this->rol,
        ];
    }

    protected $fillable = [
        'codigo_usuario',
        'nombre',
        'correo',
        'hash_password',
        'salt',
        'rol',
        'estado',
        'intentos_fallidos',
        'bloqueado_hasta',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'hash_password',
        'salt',
    ];

    public function toPublicArray()
    {
        return [
            'id' => $this->id,
            'codigo_usuario' => $this->codigo_usuario,
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'rol' => $this->rol,
            'estado' => $this->estado,
        ];
    }
}