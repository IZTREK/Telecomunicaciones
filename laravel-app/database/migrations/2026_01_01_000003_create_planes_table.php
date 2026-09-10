<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Catálogo público de planes (no requiere autenticación para leerse).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->string('id', 10)->primary(); // ej. P-10
            $table->string('nombre', 60);
            $table->string('velocidad', 30);
            $table->string('datos_moviles', 30);
            $table->string('precio', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
