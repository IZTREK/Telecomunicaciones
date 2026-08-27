# Cómo correr el backend de PerroniPhone desde VS Code

Esta guía asume que ya usaste **SQL Server Management Studio (SSMS)** para
conectarte a tu instancia de SQL Server, pero nunca has corrido un proyecto
ASP.NET Core desde VS Code (normalmente lo hacías desde Visual Studio).

---

## 1. Requisitos

1. **.NET 8 SDK** — descárgalo de https://dotnet.microsoft.com/download
   (elige "SDK", no solo "Runtime"). Verifica la instalación abriendo una
   terminal y escribiendo:
   ```
   dotnet --version
   ```
   Debe mostrar algo como `8.0.x`.
2. **Extensión de C# en VS Code** — abre VS Code → pestaña de Extensiones
   (ícono de cuadritos a la izquierda) → busca **"C# Dev Kit"** → Instalar.
3. **SQL Server** ya instalado y accesible desde SSMS (el mismo que ya usas).

---

## 2. Crear la base de datos

1. Abre **SQL Server Management Studio**.
2. Conéctate a tu instancia como siempre.
3. Abre el archivo `sql/crear-base-datos.sql` (Archivo → Abrir → Archivo…).
4. Presiona **Ejecutar** (F5). Deberías ver el mensaje
   `Base de datos PerroniPhoneDB creada correctamente.`
5. En el **Explorador de objetos**, confirma que aparece la base
   `PerroniPhoneDB` con las tablas `Usuarios`, `Permisos`, `Planes` y
   `Bitacora`.

---

## 3. Configurar la cadena de conexión

Abre `PerroniPhoneApi/appsettings.json` y ajusta esta línea según cómo te
conectas en SSMS:

```json
"Default": "Server=localhost\\SQLEXPRESS;Database=PerroniPhoneDB;Trusted_Connection=True;TrustServerCertificate=True;"
```

- Si en SSMS te conectas con el nombre de servidor `localhost` (sin
  instancia), usa: `Server=localhost;Database=PerroniPhoneDB;Trusted_Connection=True;TrustServerCertificate=True;`
- Si usas **LocalDB** (viene con Visual Studio), usa:
  `Server=(localdb)\\MSSQLLocalDB;Database=PerroniPhoneDB;Trusted_Connection=True;TrustServerCertificate=True;`
- Si te conectas con usuario y contraseña (autenticación SQL) en vez de
  Windows, usa: `Server=TU_SERVIDOR;Database=PerroniPhoneDB;User Id=TU_USUARIO;Password=TU_PASSWORD;TrustServerCertificate=True;`

**Tip:** el nombre exacto de tu servidor es el mismo que escribes en el
cuadro "Nombre del servidor" al conectarte en SSMS.

---

## 4. Abrir y correr el proyecto en VS Code

1. En VS Code: **Archivo → Abrir carpeta…** → selecciona la carpeta
   `backend/PerroniPhoneApi` (la que contiene `PerroniPhoneApi.csproj`).
2. Abre una terminal integrada: **Terminal → Nueva terminal** (o `` Ctrl+` ``).
3. Restaura los paquetes NuGet (se descargan automáticamente):
   ```
   dotnet restore
   ```
4. Corre el proyecto:
   ```
   dotnet run
   ```
5. En la consola verás algo como:
   ```
   Now listening on: http://localhost:5205
   ```
   Ese es el puerto de tu API. Si es diferente, actualízalo en
   `assets/js/api.js` (constante `API_BASE`).
6. Abre `http://localhost:5205/swagger` en el navegador — ahí puedes
   probar cada endpoint manualmente (por ejemplo, `POST /api/auth/login`)
   antes de usar el sitio web.

Deja esta terminal corriendo mientras usas el sitio (`Ctrl+C` la detiene).

---

## 5. Correr el frontend

El frontend sigue siendo estático — ábrelo con **Live Server** en VS Code
igual que antes (clic derecho en `index.html` → "Open with Live Server").

Por defecto, la API solo acepta peticiones desde `http://127.0.0.1:5500` y
`http://localhost:5500` (los puertos típicos de Live Server). Si tu Live
Server usa otro puerto, agrégalo en
`PerroniPhoneApi/appsettings.json → Cors → OrigenesPermitidos`.

---

## 6. Probar que todo quedó conectado

1. Con la API corriendo (`dotnet run`) y el sitio abierto con Live Server,
   entra a `login.html`.
2. Inicia sesión como `admin@perroniphone.edu.mx` / `Admin#2026`.
3. Ve a **Usuarios y roles**, desbloquea la cuenta de
   `emilio.farias@perroniphone.edu.mx`.
4. **Presiona F5 para recargar la página.** El usuario debe seguir
   apareciendo como "activo" — porque ahora el cambio vive en SQL Server,
   no en la memoria del navegador.
5. Para confirmarlo también desde SSMS: ejecuta
   `SELECT * FROM PerroniPhoneDB.dbo.Usuarios;` y verifica la columna
   `Estado`.

---

## Problemas comunes

| Síntoma | Causa probable |
|---|---|
| `No se pudo conectar con la API` en el sitio | El backend no está corriendo (`dotnet run`), o el puerto en `api.js` no coincide con la consola. |
| Error de CORS en la consola del navegador (F12) | El origen de Live Server no está en `Cors:OrigenesPermitidos` de `appsettings.json`. |
| `Login failed for user` o error de conexión SQL | La cadena de conexión en `appsettings.json` no coincide con tu servidor/autenticación real. |
| `Cannot open database "PerroniPhoneDB"` | No ejecutaste `sql/crear-base-datos.sql` en SSMS, o lo ejecutaste contra otra instancia. |
