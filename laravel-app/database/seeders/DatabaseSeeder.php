<?php

namespace Database\Seeders;

use App\Models\Bitacora;
use App\Models\Permiso;
use App\Models\Plan;
use App\Models\Usuario;
use App\Services\PasswordHasher;
use Illuminate\Database\Seeder;

/**
 * Datos ficticios de PerroniPhone actualizados.
 * Es idempotente a propósito: si ya hay usuarios en la base, NO vuelve a insertar nada.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Usuario::count() > 0) {
            $this->command->info('Ya hay datos en la base — se omite el seed.');
            return;
        }

        $hasher = app(PasswordHasher::class);

        // --- Definición de Usuarios Nuevos ---
        $usuariosLista = [
            [
                'codigo_usuario' => 'U-1001',
                'nombre' => 'Rafa',
                'correo' => 'rafa@perroniphone.edu.mx',
                'pass' => 'Rafa#2026',
                'rol' => 'administrador',
                'estado' => 'activo',
                'intentos_fallidos' => 0,
                'ultimo_acceso' => '2026-08-12 09:14:00',
            ],
            [
                'codigo_usuario' => 'U-1002',
                'nombre' => 'Joshua',
                'correo' => 'joshua@perroniphone.edu.mx',
                'pass' => 'Joshua#2026',
                'rol' => 'soporte',
                'estado' => 'activo',
                'intentos_fallidos' => 0,
                'ultimo_acceso' => '2026-08-13 17:02:00',
            ],
            [
                'codigo_usuario' => 'U-1003',
                'nombre' => 'Carlos',
                'correo' => 'carlos@perroniphone.edu.mx',
                'pass' => 'Carlos#2026',
                'rol' => 'cliente',
                'estado' => 'activo',
                'intentos_fallidos' => 0,
                'ultimo_acceso' => '2026-08-14 08:47:00',
            ],
            [
                'codigo_usuario' => 'U-1004',
                'nombre' => 'Iván',
                'correo' => 'ivan@perroniphone.edu.mx',
                'pass' => 'Ivan#2026',
                'rol' => 'cliente',
                'estado' => 'bloqueado',
                'intentos_fallidos' => 5,
                'ultimo_acceso' => '2026-07-30 11:20:00',
            ],
            [
                'codigo_usuario' => 'U-1005',
                'nombre' => 'Simón',
                'correo' => 'simon@perroniphone.edu.mx',
                'pass' => 'Simon#2026',
                'rol' => 'administrador',
                'estado' => 'activo',
                'intentos_fallidos' => 0,
                'ultimo_acceso' => '2026-08-15 10:00:00',
            ],
        ];

        // --- Carga dinámica de Usuarios ---
        foreach ($usuariosLista as $u) {
            $salt = bin2hex(random_bytes(16));
            Usuario::create([
                'codigo_usuario' => $u['codigo_usuario'],
                'nombre' => $u['nombre'],
                'correo' => $u['correo'],
                'rol' => $u['rol'],
                'estado' => $u['estado'],
                'salt' => $salt,
                'hash_password' => $hasher->hash($u['pass'], $salt),
                'intentos_fallidos' => $u['intentos_fallidos'],
                'ultimo_acceso' => $u['ultimo_acceso'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // --- Permisos por rol ---
        Permiso::insert([
            ['rol' => 'administrador', 'permiso' => 'ver_dashboard', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'administrador', 'permiso' => 'gestionar_usuarios', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'administrador', 'permiso' => 'ver_bitacora', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'administrador', 'permiso' => 'editar_roles', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'administrador', 'permiso' => 'ver_grc', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'soporte', 'permiso' => 'ver_dashboard', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'soporte', 'permiso' => 'ver_bitacora', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'soporte', 'permiso' => 'ver_grc', 'created_at' => now(), 'updated_at' => now()],
            ['rol' => 'cliente', 'permiso' => 'ver_dashboard', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // --- Planes ---
        Plan::insert([
            ['id' => 'P-10', 'nombre' => 'PerroniPhone Esencial', 'velocidad' => '50 Mbps', 'datos_moviles' => '10 GB', 'precio' => '$349 MXN', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'P-20', 'nombre' => 'PerroniPhone Plus', 'velocidad' => '150 Mbps', 'datos_moviles' => '30 GB', 'precio' => '$549 MXN', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'P-30', 'nombre' => 'PerroniPhone Total', 'velocidad' => '500 Mbps', 'datos_moviles' => 'Ilimitados', 'precio' => '$799 MXN', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // --- Bitácora inicial ---
        Bitacora::insert([
            ['fecha' => '2026-08-14 08:47:12', 'tipo' => 'ok', 'mensaje' => 'Inicio de sesión correcto — carlos@perroniphone.edu.mx', 'usuario_id' => 3],
            ['fecha' => '2026-08-14 08:44:05', 'tipo' => 'warn', 'mensaje' => 'Intento de acceso con formato de correo inválido', 'usuario_id' => null],
            ['fecha' => '2026-07-30 07:58:41', 'tipo' => 'danger', 'mensaje' => '5º intento fallido — cuenta bloqueada automáticamente (U-1004)', 'usuario_id' => 4],
            ['fecha' => '2026-08-12 07:12:30', 'tipo' => 'ok', 'mensaje' => 'Cambio de rol aplicado por U-1001 (administrador)', 'usuario_id' => 1],
            ['fecha' => '2026-08-11 06:30:02', 'tipo' => 'warn', 'mensaje' => 'Token de sesión expirado — se solicitó nuevo inicio de sesión', 'usuario_id' => null],
        ]);

        $this->command->info('Datos ficticios de PerroniPhone cargados correctamente.');
    }
}