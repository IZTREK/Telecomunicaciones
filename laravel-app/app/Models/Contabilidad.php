<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contabilidad extends Model
{
    protected $table = 'contabilidad';

    protected $fillable = ['fecha', 'concepto', 'tipo', 'monto', 'categoria'];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];
}