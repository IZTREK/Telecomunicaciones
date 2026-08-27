using Dapper;
using Microsoft.AspNetCore.Mvc;
using PerroniPhoneApi.Data;
using PerroniPhoneApi.Models;
using PerroniPhoneApi.Services;

namespace PerroniPhoneApi.Controllers;

[ApiController]
[Route("api/auth")]
public class AuthController : ControllerBase
{
    private const int MaxIntentos = 5;
    private const int MinutosBloqueo = 2;

    private readonly ConexionFactory _conexiones;
    private readonly PasswordHasher _hasher;
    private readonly TokenService _tokens;

    public AuthController(ConexionFactory conexiones, PasswordHasher hasher, TokenService tokens)
    {
        _conexiones = conexiones;
        _hasher = hasher;
        _tokens = tokens;
    }

    [HttpPost("login")]
    public async Task<IActionResult> Login([FromBody] LoginRequest req)
    {
        if (string.IsNullOrWhiteSpace(req.Correo) || string.IsNullOrWhiteSpace(req.Password))
            return BadRequest(new { mensaje = "Correo y contraseña son obligatorios." });

        using var conexion = _conexiones.Crear();

        // Consulta parametrizada: el correo del usuario NUNCA se concatena
        // directamente en el SQL, lo que evita inyección SQL.
        var usuario = await conexion.QuerySingleOrDefaultAsync<Usuario>(
            "SELECT * FROM Usuarios WHERE Correo = @Correo",
            new { req.Correo });

        // Mensaje genérico: no revela si el correo existe o no (anti user-enumeration).
        var credencialesInvalidas = Unauthorized(new { mensaje = "Correo o contraseña incorrectos." });

        if (usuario is null)
        {
            await RegistrarBitacora(conexion, "warn", $"Intento de acceso con correo no registrado ({req.Correo})", null);
            return credencialesInvalidas;
        }

        if (usuario.Estado == "bloqueado")
            return Unauthorized(new { mensaje = "Esta cuenta está bloqueada. Contacta a un administrador." });

        if (usuario.BloqueadoHasta is not null && usuario.BloqueadoHasta > DateTime.UtcNow)
        {
            var restante = (int)(usuario.BloqueadoHasta.Value - DateTime.UtcNow).TotalSeconds;
            return StatusCode(429, new { mensaje = $"Demasiados intentos. Intenta de nuevo en {restante}s." });
        }

        bool passwordCorrecto = _hasher.Verificar(req.Password, usuario.Salt, usuario.HashPassword);

        if (!passwordCorrecto)
        {
            int nuevosIntentos = usuario.IntentosFallidos + 1;
            DateTime? bloqueoHasta = nuevosIntentos >= MaxIntentos
                ? DateTime.UtcNow.AddMinutes(MinutosBloqueo)
                : null;

            await conexion.ExecuteAsync(
                "UPDATE Usuarios SET IntentosFallidos = @Intentos, BloqueadoHasta = @Bloqueo WHERE Id = @Id",
                new { Intentos = nuevosIntentos, Bloqueo = bloqueoHasta, usuario.Id });

            if (bloqueoHasta is not null)
            {
                await RegistrarBitacora(conexion, "danger",
                    $"{MaxIntentos}º intento fallido — cuenta bloqueada temporalmente ({usuario.CodigoUsuario})", usuario.Id);
                return StatusCode(429, new { mensaje = $"Demasiados intentos fallidos. Cuenta bloqueada temporalmente por {MinutosBloqueo} minutos." });
            }

            return credencialesInvalidas;
        }

        // Login correcto: se reinician los intentos y se registra el acceso.
        await conexion.ExecuteAsync(
            "UPDATE Usuarios SET IntentosFallidos = 0, BloqueadoHasta = NULL, UltimoAcceso = SYSUTCDATETIME() WHERE Id = @Id",
            new { usuario.Id });

        await RegistrarBitacora(conexion, "ok", $"Inicio de sesión correcto — {usuario.Correo}", usuario.Id);

        var dto = UsuarioDto.DesdeUsuario(usuario);
        var (token, expira) = _tokens.GenerarToken(dto);

        return Ok(new LoginResponse { Token = token, Expira = expira, Usuario = dto });
    }

    private static async Task RegistrarBitacora(System.Data.IDbConnection conexion, string tipo, string mensaje, int? usuarioId)
    {
        await conexion.ExecuteAsync(
            "INSERT INTO Bitacora (Tipo, Mensaje, UsuarioId) VALUES (@tipo, @mensaje, @usuarioId)",
            new { tipo, mensaje, usuarioId });
    }
}
