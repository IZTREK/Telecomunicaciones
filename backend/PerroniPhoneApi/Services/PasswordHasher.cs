using System.Security.Cryptography;

namespace PerroniPhoneApi.Services;

/// <summary>
/// Hashing de contraseñas con PBKDF2 (HMAC-SHA256, 100,000 iteraciones).
/// Esto reemplaza el SHA-256 simple usado en la versión sin backend:
/// PBKDF2 está diseñado específicamente para contraseñas (es lento a
/// propósito, lo que dificulta ataques de fuerza bruta/diccionario).
/// El cálculo y la verificación ocurren SIEMPRE en el servidor.
/// </summary>
public class PasswordHasher
{
    private const int Iteraciones = 100_000;
    private const int TamanoClave = 32; // 256 bits

    public string Hash(string password, string saltHex)
    {
        byte[] salt = Convert.FromHexString(saltHex);
        byte[] clave = Rfc2898DeriveBytes.Pbkdf2(
            password, salt, Iteraciones, HashAlgorithmName.SHA256, TamanoClave);
        return Convert.ToHexString(clave).ToLowerInvariant();
    }

    public bool Verificar(string password, string saltHex, string hashEsperado)
    {
        string calculado = Hash(password, saltHex);
        // Comparación en tiempo constante para evitar timing attacks.
        return CryptographicOperations.FixedTimeEquals(
            Convert.FromHexString(calculado),
            Convert.FromHexString(hashEsperado));
    }

    public string GenerarSalt()
    {
        return Convert.ToHexString(RandomNumberGenerator.GetBytes(16)).ToLowerInvariant();
    }
}
