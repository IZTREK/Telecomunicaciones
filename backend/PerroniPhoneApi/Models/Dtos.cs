namespace PerroniPhoneApi.Models;

public record LoginRequest(string Correo, string Password);

public class LoginResponse
{
    public string Token { get; set; } = "";
    public DateTime Expira { get; set; }
    public UsuarioDto Usuario { get; set; } = null!;
}

public record ActualizarRolRequest(string Rol);
public record ActualizarEstadoRequest(string Estado);

public class BitacoraEvento
{
    public int Id { get; set; }
    public DateTime Fecha { get; set; }
    public string Tipo { get; set; } = "";
    public string Mensaje { get; set; } = "";
    public int? UsuarioId { get; set; }
}

public class Plan
{
    public string Id { get; set; } = "";
    public string Nombre { get; set; } = "";
    public string Velocidad { get; set; } = "";
    public string DatosMoviles { get; set; } = "";
    public string Precio { get; set; } = "";
}
