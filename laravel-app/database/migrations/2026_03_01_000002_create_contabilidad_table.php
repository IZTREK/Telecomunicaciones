<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Movimientos contables ficticios (los datos de ejemplo los carga DatabaseSeeder).
return new class extends Migration
{
    public function up(): void
    {
        // Bases que ya corrieron la migración anterior de contabilidad
        // (reemplazada por ésta) ya tienen la tabla.
        if (Schema::hasTable('contabilidad')) {
            return;
        }

        Schema::create('contabilidad', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('concepto', 150);
            $table->enum('tipo', ['ingreso', 'gasto']);
            $table->decimal('monto', 12, 2);
            $table->string('categoria', 60)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contabilidad');
    }
};
