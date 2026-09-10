<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\Usuario;
use App\Services\PasswordHasher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private const MAX_INTENTOS = 5;
    private const MINUTOS_BLOQUEO = 2;

    public function __construct(private PasswordHasher $hasher)
    {
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'correo' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Consulta a través de Eloquent: internamente ya usa "prepared
        // statements" (parámetros enlazados), igual que Dapper en C#,
        // así que el correo nunca se concatena directo al SQL.
        $usuario = Usuario::where('correo', $data['correo'])->first();

        // Mensaje genérico: no revela si el correo existe o no (anti user-enumeration).
        $credencialesInvalidas = response()->json(['mensaje' => 'Correo o contraseña incorrectos.'], 401);

        if (!$usuario) {
            $this->registrarBitacora('warn', "Intento de acceso con correo no registrado ({$data['correo']})");
            return $credencialesInvalidas;
        }

        if ($usuario->estado === 'bloqueado') {
            return response()->json(['mensaje' => 'Esta cuenta está bloqueada. Contacta a un administrador.'], 401);
        }

        if ($usuario->bloqueado_hasta && Carbon::parse($usuario->bloqueado_hasta)->isFuture()) {
            $restante = now()->diffInSeconds($usuario->bloqueado_hasta);
            return response()->json(['mensaje' => "Demasiados intentos. Intenta de nuevo en {$restante}s."], 429);
        }

        $passwordCorrecto = $this->hasher->verificar($data['password'], $usuario->salt, $usuario->hash_password);

        if (!$passwordCorrecto) {
            $usuario->intentos_fallidos += 1;

            if ($usuario->intentos_fallidos >= self::MAX_INTENTOS) {
                $usuario->bloqueado_hasta = now()->addMinutes(self::MINUTOS_BLOQUEO);
                $usuario->save();

                $this->registrarBitacora(
                    'danger',
                    self::MAX_INTENTOS . "º intento fallido — cuenta bloqueada temporalmente ({$usuario->codigo_usuario})",
                    $usuario->id
                );

                return response()->json([
                    'mensaje' => 'Demasiados intentos fallidos. Cuenta bloqueada temporalmente por ' . self::MINUTOS_BLOQUEO . ' minutos.',
                ], 429);
            }

            $usuario->save();
            return $credencialesInvalidas;
        }

        // Login correcto: se reinician los intentos y se registra el acceso.
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
            'usuario' => $usuario->toPublicArray(),
        ]);
    }

    private function registrarBitacora(string $tipo, string $mensaje, ?int $usuarioId = null): void
    {
        Bitacora::create([
            'fecha' => now(),
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'usuario_id' => $usuarioId,
        ]);
    }
}
