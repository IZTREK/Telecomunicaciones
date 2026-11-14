# PerroniPhone — Portal corporativo académico

Portal corporativo de **PerroniPhone Comunicaciones**, una compañía de
telecomunicaciones **100% ficticia**, creado como proyecto académico de
ciberseguridad y gobierno de TI. Todos los datos, usuarios, correos y
empresas mencionados son simulados con fines educativos.

---

## ¿Qué incluye el proyecto?

- **Portal público** con información institucional, catálogo de planes y
  un resumen de gobierno, riesgo y cumplimiento (GRC).
- **Autenticación** con JWT, hashing PBKDF2, bloqueo por intentos fallidos
  y límite de peticiones por IP.
- **Control de acceso por permisos (RBAC)** aplicado en el servidor, con
  una sola matriz de permisos para backend y frontend.
- **Panel de usuario (dashboard)** con datos de cuenta y bitácora de
  seguridad, según el rol.
- **Gestión de usuarios y roles**, **Contabilidad** (datos ficticios) y
  **Gobierno de TI** (matrices de activos, riesgos y controles).
- **Backend** en **Laravel 11** + **MySQL 8**, en contenedores **Docker**
  detrás de **Nginx**.
- **Documentación de seguridad** explicando cada decisión de diseño y sus
  límites ([`SECURITY.md`](SECURITY.md)).

---

## Cómo correrlo

### Requisitos

| Necesitas | Para qué | Notas |
|---|---|---|
| **Docker Desktop** | Correr los 4 contenedores | Windows: activa *WSL 2* cuando el instalador lo pida. macOS/Linux: instalación normal. Debe estar **abierto** (ícono de la ballena activo) antes de usar `docker`. |
| **Git** | Clonar el repositorio | — |
| **Node.js** (opcional) | Solo para `tools/validar-matrices.js` | No lo necesita el portal. |

No hace falta instalar PHP, Composer, MySQL ni Live Server: todo vive dentro
de Docker.

### Paso a paso (primera vez)

1. **Clona el repositorio** y entra a la carpeta:

   ```bash
   git clone https://github.com/IZTREK/Telecomunicaciones.git
   cd Telecomunicaciones
   ```

2. **Construye y levanta los contenedores**:

   ```bash
   docker compose up -d --build
   ```

   La primera vez tarda varios minutos (descarga PHP, compila extensiones e
   instala Laravel). Las siguientes veces tarda segundos.

3. **Espera a que Laravel termine de arrancar**:

   ```bash
   docker compose logs -f app
   ```

   Verás que crea el `.env`, genera las llaves, corre las migraciones y
   carga los datos de ejemplo. Está listo cuando aparece:

   ```
   [entrypoint] Listo. Arrancando PHP-FPM...
   ```

   Presiona `Ctrl + C` para dejar de ver los logs (los contenedores siguen
   corriendo).

4. **Abre http://localhost:8080** en el navegador e inicia sesión con una de
   las [cuentas del equipo](#cuentas-del-equipo).

5. Para comprobar que todo está arriba:

   ```bash
   docker compose ps
   ```

   Deben aparecer `perroniphone_nginx`, `perroniphone_app`,
   `perroniphone_mysql` (con `healthy`) y `perroniphone_phpmyadmin`.

### ¿Qué pasa cada vez que arranca?

`docker/entrypoint.sh` corre automáticamente en el contenedor `app`:

1. Crea `.env` a partir de `laravel-app/.env.example` (si no existe).
2. Escribe en `.env` los datos de conexión a MySQL de `docker-compose.yml`.
3. Genera `APP_KEY` y `JWT_SECRET` propias de ese contenedor.
4. Corre las migraciones pendientes (`php artisan migrate`).
5. Corre el seeder: sincroniza la matriz de permisos y carga los datos de
   ejemplo **solo en tablas vacías** (nunca pisa lo que hayas cambiado desde
   el portal).
6. Arranca PHP-FPM.

| Servicio | URL | Notas |
|---|---|---|
| Portal + API | http://localhost:8080 | La API vive en `/api/*` |
| phpMyAdmin | http://localhost:8081 | `perroniphone_user` / `perroniphone_pass` (o `root` / `perroniphone_root_pass`) |
| MySQL | `127.0.0.1:3307` | Para un cliente externo (DBeaver, Workbench…) |

### Comandos del día a día

```bash
docker compose up -d --build   # levantar (y reconstruir si cambió código PHP)
docker compose down            # apagar todo (los datos de MySQL se conservan)
docker compose logs -f app     # ver qué hace Laravel en vivo
docker compose exec app bash   # terminal DENTRO del contenedor de Laravel
docker compose down -v         # apagar y BORRAR la base (se vuelve a sembrar al levantar)
```

- **Cambios en HTML/CSS/JS** (`laravel-app/public/`): se ven al recargar el
  navegador, no hace falta reconstruir.
- **Cambios en PHP** (`laravel-app/app`, `routes`, `database`…): corre
  `docker compose up -d --build`.
- Al recrear el contenedor `app` se generan llaves nuevas, así que las
  sesiones abiertas se cierran y hay que volver a iniciar sesión.

### Compartirlo por internet (ngrok)

Frontend y API comparten origen, así que basta con exponer el puerto 8080:

```bash
ngrok http 8080
```

No hay que tocar ninguna URL ni configuración de CORS. Solo se expone el
portal: MySQL y phpMyAdmin escuchan únicamente en tu máquina. Apaga el túnel
cuando no lo estén usando, porque las contraseñas de demostración son públicas.

### Actualizar tu copia después de un merge

```bash
git checkout main
git pull
docker compose up -d --build
```

Las migraciones nuevas se aplican solas sobre tu base existente. Si quieres
además las cuentas y datos de ejemplo actuales, borra la base primero con
`docker compose down -v`.

### Problemas comunes

| Síntoma | Solución |
|---|---|
| `Cannot connect to the Docker daemon` / `failed to connect to the docker API` | Docker Desktop no está abierto. Ábrelo y espera a que diga *Engine running*. |
| `port is already allocated` (8080, 8081 o 3307) | Otro programa usa ese puerto. Ciérralo o cambia el número de la izquierda en `docker-compose.yml` (p. ej. `"8090:80"`) y entra por ese puerto. |
| El portal dice que no pudo conectar con la API | El contenedor `app` aún está arrancando: revisa `docker compose logs -f app`. |
| `docker compose logs app` muestra un error y el contenedor se reinicia | Copia el error; casi siempre se resuelve con `docker compose down -v` y `docker compose up -d --build`. |
| Iniciaste sesión y te regresa al login | La sesión expiró (20 min) o se recreó el contenedor (llaves nuevas). Vuelve a entrar. |
| "Correo o contraseña incorrectos" con una cuenta del README | Tu base es de una versión anterior (otros usuarios/roles) o la cuenta se bloqueó 2 min por 5 intentos fallidos. Espera 2 min o usa `docker compose down -v`. |
| "Demasiadas solicitudes de inicio de sesión" | Más de 20 intentos de login en un minuto desde tu IP. Espera un minuto. |
| Cambié código PHP y no se ve el cambio | Corre `docker compose up -d --build` (los cambios en HTML/CSS/JS no lo necesitan). |
| En Windows: `entrypoint.sh: not found` o `\r: command not found` | El repo se clonó con saltos de línea de Windows. El `Dockerfile` ya los corrige; asegúrate de reconstruir con `--build`. |
| Quiero volver a los datos de ejemplo originales | `docker compose down -v` y luego `docker compose up -d --build`. |

---

## Cuentas del equipo

| Correo | Contraseña | Rol | Estado |
|---|---|---|---|
| simon@perroniphone.edu.mx | Simon#2026 | administrador | activo |
| rafa@perroniphone.edu.mx | Rafa#2026 | redes | activo |
| carlos@perroniphone.edu.mx | Carlos#2026 | encargado | activo |
| ivan@perroniphone.edu.mx | Ivan#2026 | contabilidad | activo |
| joshua@perroniphone.edu.mx | Joshua#2026 | cliente | **bloqueado** (para demostrar el bloqueo) |

> Estas cuentas se crean solo en una base **vacía**. Si tu base es de una
> versión anterior, conserva los roles que tenía; corre `docker compose down -v`
> para empezar con estos.

### Roles y permisos

La matriz vive en un solo archivo,
[`laravel-app/database/data/permisos.json`](laravel-app/database/data/permisos.json).
El backend la usa para autorizar cada endpoint y el frontend la recibe al
iniciar sesión. Para cambiar un permiso, edita ese archivo y corre
`docker compose up -d --build`.

| Rol | Panel | Usuarios (ver/bloquear) | Cambiar roles | Bitácora | Gobierno (GRC) | Contabilidad |
|---|:-:|:-:|:-:|:-:|:-:|:-:|
| administrador | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| encargado | ✅ | ✅ | — | — | ✅ | ✅ |
| redes | ✅ | — | — | — | ✅ | — |
| contabilidad | ✅ | — | — | — | — | ✅ |
| soporte | ✅ | — | — | ✅ | — | — |
| cliente | ✅ | — | — | — | — | — |

Además, nadie puede modificar su propia cuenta y solo un administrador puede
modificar a otro administrador, así que siempre queda al menos un
administrador activo.

**Agregar un rol o permiso nuevo:**

1. Edita `laravel-app/database/data/permisos.json` (el rol es la llave; sus
   permisos, la lista).
2. Si es un permiso nuevo, protégelo en `laravel-app/routes/api.php` con
   `->middleware('permiso:nombre_del_permiso')` y, en el frontend, usa
   `PERRONI_AUTH.requerirSesion("nombre_del_permiso")` o
   `data-permiso="nombre_del_permiso"` en el enlace del menú.
3. Corre `docker compose up -d --build`. El rol aparece solo en el selector
   de "Usuarios y roles".

### Seguridad, en resumen

| Control | Dónde vive |
|---|---|
| Contraseñas con PBKDF2 (100,000 iteraciones) + salt | `app/Services/PasswordHasher.php` |
| Mismo mensaje y mismo tiempo de respuesta exista o no el correo | `AuthController::login` |
| Bloqueo de 2 min tras 5 intentos fallidos (atómico) | `AuthController::registrarIntentoFallido` |
| Máx. 20 intentos de login por minuto por IP | `app/Providers/AppServiceProvider.php` |
| Sesión con JWT firmado (20 min) | paquete `jwt-auth`, `config/auth.php` |
| Permiso exigido en cada endpoint | `EnsurePermission` + `routes/api.php` |
| Cuenta bloqueada = token inválido de inmediato | `EnsureCuentaActiva` |
| Bitácora de accesos, bloqueos y cambios de rol | tabla `bitacora` |
| Escape de HTML contra XSS | `PERRONI_AUTH.escapeHTML` |

El detalle (decisiones, razones y límites) está en [`SECURITY.md`](SECURITY.md).

---

## Arquitectura

```
Navegador ──► Nginx (:8080) ──┬─► archivos estáticos (laravel-app/public)
                              └─► /api/* ─► Laravel / PHP-FPM ─► MySQL
                                                                  ▲
                                         phpMyAdmin (:8081) ──────┘
```

- **nginx** sirve el frontend directamente desde `laravel-app/public/` y
  reenvía `/api/*` a Laravel.
- **app** (Laravel) expone la API REST protegida con JWT y permisos.
- **mysql** guarda los datos en el volumen `perroniphone_mysql_data`.
- Al arrancar, `docker/entrypoint.sh` genera las llaves, corre las
  migraciones y siembra los datos de ejemplo (sin pisar datos existentes).

### Recorrido de una petición (ejemplo: un administrador cambia un rol)

1. En `admin.html` se elige un rol nuevo; `PERRONI_API.actualizarRol()`
   envía `PUT /api/usuarios/{id}/rol` con el JWT en `Authorization: Bearer`.
2. Nginx recibe la petición en `:8080` y, como empieza con `/api`, la pasa a
   Laravel (PHP-FPM).
3. Laravel aplica, en orden: `auth:api` (firma y vigencia del JWT),
   `cuenta.activa` (la cuenta no está bloqueada) y `permiso:editar_roles`
   (el rol actual tiene ese permiso en la tabla `permisos`).
4. `UsuariosController::actualizarRol` valida el rol contra la matriz,
   revisa que no te modifiques a ti mismo ni a un administrador sin serlo,
   guarda en MySQL y escribe en la bitácora.
5. Responde `204`; la página vuelve a pedir el directorio para mostrar el
   estado real. Si algo falla, la API responde `{ "mensaje": "..." }` y la
   página lo muestra.

### Endpoints de la API

| Método y ruta | Acceso |
|---|---|
| `POST /api/auth/login` | Público (máx. 20 por minuto por IP) |
| `GET /api/planes` | Público |
| `GET /api/usuarios` · `GET /api/permisos` · `PUT /api/usuarios/{id}/estado` | `gestionar_usuarios` |
| `PUT /api/usuarios/{id}/rol` | `editar_roles` |
| `GET /api/bitacora` | `ver_bitacora` |
| `GET /api/contabilidad` | `ver_contabilidad` |

---

## Estructura del proyecto

```
Telecomunicaciones/
├── docker-compose.yml          # Orquesta los 4 contenedores
├── Dockerfile                  # Imagen del contenedor "app" (Laravel)
├── docker/
│   ├── entrypoint.sh           # Llaves, migraciones y seeders al arrancar
│   └── nginx/default.conf      # Frontend estático + proxy de /api a PHP-FPM
├── laravel-app/                # Código propio, se copia sobre un Laravel limpio
│   ├── app/
│   │   ├── Http/Controllers/Api/   # Auth, Usuarios, Permisos, Bitacora, Planes, Contabilidad
│   │   ├── Http/Middleware/        # EnsurePermission, EnsureCuentaActiva
│   │   ├── Models/                 # Usuario, Permiso, Plan, Bitacora, Contabilidad
│   │   ├── Providers/AppServiceProvider.php   # Límite de peticiones del login
│   │   └── Services/PasswordHasher.php        # PBKDF2
│   ├── bootstrap/app.php       # Rutas, middlewares, proxies de confianza
│   ├── config/                 # auth.php (guard JWT), cors.php
│   ├── database/
│   │   ├── data/permisos.json  # ÚNICA matriz de roles y permisos
│   │   ├── migrations/
│   │   └── seeders/DatabaseSeeder.php
│   ├── routes/api.php
│   ├── public/                 # Frontend (lo sirve nginx)
│   │   ├── index.html  login.html  dashboard.html  admin.html
│   │   ├── gobierno.html  contabilidad.html  security.html
│   │   └── assets/ (css/styles.css, js/api.js, js/auth.js, js/grc.js)
│   └── .env.example            # Plantilla; el .env real lo crea el contenedor
├── docs/                       # Análisis de gobierno, riesgo y cumplimiento
│   ├── 01-contexto.md … 06-matriz-controles.md
│   └── avance-01-organizacion.md
├── tools/validar-matrices.js   # Verifica la coherencia de las matrices GRC
├── SECURITY.md                 # Decisiones de seguridad
└── MIGRACION-LARAVEL.md        # Qué cambió al migrar de ASP.NET Core a Laravel
```

El esqueleto completo de Laravel (`vendor/`, `artisan`,
`public/index.php`, …) no vive en el repositorio: el `Dockerfile` lo genera
al construir la imagen y copia encima el código de `laravel-app/`.

---

## Verificación de las matrices GRC

```bash
node tools/validar-matrices.js
```

Comprueba que niveles de riesgo, referencias cruzadas entre activos, riesgos
y controles, y la criticidad de cada activo sean coherentes (requiere Node.js).

---

## Gobierno de TI (GRC)

La sección de Gobierno del portal (`#gobierno` en la página de inicio, y
`gobierno.html` para el detalle) presenta el análisis de activos, riesgos
y controles de la organización ficticia, junto con su marco regulatorio
(IFT, INAI, PROFECO, SEDECYT). El análisis completo está en [`docs/`](docs/).

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
