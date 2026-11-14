<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Equivalente a la tabla Usuarios de SQL Server, adaptada a MySQL/Eloquent.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_usuario', 10)->unique();   // ej. U-1001
            $table->string('nombre', 120);
            $table->string('correo', 150)->unique();
            // Texto libre: los roles válidos los define database/data/permisos.json.
            $table->string('rol', 30);
            $table->enum('estado', ['activo', 'bloqueado'])->default('activo');
            $table->string('salt', 64);
            $table->string('hash_password', 128);
            $table->unsignedTinyInteger('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
