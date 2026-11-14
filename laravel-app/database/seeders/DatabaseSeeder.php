<?php

namespace Database\Seeders;

use App\Models\Bitacora;
use App\Models\Contabilidad;
use App\Models\Permiso;
use App\Models\Plan;
use App\Models\Usuario;
use App\Services\PasswordHasher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos ficticios de PerroniPhone. Corre en CADA arranque del contenedor
 * (docker/entrypoint.sh), así que es idempotente:
 *  - La matriz de permisos se sincroniza siempre con database/data/permisos.json.
 *  - Usuarios, planes, bitácora y contabilidad solo se cargan si su tabla
 *    está vacía (nunca se pisan cambios hechos desde el portal).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->sincronizarPermisos();

        if (Usuario::count() === 0) {
            $this->sembrarUsuariosYBitacora();
        }

        if (Plan::count() === 0) {
            $this->sembrarPlanes();
        }

        if (Contabilidad::count() === 0) {
            $this->sembrarContabilidad();
        }
    }

    private function sincronizarPermisos(): void
    {
        $matriz = json_decode(file_get_contents(database_path('data/permisos.json')), true, flags: JSON_THROW_ON_ERROR);

        $filas = [];
        foreach ($matriz as $rol => $permisos) {
            foreach ($permisos as $permiso) {
                $filas[] = ['rol' => $rol, 'permiso' => $permiso, 'created_at' => now(), 'updated_at' => now()];
            }
        }

        DB::transaction(function () use ($filas) {
            Permiso::query()->delete();
            Permiso::insert($filas);
        });

        $this->command->info('Matriz de permisos sincronizada (' . count($filas) . ' permisos).');
    }

    private function sembrarUsuariosYBitacora(): void
    {
        $hasher = app(PasswordHasher::class);

        $usuarios = [
            ['codigo_usuario' => 'U-1001', 'nombre' => 'Rafa',   'correo' => 'rafa@perroniphone.edu.mx',   'pass' => 'Rafa#2026',   'rol' => 'redes',         'estado' => 'activo',    'ultimo_acceso' => '2026-08-12 09:14:00'],
            ['codigo_usuario' => 'U-1002', 'nombre' => 'Joshua', 'correo' => 'joshua@perroniphone.edu.mx', 'pass' => 'Joshua#2026', 'rol' => 'cliente',       'estado' => 'bloqueado', 'ultimo_acceso' => '2026-08-13 17:02:00'],
            ['codigo_usuario' => 'U-1003', 'nombre' => 'Carlos', 'correo' => 'carlos@perroniphone.edu.mx', 'pass' => 'Carlos#2026', 'rol' => 'encargado',     'estado' => 'activo',    'ultimo_acceso' => '2026-08-14 08:47:00'],
            ['codigo_usuario' => 'U-1004', 'nombre' => 'Iván',   'correo' => 'ivan@perroniphone.edu.mx',   'pass' => 'Ivan#2026',   'rol' => 'contabilidad',  'estado' => 'activo',    'ultimo_acceso' => '2026-07-30 11:20:00'],
            ['codigo_usuario' => 'U-1005', 'nombre' => 'Simón',  'correo' => 'simon@perroniphone.edu.mx',  'pass' => 'Simon#2026',  'rol' => 'administrador', 'estado' => 'activo',    'ultimo_acceso' => '2026-08-15 10:00:00'],
        ];

        $ids = [];
        foreach ($usuarios as $u) {
            $salt = $hasher->generarSalt();
            $ids[$u['codigo_usuario']] = Usuario::create([
                'codigo_usuario' => $u['codigo_usuario'],
                'nombre' => $u['nombre'],
                'correo' => $u['correo'],
                'rol' => $u['rol'],
                'estado' => $u['estado'],
                'salt' => $salt,
                'hash_password' => $hasher->hash($u['pass'], $salt),
                'intentos_fallidos' => 0,
                'ultimo_acceso' => $u['ultimo_acceso'],
            ])->id;
        }

        Bitacora::insert([
            ['fecha' => '2026-08-14 08:47:12', 'tipo' => 'ok',     'mensaje' => 'Inicio de sesión correcto — carlos@perroniphone.edu.mx', 'usuario_id' => $ids['U-1003']],
            ['fecha' => '2026-08-14 08:44:05', 'tipo' => 'warn',   'mensaje' => 'Intento de acceso con formato de correo inválido', 'usuario_id' => null],
            ['fecha' => '2026-08-13 17:30:41', 'tipo' => 'warn',   'mensaje' => 'Cuenta bloqueada por U-1005', 'usuario_id' => $ids['U-1002']],
            ['fecha' => '2026-08-12 07:12:30', 'tipo' => 'ok',     'mensaje' => "Cambio de rol a 'redes' aplicado por U-1005", 'usuario_id' => $ids['U-1001']],
            ['fecha' => '2026-08-11 06:30:02', 'tipo' => 'danger', 'mensaje' => '5º intento fallido — cuenta bloqueada temporalmente (U-1004)', 'usuario_id' => $ids['U-1004']],
        ]);

        $this->command->info('Usuarios y bitácora de ejemplo cargados.');
    }

    private function sembrarPlanes(): void
    {
        Plan::insert([
            ['id' => 'P-10', 'nombre' => 'PerroniPhone Esencial', 'velocidad' => '50 Mbps',  'datos_moviles' => '10 GB',      'precio' => '$349 MXN', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'P-20', 'nombre' => 'PerroniPhone Plus',     'velocidad' => '150 Mbps', 'datos_moviles' => '30 GB',      'precio' => '$549 MXN', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'P-30', 'nombre' => 'PerroniPhone Total',    'velocidad' => '500 Mbps', 'datos_moviles' => 'Ilimitados', 'precio' => '$799 MXN', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function sembrarContabilidad(): void
    {
        $movimientos = [
            ['2026-08-01', 'Pago de clientes — plan Esencial', 'ingreso', 125400.00, 'Ventas'],
            ['2026-08-03', 'Pago de clientes — plan Plus',     'ingreso', 98700.00,  'Ventas'],
            ['2026-08-05', 'Mantenimiento de torres de red',   'gasto',   42000.00,  'Infraestructura'],
            ['2026-08-10', 'Nómina quincenal',                 'gasto',   186500.00, 'Nómina'],
            ['2026-08-12', 'Pago de clientes — plan Total',    'ingreso', 143200.00, 'Ventas'],
            ['2026-08-15', 'Licencias de software de soporte', 'gasto',   15800.00,  'Operación'],
            ['2026-08-20', 'Renovación de ancho de banda',     'gasto',   67300.00,  'Infraestructura'],
            ['2026-08-25', 'Pago de clientes empresariales',   'ingreso', 210600.00, 'Ventas'],
        ];

        Contabilidad::insert(array_map(fn ($m) => [
            'fecha' => $m[0], 'concepto' => $m[1], 'tipo' => $m[2], 'monto' => $m[3], 'categoria' => $m[4],
            'created_at' => now(), 'updated_at' => now(),
        ], $movimientos));
    }
}
