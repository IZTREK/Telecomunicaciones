<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// RBAC: qué puede hacer cada rol dentro del portal.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('rol', 20);
            $table->string('permiso', 50);
            $table->timestamps();

            $table->unique(['rol', 'permiso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
