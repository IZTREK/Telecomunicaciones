<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Permiso;
use App\Models\Usuario;
use App\Services\PasswordHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private const MAX_INTENTOS = 5;
    private const MINUTOS_BLOQUEO = 2;

    // Salt/hash de relleno para correos que no existen: así se ejecuta el
    // mismo PBKDF2 y la respuesta tarda lo mismo exista o no la cuenta
    // (evita enumerar usuarios midiendo tiempos).
    private const SALT_FICTICIO = '00000000000000000000000000000000';
    private const HASH_FICTICIO = '0000000000000000000000000000000000000000000000000000000000000000';

    public function __construct(private PasswordHasher $hasher)
    {
    }

    public function login(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'correo' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:200'],
        ]);
        if ($validador->fails()) {
            return response()->json(['mensaje' => 'Ingresa un correo válido y tu contraseña.'], 422);
        }
        $data = $validador->validated();

        // Eloquent usa consultas parametrizadas: el correo nunca se
        // concatena al SQL (sin inyección SQL).
        $usuario = Usuario::where('correo', $data['correo'])->first();

        $passwordCorrecto = $this->hasher->verificar(
            $data['password'],
            $usuario->salt ?? self::SALT_FICTICIO,
            $usuario->hash_password ?? self::HASH_FICTICIO,
        ) && $usuario !== null;

        // Mismo mensaje y mismo código si el correo no existe, si la
        // contraseña es incorrecta o si la cuenta está bloqueada: nada de
        // eso se revela a quien no conoce la contraseña.
        if (!$passwordCorrecto) {
            if ($usuario) {
                $this->registrarIntentoFallido($usuario->id);
            } else {
                $this->registrarBitacora('warn', "Intento de acceso con correo no registrado ({$data['correo']})");
            }
            return response()->json(['mensaje' => 'Correo o contraseña incorrectos.'], 401);
        }

        // A partir de aquí quien pide ya demostró conocer la contraseña,
        // así que sí se le puede explicar por qué no entra.
        if ($usuario->estado !== 'activo') {
            return response()->json(['mensaje' => 'Esta cuenta está bloqueada. Contacta a un administrador.'], 403);
        }

        if ($usuario->bloqueado_hasta?->isFuture()) {
            $segundos = (int) ceil(now()->diffInSeconds($usuario->bloqueado_hasta, true));
            return response()->json([
                'mensaje' => "Cuenta bloqueada temporalmente por intentos fallidos. Intenta de nuevo en {$segundos} s.",
            ], 429);
        }

        $usuario->intentos_fallidos = 0;
        $usuario->bloqueado_hasta = null;
        $usuario->ultimo_acceso = now();
        $usuario->save();

        $this->registrarBitacora('ok', "Inicio de sesión correcto — {$usuario->correo}", $usuario->id);

        $token = JWTAuth::fromUser($usuario);
        $ttlMinutos = (int) config('jwt.ttl');

        return response()->json([
            'token' => $token,
            'expira' => now()->addMinutes($ttlMinutos)->toIso8601String(),
            'usuario' => $usuario->toPublicArray() + ['permisos' => Permiso::delRol($usuario->rol)],
        ]);
    }

    /**
     * Suma un intento fallido de forma atómica: el registro se bloquea
     * (SELECT ... FOR UPDATE) mientras se actualiza, así que peticiones
     * simultáneas no pueden "perder" intentos y saltarse el límite.
     */
    private function registrarIntentoFallido(int $usuarioId): void
    {
        DB::transaction(function () use ($usuarioId) {
            $usuario = Usuario::whereKey($usuarioId)->lockForUpdate()->first();

            // Durante un bloqueo vigente los intentos ya no cuentan.
            if ($usuario->bloqueado_hasta?->isFuture()) {
                return;
            }

            // Si hubo un bloqueo que ya venció (o el contador quedó en el
            // máximo), se empieza de cero en lugar de re-bloquear al primer error.
            if ($usuario->bloqueado_hasta || $usuario->intentos_fallidos >= self::MAX_INTENTOS) {
                $usuario->intentos_fallidos = 0;
                $usuario->bloqueado_hasta = null;
            }

            $usuario->intentos_fallidos += 1;

            if ($usuario->intentos_fallidos >= self::MAX_INTENTOS) {
                $usuario->bloqueado_hasta = now()->addMinutes(self::MINUTOS_BLOQUEO);
                $this->registrarBitacora(
                    'danger',
                    self::MAX_INTENTOS . "º intento fallido — cuenta bloqueada temporalmente ({$usuario->codigo_usuario})",
                    $usuario->id
                );
            }

            $usuario->save();
        });
    }

    private function registrarBitacora(string $tipo, string $mensaje, ?int $usuarioId = null): void
    {
        Bitacora::create([
            'fecha' => now(),
            'tipo' => $tipo,
            'mensaje' => Str::limit($mensaje, 295),
            'usuario_id' => $usuarioId,
        ]);
    }
}
