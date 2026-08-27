using Microsoft.Data.SqlClient;

namespace PerroniPhoneApi.Data;

/// <summary>
/// Crea conexiones a SQL Server a partir de la cadena de conexión en
/// appsettings.json. Se usan consultas parametrizadas en todo el proyecto
/// (nunca concatenación de strings) para prevenir inyección SQL.
/// </summary>
public class ConexionFactory
{
    private readonly string _connectionString;

    public ConexionFactory(IConfiguration config)
    {
        _connectionString = config.GetConnectionString("Default")
            ?? throw new InvalidOperationException("Falta la cadena de conexión 'Default' en appsettings.json");
    }

    public SqlConnection Crear() => new SqlConnection(_connectionString);
}
