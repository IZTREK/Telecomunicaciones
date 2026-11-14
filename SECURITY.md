# Documentación de decisiones de seguridad — Portal PerroniPhone

Este documento explica **qué** controles de seguridad implementa el portal
académico de PerroniPhone, **por qué** se eligieron y **cuáles son sus
límites**. La versión navegable está en
[`laravel-app/public/security.html`](laravel-app/public/security.html)
(`http://localhost:8080/security.html` con los contenedores arriba).

> **Alcance**: frontend HTML/CSS/JS + API REST en **Laravel 11 (PHP 8.3)** +
> **MySQL 8**, detrás de **Nginx**, todo en **Docker Compose**. Todos los
> usuarios, correos y contraseñas son ficticios.

> **Historia**: la v1 era 100% del lado del cliente; la v2 agregó un backend
> en ASP.NET Core + SQL Server; la versión actual (v3) migró a
> Laravel + MySQL. Las referencias a la v1/v2 quedan en el historial de Git.

---

## 1. Autenticación

### 1.1 Contraseñas nunca en texto plano
- **Decisión**: cada fila de `usuarios` guarda un `salt` aleatorio de 16
  bytes y un `hash_password` derivado con **PBKDF2-HMAC-SHA256, 100,000
  iteraciones** (`app/Services/PasswordHasher.php`). La comparación usa
  `hash_equals()` (tiempo constante, evita *timing attacks*).
- **Por qué**: PBKDF2 es lento a propósito, lo que encarece la fuerza bruta
  y los diccionarios si alguien obtuviera la base.
- **Límite**: hoy se prefiere **Argon2id** o **bcrypt** (`Hash::make()` de
  Laravel). Se conservó PBKDF2 por continuidad con la v2.

### 1.2 Sin enumeración de usuarios
- **Decisión** (`AuthController::login`): si el correo no existe, la
  contraseña es incorrecta **o la cuenta está bloqueada**, la respuesta es
  siempre `401 "Correo o contraseña incorrectos."`. El motivo real
  (cuenta bloqueada, bloqueo temporal) solo se explica cuando la contraseña
  es correcta.
- **Tiempo constante**: para correos inexistentes se calcula igualmente un
  PBKDF2 contra un salt/hash de relleno, así la respuesta tarda lo mismo
  exista o no la cuenta.
- **Por qué**: un atacante no puede averiguar qué correos están registrados
  ni por el mensaje, ni por el código HTTP, ni midiendo el tiempo.

### 1.3 Bloqueo por intentos fallidos (por cuenta)
- **Decisión**: al 5º intento fallido la cuenta se bloquea **2 minutos**
  (`bloqueado_hasta`). Durante el bloqueo los intentos no cuentan; al
  vencer, el contador vuelve a cero.
- **Atomicidad**: el incremento se hace dentro de una transacción con
  `SELECT … FOR UPDATE`, así que peticiones simultáneas no pueden "perder"
  intentos y saltarse el límite.

### 1.4 Límite de peticiones (por IP)
- **Decisión**: `POST /api/auth/login` acepta **20 peticiones por minuto por
  IP** (`RateLimiter::for('login')` en `app/Providers/AppServiceProvider.php`).
- **Por qué**: el bloqueo por cuenta no frena a quien prueba muchas cuentas
  distintas; éste sí. También limita el costo de CPU de PBKDF2.
- **IP real**: Laravel solo confía en `X-Forwarded-For` cuando la petición
  viene de una red privada (nginx en Docker o el agente de ngrok), ver
  `trustProxies` en `bootstrap/app.php`. Nadie puede falsificar su IP
  mandando ese header desde internet.

---

## 2. Gestión de sesión (JWT)

- **Decisión**: al iniciar sesión la API emite un **JWT firmado** (HS256,
  paquete `php-open-source-saver/jwt-auth`) con vigencia de **20 minutos**
  (`JWT_TTL`). El navegador lo guarda en `sessionStorage` y lo manda en
  `Authorization: Bearer …`.
- **Llaves**: `APP_KEY` y `JWT_SECRET` **no están en el repositorio ni en la
  imagen de Docker**: `docker/entrypoint.sh` las genera al crear cada
  contenedor. Si el contenedor se recrea, los tokens anteriores dejan de ser
  válidos y el frontend manda al login automáticamente.
- **Cuenta bloqueada = sesión terminada**: el middleware `cuenta.activa`
  (`EnsureCuentaActiva`) revisa el estado en la base en cada petición; si
  un administrador bloquea una cuenta, su token deja de servir de inmediato
  aunque no haya expirado.
- **Por qué `sessionStorage`**: se borra al cerrar la pestaña, a diferencia
  de `localStorage`.
- **Límites**: no hay lista de revocación (cerrar sesión borra el token del
  navegador, pero el token sigue siendo válido hasta expirar) y un XSS
  podría leer `sessionStorage`. En producción convendría una cookie
  `httpOnly; Secure; SameSite=Strict` y *refresh tokens*.

---

## 3. Control de acceso (RBAC por permisos)

- **Una sola fuente de verdad**:
  [`laravel-app/database/data/permisos.json`](laravel-app/database/data/permisos.json)
  define qué permisos tiene cada rol. El seeder la sincroniza con la tabla
  `permisos` en cada arranque.
- **Backend (la barrera real)**: cada ruta exige un permiso con el
  middleware `permiso:<nombre>` (`EnsurePermission`), que consulta la tabla
  con el **rol actual de la base** (no el del token): un cambio de rol
  aplica en la siguiente petición.

  | Endpoint | Permiso |
  |---|---|
  | `GET /api/usuarios`, `GET /api/permisos`, `PUT /api/usuarios/{id}/estado` | `gestionar_usuarios` |
  | `PUT /api/usuarios/{id}/rol` | `editar_roles` |
  | `GET /api/bitacora` | `ver_bitacora` |
  | `GET /api/contabilidad` | `ver_contabilidad` |

- **Reglas sobre quién modifica a quién** (`UsuariosController`): nadie puede
  cambiar su propio rol ni bloquearse a sí mismo, y solo un administrador
  puede modificar a otro administrador. Juntas garantizan que **siempre
  quede al menos un administrador activo**.
- **Frontend (solo UI)**: el login devuelve la lista de permisos del
  usuario; las páginas la usan para ocultar enlaces y redirigir
  (`PERRONI_AUTH.requerirSesion("ver_grc")`), pero nunca es la barrera.
- **Límite**: el contenido de `gobierno.html` (matrices GRC) vive en
  `assets/js/grc.js`, un archivo público; la restricción de esa página es
  de interfaz. Es aceptable porque el análisis GRC también es público en
  `docs/`.

---

## 4. Inyección SQL
- **Decisión**: todas las consultas usan Eloquent / Query Builder, que
  envían parámetros enlazados (*prepared statements*). Nunca se concatena
  texto del usuario en SQL.

## 5. XSS (Cross-Site Scripting)
- **Decisión**: todo dato dinámico se inserta en el DOM a través de
  `PERRONI_AUTH.escapeHTML()`, que convierte `& < > " '` en entidades HTML.

## 6. CSRF
- **Decisión**: la API no usa cookies de sesión; la credencial viaja en el
  header `Authorization`, que un sitio externo no puede hacer que el
  navegador envíe solo. Por eso no se necesita token anti-CSRF.
- **CORS**: frontend y API comparten origen (`http://localhost:8080` o el
  túnel de ngrok), así que `config/cors.php` no permite ningún origen
  externo por defecto (`FRONTEND_ORIGINS` vacío).

## 7. Validación de entradas
- **Servidor**: el login exige `correo` con formato de email y máximo 150
  caracteres, y `password` de máximo 200. Roles y estados se validan contra
  listas blancas (los roles válidos son los de la matriz de permisos). Los
  IDs de las rutas solo aceptan números (`whereNumber('id')`).
- **Cliente**: el correo se valida con una expresión regular antes de
  enviarlo y se muestra la fuerza estimada de la contraseña (solo
  informativo).

## 8. Bitácora de seguridad (auditoría)
- **Decisión**: la tabla `bitacora` registra accesos correctos, intentos
  con correos inexistentes, bloqueos automáticos, cambios de rol y
  bloqueos/desbloqueos manuales con el código del responsable. Visible solo
  con el permiso `ver_bitacora`. Los mensajes se truncan a la longitud de la
  columna para que un dato largo nunca rompa el registro.
- **Límite**: no se guarda la IP de origen y los registros no son
  inmutables (un DBA podría editarlos).

## 9. Infraestructura (Docker)
- `.env` **no se versiona** (`.gitignore`); cada contenedor crea el suyo
  desde `.env.example` con llaves nuevas.
- `APP_DEBUG=false`: un error 500 responde un mensaje genérico, sin stack
  trace ni rutas internas.
- MySQL (`3307`) y phpMyAdmin (`8081`) solo escuchan en `127.0.0.1`: no son
  accesibles desde la red ni a través de ngrok. Solo se publica nginx
  (`8080`).
- Nginx bloquea archivos ocultos (`.env`, `.git`, …).

---

## 10. Resumen de límites conocidos

| Control actual | Mejora recomendada en producción |
|---|---|
| PBKDF2 (100k iteraciones) | Argon2id / bcrypt con `Hash::make()` |
| JWT en `sessionStorage`, sin revocación | Cookie `httpOnly` + `Secure` + `SameSite`, *refresh tokens* y lista de revocación |
| Rate limit por IP en la aplicación | Además, WAF / rate limit en el borde |
| Bitácora sin IP y editable | Registro inmutable con IP, enviado a un SIEM |
| Contraseñas de demo publicadas en el README | Alta de usuarios con contraseña temporal y cambio obligatorio |
| Laravel 11 sin soporte de seguridad | Actualizar a una versión con soporte vigente |
| HTTP en local | HTTPS obligatorio (ngrok ya lo da en el túnel público) |
