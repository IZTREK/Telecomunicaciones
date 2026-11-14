<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Matriz RBAC: qué permisos tiene cada rol. La tabla se sincroniza en cada
 * arranque desde database/data/permisos.json (ver DatabaseSeeder), que es
 * la ÚNICA fuente de verdad: el backend autoriza con esta tabla y el
 * frontend recibe los permisos del usuario en la respuesta del login.
 */
class Permiso extends Model
{
    protected $table = 'permisos';
    protected $fillable = ['rol', 'permiso'];

    public static function tiene(string $rol, string $permiso): bool
    {
        return static::where('rol', $rol)->where('permiso', $permiso)->exists();
    }

    /** @return string[] */
    public static function delRol(string $rol): array
    {
        return static::where('rol', $rol)->orderBy('id')->pluck('permiso')->all();
    }

    /** @return array<string, string[]>  ej. ['administrador' => ['ver_dashboard', ...], ...] */
    public static function matriz(): array
    {
        return static::orderBy('id')->get(['rol', 'permiso'])
            ->groupBy('rol')
            ->map(fn ($filas) => $filas->pluck('permiso')->all())
            ->all();
    }

    /** Los roles válidos son exactamente los que existen en la matriz. */
    public static function roles(): array
    {
        return array_keys(static::matriz());
    }
}
