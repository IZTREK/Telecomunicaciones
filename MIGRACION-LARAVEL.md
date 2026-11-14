# Migración: de ASP.NET Core + SQL Server a Docker + Laravel + MySQL

Registro de cómo se migró el backend (v2 → v3) para cumplir con la
arquitectura de la propuesta del proyecto: **Nginx + Laravel + MySQL en
Docker**. Para correr el proyecto, sigue el [README](README.md).

La lógica de negocio se conservó (mismos endpoints, mismo algoritmo de
contraseñas, mismas reglas de bloqueo); cambiaron el lenguaje y la
infraestructura. Después de la migración se agregaron roles nuevos, el
módulo de contabilidad y la autorización por permisos (ver más abajo).

---

## 1. Equivalencias

| Antes (ASP.NET Core + SQL Server) | Ahora (Laravel + MySQL) |
|---|---|
| `Program.cs` (arranque, JWT, CORS) | `bootstrap/app.php` + `config/auth.php` + `config/cors.php` |
| `AuthController.cs` | `app/Http/Controllers/Api/AuthController.php` |
| `UsuariosController.cs` | `app/Http/Controllers/Api/UsuariosController.php` |
| `BitacoraController.cs` | `app/Http/Controllers/Api/BitacoraController.php` |
| `[Authorize(Roles = "...")]` | Middleware `permiso:<nombre>` (`EnsurePermission`) en `routes/api.php` |
| `PasswordHasher.cs` (PBKDF2) | `app/Services/PasswordHasher.php` (mismos parámetros) |
| `TokenService.cs` (JWT) | Paquete `php-open-source-saver/jwt-auth` |
| `appsettings.json` | `.env` (generado por `docker/entrypoint.sh`) |
| `crear-base-datos.sql` (SSMS) | `database/migrations/*.php` + `database/seeders/DatabaseSeeder.php` |
| SQL Server Management Studio | phpMyAdmin (http://localhost:8081) |
| `dotnet run` | `docker compose up -d --build` |

## 2. Cómo se arma la imagen

El repositorio solo guarda **nuestro** código (`laravel-app/`). El
`Dockerfile`:

1. Parte de `php:8.3-fpm` e instala las extensiones que necesita Laravel.
2. Crea un Laravel 11 limpio con `composer create-project` e instala el
   paquete de JWT.
3. Copia encima nuestras carpetas (`app/`, `routes/`, `database/`,
   `public/`, `bootstrap/app.php`, `config/auth.php`, `config/cors.php`).

Al arrancar, `docker/entrypoint.sh` crea el `.env`, genera `APP_KEY` y
`JWT_SECRET`, corre las migraciones y el seeder.

## 3. Cambios posteriores a la migración

- **Frontend dentro de `laravel-app/public/`**: nginx lo sirve en el mismo
  origen que la API (`/api`), así que ya no se usa Live Server y funciona
  igual detrás de ngrok.
- **Roles nuevos**: `redes`, `encargado` y `contabilidad`, además de los
  originales `administrador`, `soporte` y `cliente`.
- **Autorización por permisos**: la matriz rol → permisos vive en
  `database/data/permisos.json`; el backend autoriza con ella y el frontend
  la recibe en el login.
- **Módulo de contabilidad**: tabla `contabilidad`, endpoint
  `GET /api/contabilidad` y página `contabilidad.html`.

## 4. Bases de datos de versiones anteriores

Las migraciones y el seeder están hechos para actualizar una base existente
sin borrarla: la columna `rol` se convierte a texto, se crea la tabla de
contabilidad si falta y la matriz de permisos se sincroniza en cada arranque.
Los usuarios existentes **conservan su rol**; para empezar con las cuentas
del README, borra la base con `docker compose down -v`.
