<?php

namespace App\Services;

/**
 * Hashing de contraseñas con PBKDF2 (HMAC-SHA256, 100,000 iteraciones).
 * Es el mismo algoritmo y los mismos parámetros que usábamos en
 * PasswordHasher.cs (ASP.NET Core) — por eso los usuarios ficticios se
 * pudieron migrar con el mismo salt/hash sin tener que inventar
 * contraseñas nuevas. PHP trae hash_pbkdf2() en el núcleo del lenguaje,
 * no requiere ningún paquete adicional.
 */
class PasswordHasher
{
    private const ITERACIONES = 100000;
    private const LONGITUD_CLAVE = 32; // 256 bits

    public function hash(string $password, string $saltHex): string
    {
        $salt = hex2bin($saltHex);
        return hash_pbkdf2('sha256', $password, $salt, self::ITERACIONES, self::LONGITUD_CLAVE, false);
    }

    public function verificar(string $password, string $saltHex, string $hashEsperado): bool
    {
        $calculado = $this->hash($password, $saltHex);
        // hash_equals() compara en tiempo constante (evita timing attacks),
        // igual que CryptographicOperations.FixedTimeEquals en C#.
        return hash_equals($hashEsperado, $calculado);
    }

    public function generarSalt(): string
    {
        return bin2hex(random_bytes(16));
    }
}
