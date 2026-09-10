<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'planes';
    public $incrementing = false;   // el id es texto ("P-10"), no autonumérico
    protected $keyType = 'string';

    protected $fillable = ['id', 'nombre', 'velocidad', 'datos_moviles', 'precio'];
}
