using Dapper;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using PerroniPhoneApi.Data;
using PerroniPhoneApi.Models;

namespace PerroniPhoneApi.Controllers;

[ApiController]
[Route("api/bitacora")]
[Authorize(Roles = "administrador,soporte")] // solo estos roles ven la auditoría
public class BitacoraController : ControllerBase
{
    private readonly ConexionFactory _conexiones;

    public BitacoraController(ConexionFactory conexiones)
    {
        _conexiones = conexiones;
    }

    [HttpGet]
    public async Task<IActionResult> Listar()
    {
        using var conexion = _conexiones.Crear();
        var eventos = await conexion.QueryAsync<BitacoraEvento>(
            "SELECT TOP 20 * FROM Bitacora ORDER BY Fecha DESC");
        return Ok(eventos);
    }
}

[ApiController]
[Route("api/planes")]
public class PlanesController : ControllerBase
{
    private readonly ConexionFactory _conexiones;

    public PlanesController(ConexionFactory conexiones)
    {
        _conexiones = conexiones;
    }

    // Catálogo público: no requiere autenticación (se usa en la página de inicio).
    [HttpGet]
    [AllowAnonymous]
    public async Task<IActionResult> Listar()
    {
        using var conexion = _conexiones.Crear();
        var planes = await conexion.QueryAsync<Plan>("SELECT * FROM Planes");
        return Ok(planes);
    }
}
