using Dapper;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using PerroniPhoneApi.Data;
using PerroniPhoneApi.Models;

namespace PerroniPhoneApi.Controllers;

[ApiController]
[Route("api/usuarios")]
[Authorize] // requiere un JWT válido para cualquier acción de este controlador
public class UsuariosController : ControllerBase
{
    private static readonly string[] RolesValidos = { "cliente", "soporte", "administrador" };

    private readonly ConexionFactory _conexiones;

    public UsuariosController(ConexionFactory conexiones)
    {
        _conexiones = conexiones;
    }

    // Cualquier usuario autenticado puede ver el directorio (se usa en el dashboard).
    [HttpGet]
    public async Task<IActionResult> Listar()
    {
        using var conexion = _conexiones.Crear();
        var usuarios = await conexion.QueryAsync<Usuario>(
            "SELECT * FROM Usuarios ORDER BY Id");

        return Ok(usuarios.Select(UsuarioDto.DesdeUsuario));
    }

    // Solo administradores pueden cambiar el rol de un usuario.
    [HttpPut("{id:int}/rol")]
    [Authorize(Roles = "administrador")]
    public async Task<IActionResult> ActualizarRol(int id, [FromBody] ActualizarRolRequest req)
    {
        if (!RolesValidos.Contains(req.Rol))
            return BadRequest(new { mensaje = "Rol inválido." });

        using var conexion = _conexiones.Crear();
        int filas = await conexion.ExecuteAsync(
            "UPDATE Usuarios SET Rol = @Rol WHERE Id = @Id",
            new { req.Rol, Id = id });

        if (filas == 0) return NotFound();

        string adminCodigo = User.FindFirst("codigo")?.Value ?? "desconocido";
        await conexion.ExecuteAsync(
            "INSERT INTO Bitacora (Tipo, Mensaje, UsuarioId) VALUES ('ok', @msg, @id)",
            new { msg = $"Cambio de rol a '{req.Rol}' aplicado por {adminCodigo}", id });

        return NoContent();
    }

    // Solo administradores pueden bloquear/desbloquear cuentas.
    [HttpPut("{id:int}/estado")]
    [Authorize(Roles = "administrador")]
    public async Task<IActionResult> ActualizarEstado(int id, [FromBody] ActualizarEstadoRequest req)
    {
        if (req.Estado is not ("activo" or "bloqueado"))
            return BadRequest(new { mensaje = "Estado inválido." });

        using var conexion = _conexiones.Crear();

        // Al reactivar una cuenta también se limpian los contadores de intentos fallidos.
        int filas = await conexion.ExecuteAsync(
            @"UPDATE Usuarios
              SET Estado = @Estado,
                  IntentosFallidos = CASE WHEN @Estado = 'activo' THEN 0 ELSE IntentosFallidos END,
                  BloqueadoHasta = CASE WHEN @Estado = 'activo' THEN NULL ELSE BloqueadoHasta END
              WHERE Id = @Id",
            new { req.Estado, Id = id });

        if (filas == 0) return NotFound();

        string adminCodigo = User.FindFirst("codigo")?.Value ?? "desconocido";
        string accion = req.Estado == "activo" ? "desbloqueada" : "bloqueada";
        await conexion.ExecuteAsync(
            "INSERT INTO Bitacora (Tipo, Mensaje, UsuarioId) VALUES (@tipo, @msg, @id)",
            new { tipo = req.Estado == "activo" ? "ok" : "warn", msg = $"Cuenta {accion} por {adminCodigo}", id });

        return NoContent();
    }
}
