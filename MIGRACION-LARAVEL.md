# Migración a Docker + Nginx + Laravel + MySQL

Esta carpeta reemplaza al backend en ASP.NET Core + SQL Server por uno
equivalente en **Laravel + MySQL**, corriendo dentro de **Docker** con
**Nginx** como servidor web — tal como pide la propuesta del proyecto.

La lógica es la misma que ya conocen (mismos endpoints, mismos usuarios
ficticios, mismas reglas de seguridad); solo cambió el lenguaje y dónde
corre.

---

## 1. Qué se instala dónde

```
perroniphone/
├── docker-compose.yml
├── Dockerfile
├── docker/
│   ├── entrypoint.sh
│   └── nginx/default.conf
├── laravel-app/              <- código que SÍ escribimos nosotros
│   ├── app/Http/Controllers/Api/   (AuthController, UsuariosController, ...)
│   ├── app/Http/Middleware/EnsureRole.php
│   ├── app/Models/            (Usuario, Plan, Bitacora, Permiso)
│   ├── app/Services/PasswordHasher.php
│   ├── bootstrap/app.php
│   ├── config/ (auth.php, cors.php)
│   ├── database/migrations/   (equivalente a crear-base-datos.sql)
│   ├── database/seeders/DatabaseSeeder.php  (datos ficticios)
│   ├── routes/api.php
│   └── .env.example
├── assets/js/api.js           <- YA actualizado (apunta a http://localhost:8080/api)
└── (el resto de tu proyecto: index.html, login.html, etc. — sin cambios)
```

**Lo que NO está aquí a propósito:** el esqueleto completo de Laravel
(`vendor/`, `public/index.php`, `artisan`, etc.). Eso se genera solo, la
primera vez que construyan la imagen de Docker (necesita internet real
para bajar Laravel de Packagist/GitHub, algo que yo no puedo hacer desde
aquí). Ustedes solo corren un comando y Docker se encarga.

---

## 2. Cómo instalarlo sobre tu proyecto actual

1. Copia las carpetas `docker/` y `laravel-app/`, y los archivos
   `docker-compose.yml` y `Dockerfile`, a la raíz de tu proyecto
   (`perroniphone/`), junto a `index.html`.
2. Reemplaza tu `assets/js/api.js` actual por el que te mando aparte (ya
   apunta al puerto de Docker/nginx).
3. El backend de ASP.NET Core (`backend/`) puedes dejarlo ahí sin
   problema, o moverlo a una carpeta `backend-dotnet-legacy/` si quieren
   conservar evidencia de la primera versión para su documentación — no
   estorba, Docker no lo toca.

---

## 3. Levantarlo

Desde la terminal, parados en la raíz del proyecto (donde está
`docker-compose.yml`):

```bash
docker compose build
```

Esto tarda varios minutos la primera vez (descarga PHP, Composer, y todo
Laravel). Cuando termine:

```bash
docker compose up -d
```

Con `docker compose logs -f app` puedes ver en vivo qué está haciendo el
contenedor de Laravel (debe terminar con algo como
`[entrypoint] Listo. Arrancando PHP-FPM...`).

---

## 4. Verificar que quedó funcionando

- **API por nginx:** `http://localhost:8080/api/planes` en el navegador
  debe devolver un JSON con los 3 planes.
- **Base de datos (phpMyAdmin):** `http://localhost:8081` — usuario
  `perroniphone_user`, contraseña `perroniphone_pass` (o `root` /
  `perroniphone_root_pass` para ver todo). Deben aparecer las tablas
  `usuarios`, `planes`, `bitacora`, `permisos` ya con datos.
- **Frontend:** abre `index.html` con Live Server, igual que siempre.
  Intenta iniciar sesión con `admin@perroniphone.edu.mx` / `Admin#2026`.

---

## 5. Comandos útiles del día a día

```bash
docker compose up -d          # levantar todo (en segundo plano)
docker compose down           # apagar todo
docker compose logs -f app    # ver qué hace Laravel en vivo
docker compose exec app bash  # entrar a una terminal DENTRO del contenedor
docker compose build app      # reconstruir la imagen si cambiaste el Dockerfile
```

Si cambian código PHP dentro de `laravel-app/`, necesitan **reconstruir**
la imagen (`docker compose build app && docker compose up -d`) para que
el cambio se refleje — a diferencia de `dotnet run`, este contenedor no
recarga solo con guardar el archivo.

---

## 6. Credenciales de prueba (idénticas a las de SQL Server)

| Correo | Contraseña | Rol |
|---|---|---|
| admin@perroniphone.edu.mx | Admin#2026 | administrador |
| soporte@perroniphone.edu.mx | Soporte#2026 | soporte |
| cliente@perroniphone.edu.mx | Cliente#2026 | cliente |
| emilio.farias@perroniphone.edu.mx | Cliente#2026 | cliente (bloqueada) |

---

## 7. Qué cambió realmente (para su reporte de aportaciones)

| Antes (ASP.NET Core + SQL Server) | Ahora (Laravel + MySQL) |
|---|---|
| `Program.cs` (arranque, JWT, CORS) | `bootstrap/app.php` + `config/auth.php` + `config/cors.php` |
| `AuthController.cs` | `app/Http/Controllers/Api/AuthController.php` |
| `UsuariosController.cs` | `app/Http/Controllers/Api/UsuariosController.php` |
| `[Authorize(Roles = "...")]` | Middleware `EnsureRole` en `routes/api.php` |
| `PasswordHasher.cs` (PBKDF2) | `app/Services/PasswordHasher.php` (mismo algoritmo) |
| `TokenService.cs` (JWT) | Paquete `php-open-source-saver/jwt-auth` |
| `crear-base-datos.sql` (SSMS) | `database/migrations/*.php` + `database/seeders/DatabaseSeeder.php` |
| `dotnet run` | `docker compose up` (Nginx + PHP-FPM + MySQL) |

La lógica de negocio (reglas de bloqueo, validaciones, permisos por rol)
es la misma en los dos — lo que cambió es el lenguaje y la
infraestructura donde corre.
