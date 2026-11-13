<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Esta migración hace tres cosas, y se aplica sola con "php artisan migrate"
 * (que ya corre automáticamente en cada arranque del contenedor vía
 * entrypoint.sh) — no hace falta reseedear ni borrar nada:
 *
 *  1. La columna "rol" deja de ser un ENUM cerrado (administrador/soporte/
 *     cliente) y pasa a VARCHAR, para poder agregar roles nuevos del
 *     equipo sin tener que escribir una migración cada vez.
 *  2. Reasigna los roles de las cuentas que ya existen en la base:
 *     Rafa -> redes, Carlos -> encargado, Iván -> contabilidad.
 *  3. Crea la tabla "contabilidad" con movimientos ficticios de
 *     demostración, y el permiso "ver_contabilidad" (solo para
 *     administrador, encargado y contabilidad).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Columna de rol más flexible.
        DB::statement("ALTER TABLE usuarios MODIFY rol VARCHAR(30) NOT NULL");

        // 2) Reasignación de roles de las cuentas del equipo.
        DB::table('usuarios')->where('codigo_usuario', 'U-1001')->update(['rol' => 'redes']);        // Rafa
        DB::table('usuarios')->where('codigo_usuario', 'U-1003')->update(['rol' => 'encargado']);     // Carlos
        DB::table('usuarios')->where('codigo_usuario', 'U-1004')->update(['rol' => 'contabilidad']);  // Iván

        // Rafa (redes) también debe poder ver Gobierno/GRC.
        DB::table('permisos')->updateOrInsert(
            ['rol' => 'redes', 'permiso' => 'ver_dashboard'],
            ['created_at' => now(), 'updated_at' => now()]
        );
        DB::table('permisos')->updateOrInsert(
            ['rol' => 'redes', 'permiso' => 'ver_grc'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // 3) Permiso de contabilidad: solo administrador, encargado y contabilidad.
        foreach (['administrador', 'encargado', 'contabilidad'] as $rol) {
            DB::table('permisos')->updateOrInsert(
                ['rol' => $rol, 'permiso' => 'ver_contabilidad'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Tabla de contabilidad (datos 100% ficticios, solo de demostración).
        Schema::create('contabilidad', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('concepto', 150);
            $table->enum('tipo', ['ingreso', 'gasto']);
            $table->decimal('monto', 12, 2);
            $table->string('categoria', 60)->nullable();
            $table->timestamps();
        });

        DB::table('contabilidad')->insert([
            ['fecha' => '2026-08-01', 'concepto' => 'Pago de clientes — plan Esencial',  'tipo' => 'ingreso', 'monto' => 125400.00, 'categoria' => 'Ventas',          'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-03', 'concepto' => 'Pago de clientes — plan Plus',      'tipo' => 'ingreso', 'monto' => 98700.00,  'categoria' => 'Ventas',          'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-05', 'concepto' => 'Mantenimiento de torres de red',    'tipo' => 'gasto',   'monto' => 42000.00,  'categoria' => 'Infraestructura', 'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-10', 'concepto' => 'Nómina quincenal',                  'tipo' => 'gasto',   'monto' => 186500.00, 'categoria' => 'Nómina',          'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-12', 'concepto' => 'Pago de clientes — plan Total',     'tipo' => 'ingreso', 'monto' => 143200.00, 'categoria' => 'Ventas',          'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-15', 'concepto' => 'Licencias de software de soporte',  'tipo' => 'gasto',   'monto' => 15800.00,  'categoria' => 'Operación',       'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-20', 'concepto' => 'Renovación de ancho de banda',      'tipo' => 'gasto',   'monto' => 67300.00,  'categoria' => 'Infraestructura', 'created_at' => now(), 'updated_at' => now()],
            ['fecha' => '2026-08-25', 'concepto' => 'Pago de clientes empresariales',    'tipo' => 'ingreso', 'monto' => 210600.00, 'categoria' => 'Ventas',          'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contabilidad');
        DB::table('permisos')->where('permiso', 'ver_contabilidad')->delete();
        DB::statement("ALTER TABLE usuarios MODIFY rol ENUM('administrador','soporte','cliente') NOT NULL");
    }
};