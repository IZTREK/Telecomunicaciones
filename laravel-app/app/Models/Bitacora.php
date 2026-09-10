<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    public $timestamps = false; // usamos la columna "fecha" en vez de created_at

    protected $fillable = ['fecha', 'tipo', 'mensaje', 'usuario_id'];

    protected $casts = [
        'fecha' => 'datetime',
    ];
}
