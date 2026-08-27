using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Microsoft.IdentityModel.Tokens;
using PerroniPhoneApi.Models;

namespace PerroniPhoneApi.Services;

/// <summary>
/// Genera el JWT que representa la sesión del usuario. A diferencia de la
/// versión sin backend (donde el "token" era un JSON en Base64 sin firmar),
/// este token está firmado con una llave que solo el servidor conoce:
/// el cliente no puede alterar el rol o el id sin invalidar la firma.
/// </summary>
public class TokenService
{
    private readonly IConfiguration _config;

    public TokenService(IConfiguration config)
    {
        _config = config;
    }

    public (string token, DateTime expira) GenerarToken(UsuarioDto usuario)
    {
        var jwtSection = _config.GetSection("Jwt");
        var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtSection["Key"]!));
        var credenciales = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);

        int minutos = int.Parse(jwtSection["ExpiraMinutos"] ?? "20");
        var expira = DateTime.UtcNow.AddMinutes(minutos);

        var claims = new List<Claim>
        {
            new(JwtRegisteredClaimNames.Sub, usuario.Id.ToString()),
            new("codigo", usuario.CodigoUsuario),
            new("nombre", usuario.Nombre),
            new(ClaimTypes.Email, usuario.Correo),
            new(ClaimTypes.Role, usuario.Rol),
        };

        var token = new JwtSecurityToken(
            issuer: jwtSection["Issuer"],
            audience: jwtSection["Audience"],
            claims: claims,
            expires: expira,
            signingCredentials: credenciales
        );

        return (new JwtSecurityTokenHandler().WriteToken(token), expira);
    }
}
