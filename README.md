# PerroniPhone — Portal corporativo académico

Portal corporativo de **PerroniPhone Comunicaciones**, una compañía de
telecomunicaciones **100% ficticia**, creado como proyecto académico de
ciberseguridad y gobierno de TI. Todos los datos, usuarios, correos y
empresas mencionados son simulados con fines educativos.

---

## ¿Qué incluye el proyecto?

- **Portal público** con información institucional, catálogo de planes y
  un resumen de gobierno, riesgo y cumplimiento (GRC).
- **Autenticación** con inicio de sesión, roles y permisos.
- **Panel de usuario (dashboard)** con datos de cuenta y bitácora de
  seguridad, según el rol.
- **Gestión de usuarios y roles** (solo administradores).
- **Sección de Gobierno de TI**: matrices de activos, riesgos y controles,
  y su marco regulatorio.
- **Backend real** en **Laravel**, respaldado por **MySQL**, corriendo en
  contenedores **Docker** detrás de **Nginx** — autenticación JWT, hashing
  de contraseñas con PBKDF2 y control de acceso por rol aplicado también
  del lado del servidor.
- **Documentación de seguridad** explicando cada decisión de diseño y sus
  límites.

> **Nota sobre versiones:** este proyecto tuvo una primera versión del
> backend en ASP.NET Core + SQL Server (documentada en
> `backend-dotnet-legacy/` si la conservaron, o en el historial de Git).
> La versión vigente, descrita en este README, es la de **Docker + Nginx +
> Laravel + MySQL**, migrada para cumplir con la arquitectura definida en
> la propuesta del proyecto.

---

## Estructura del proyecto

```
perroniphone/
├── index.html              # Portal público (organización, planes, seguridad, gobierno)
├── login.html               # Formulario de acceso
├── dashboard.html            # Panel de usuario autenticado
├── admin.html                # Gestión de usuarios y roles (solo administrador)
├── gobierno.html             # Detalle de gobierno de TI / GRC
├── security.html             # Documentación de seguridad (versión web)
├── SECURITY.md                # Documentación de seguridad (versión Markdown)
├── MIGRACION-LARAVEL.md       # Guía detallada de la migración a Docker/Laravel/MySQL
├── docker-compose.yml         # Orquesta los 4 contenedores
├── Dockerfile                 # Imagen del contenedor "app" (Laravel)
├── .gitignore
│
├── assets/
│   ├── css/
│   │   └── styles.css        # Sistema de diseño del portal
│   └── js/
│       ├── api.js             # Cliente HTTP hacia la API (fetch) — apunta a http://localhost:8080/api
│       ├── auth.js            # Sesión, permisos por rol, utilidades de seguridad
│       └── grc.js             # Datos de activos/riesgos/controles (gobierno de TI)
│
├── docs/                      # Análisis de gobierno, riesgo y cumplimiento
│   ├── 02-gobierno-ti.md
│   ├── 04-matriz-activos.md
│   ├── 05-matriz-riesgos.md
│   └── 06-matriz-controles.md
│
├── docker/
│   ├── entrypoint.sh           # Prepara .env, corre migraciones/seeders al arrancar
│   └── nginx/default.conf      # Config de nginx (proxy hacia PHP-FPM)
│
└── laravel-app/                # Código de la aplicación Laravel
    ├── app/Http/Controllers/Api/   # AuthController, UsuariosController, BitacoraController, PlanesController
    ├── app/Http/Middleware/EnsureRole.php
    ├── app/Models/                  # Usuario, Plan, Bitacora, Permiso
    ├── app/Services/PasswordHasher.php   # PBKDF2 (mismo algoritmo que la v1)
    ├── bootstrap/app.php
    ├── config/ (auth.php, cors.php)
    ├── database/migrations/         # Esquema (usuarios, permisos, planes, bitacora)
    ├── database/seeders/DatabaseSeeder.php   # Datos ficticios (idempotente)
    ├── routes/api.php
    └── .env.example
```

**Nota:** el esqueleto completo de Laravel (`vendor/`, `artisan`,
`public/index.php`, etc.) no vive en el repositorio — se genera solo la
primera vez que se construye la imagen de Docker (`docker compose build`),
porque requiere descargar el framework desde internet.

---

## Arquitectura

```
Navegador (HTML/CSS/JS)  →  Nginx (:8080)  →  Laravel/PHP-FPM  →  MySQL
                                                                  ↑
                                             phpMyAdmin (:8081) ─┘
```

El frontend es completamente estático y se comunica con el backend
mediante `fetch` (ver `assets/js/api.js`). El backend expone endpoints
REST protegidos con JWT y control de acceso por rol
(middleware `EnsureRole`), y persiste todos los datos en MySQL.

Los 4 contenedores (`nginx`, `app`, `mysql`, `phpmyadmin`) se levantan
juntos con Docker Compose.

---

## Cómo correrlo

Instrucciones completas, comandos y solución de problemas comunes en
[`MIGRACION-LARAVEL.md`](MIGRACION-LARAVEL.md). En resumen:

```bash
docker compose build     # solo la primera vez (o si cambia el Dockerfile)
docker compose up -d
```

Luego abre `index.html` con **Live Server** en VS Code (no con doble
clic — el navegador bloquea las peticiones hacia la API desde `file://`).

---

## Cuentas del equipo

| Correo | Contraseña | Rol |
|---|---|---|
| rafa@perroniphone.edu.mx | Rafa#2026 | administrador |
| simon@perroniphone.edu.mx | Simon#2026 | administrador |
| joshua@perroniphone.edu.mx | Joshua#2026 | soporte |
| carlos@perroniphone.edu.mx | Carlos#2026 | cliente |
| ivan@perroniphone.edu.mx | Ivan#2026 | cliente (cuenta bloqueada) |

---

## Verificar la base de datos

Entra a **phpMyAdmin** en `http://localhost:8081` (usuario
`perroniphone_user` / `perroniphone_pass`, o `root` /
`perroniphone_root_pass` para ver todo). Deben aparecer las tablas
`usuarios`, `planes`, `bitacora` y `permisos` ya con los datos ficticios
cargados.

---

## Seguridad

El detalle completo de cada decisión de seguridad (hashing de contraseñas,
JWT, control de acceso por rol, bloqueo por intentos fallidos, protección
contra inyección SQL, etc.) está documentado en:

- [`security.html`](security.html) — versión navegable dentro del sitio
- [`SECURITY.md`](SECURITY.md) — versión Markdown

---

## Gobierno de TI (GRC)

La sección de Gobierno del portal (`#gobierno` en la página de inicio, y
`gobierno.html` para el detalle) presenta el análisis de activos, riesgos
y controles de la organización ficticia, junto con su marco regulatorio
(IFT, INAI, PROFECO, SEDECYT). El análisis completo está en la carpeta
[`docs/`](docs/).

---

## Aviso

Este es un proyecto académico. La compañía, los usuarios, las
contraseñas, los riesgos y cualquier otro dato mostrado son **completamente
ficticios** y se usan únicamente con fines de aprendizaje.

## Integrantes

- Covarrubias Delgado Ángel Iván
- Carrillo Lima Rafael Alberto
- Arenas Maciel Carlos Saúl
- Rodríguez Acosta Joshua Rafael
- Vela Puebla Simón
