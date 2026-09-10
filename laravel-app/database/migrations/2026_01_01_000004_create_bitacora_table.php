<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Auditoría de seguridad: accesos correctos, fallidos, bloqueos, cambios de rol.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha')->useCurrent();
            $table->enum('tipo', ['ok', 'warn', 'danger']);
            $table->string('mensaje', 300);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
