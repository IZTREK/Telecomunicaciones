<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Bases creadas con la versión anterior de create_usuarios_table tienen
 * "rol" como ENUM('administrador','soporte','cliente'), que no admite los
 * roles nuevos (redes, encargado, contabilidad). Esto la convierte a texto.
 * En bases nuevas la columna ya nace como VARCHAR y el ALTER no cambia nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE usuarios MODIFY rol VARCHAR(30) NOT NULL');
    }

    public function down(): void
    {
        // Sin reversa a propósito: volver al ENUM de tres roles fallaría en
        // cuanto exista un usuario con un rol nuevo. La columna se queda VARCHAR.
    }
};
