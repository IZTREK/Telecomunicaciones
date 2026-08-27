namespace PerroniPhoneApi.Models;

public class Usuario
{
    public int Id { get; set; }
    public string CodigoUsuario { get; set; } = "";
    public string Nombre { get; set; } = "";
    public string Correo { get; set; } = "";
    public string Rol { get; set; } = "";
    public string Estado { get; set; } = "";
    public string Salt { get; set; } = "";
    public string HashPassword { get; set; } = "";
    public int IntentosFallidos { get; set; }
    public DateTime? BloqueadoHasta { get; set; }
    public DateTime? UltimoAcceso { get; set; }
}

// DTO seguro para enviar al frontend (nunca incluye Salt/HashPassword)
public class UsuarioDto
{
    public int Id { get; set; }
    public string CodigoUsuario { get; set; } = "";
    public string Nombre { get; set; } = "";
    public string Correo { get; set; } = "";
    public string Rol { get; set; } = "";
    public string Estado { get; set; } = "";
    public DateTime? UltimoAcceso { get; set; }

    public static UsuarioDto DesdeUsuario(Usuario u) => new()
    {
        Id = u.Id,
        CodigoUsuario = u.CodigoUsuario,
        Nombre = u.Nombre,
        Correo = u.Correo,
        Rol = u.Rol,
        Estado = u.Estado,
        UltimoAcceso = u.UltimoAcceso
    };
}
