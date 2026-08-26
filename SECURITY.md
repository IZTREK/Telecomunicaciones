# Documentación de decisiones de seguridad — Portal PerroniPhone

Este documento explica **qué** controles de seguridad se implementaron en el
portal académico de PerroniPhone, **por qué** se eligieron y **cuáles son sus
límites** al tratarse de un sitio estático (sin servidor real). El objetivo
es que la implementación sea evaluable como ejercicio de seguridad
aplicada, no solo como interfaz visual.

> **Alcance del proyecto**: HTML + CSS + JavaScript en el navegador, sin
> backend ni base de datos real. Todos los usuarios, correos y contraseñas
> son ficticios. Donde un control depende de infraestructura de servidor,
> se indica explícitamente cómo se resolvería en producción.

---

## 1. Autenticación

### 1.1 Contraseñas nunca en texto plano
- **Decisión**: cada usuario en `assets/js/db.js` almacena un `salt`
  aleatorio y un `hash` SHA-256 de `salt + contraseña`, calculado con
  antelación. El formulario de login (`login.html`) nunca compara la
  contraseña escrita contra un valor en texto plano: usa
  `crypto.subtle.digest("SHA-256", ...)` en `assets/js/auth.js` y compara
  hashes.
- **Por qué**: aun en una demo, se busca modelar la práctica real de no
  guardar ni transmitir contraseñas legibles.
- **Límite conocido**: SHA-256 puro es rápido de fuerza-bruta comparado con
  funciones diseñadas para contraseñas. En producción se usaría **bcrypt,
  scrypt o Argon2** en el servidor, con el cómputo del hash ocurriendo del
  lado servidor, nunca en el cliente (aquí ocurre en el cliente porque no
  existe servidor).

### 1.2 Mensajes de error genéricos
- **Decisión**: si el correo no existe o la contraseña es incorrecta, se
  muestra el mismo mensaje ("Correo o contraseña incorrectos").
- **Por qué**: evita que un atacante pueda enumerar qué correos están
  registrados en el sistema (*user enumeration*).

### 1.3 Bloqueo por intentos fallidos (mitigación de fuerza bruta)
- **Decisión**: tras 5 intentos fallidos sobre el mismo correo, la cuenta
  se bloquea temporalmente 2 minutos (`registrarIntentoFallido` en
  `auth.js`). Además, el directorio de usuarios incluye una cuenta ya
  bloqueada de forma permanente (`emilio.farias@perroniphone.edu.mx`) para
  demostrar el estado "bloqueado" administrado manualmente.
- **Límite conocido**: el conteo vive en `sessionStorage` del navegador,
  por lo que un atacante podría evadirlo abriendo una pestaña nueva. En
  producción, el conteo debe llevarse en el servidor, idealmente por
  combinación de cuenta + dirección IP, con *rate limiting* a nivel de
  infraestructura (por ejemplo, un WAF o middleware dedicado).

---

## 2. Gestión de sesión

- **Decisión**: al autenticarse, se genera un objeto de sesión
  (`id`, `rol`, `correo`, tiempos de emisión/expiración) codificado en
  Base64 y guardado en `sessionStorage`, con expiración de 20 minutos.
  Cada página protegida llama a `requerirSesion()` para validar que exista
  una sesión vigente antes de mostrar contenido.
- **Por qué `sessionStorage` y no `localStorage`**: `sessionStorage` se
  destruye al cerrar la pestaña/navegador, reduciendo la ventana de
  exposición frente a `localStorage`, que persiste indefinidamente.
- **Límite conocido**: este token **no está firmado criptográficamente**;
  cualquier persona con acceso a las herramientas de desarrollador del
  navegador podría editarlo. Esto es aceptable únicamente porque no hay
  datos reales ni un backend que confíe en ese token para operaciones
  sensibles. En producción, la sesión debe representarse con una
  **cookie `httpOnly`, `Secure` y `SameSite=Strict`**, o un JWT firmado
  por el servidor y validado en cada petición, nunca confiando en un
  valor que el propio cliente puede modificar.

---

## 3. Control de acceso basado en roles (RBAC)

- **Decisión**: existen tres roles — `administrador`, `soporte` y
  `cliente` — cada uno con una lista explícita de permisos en
  `PERRONI_DB.permisos`. `dashboard.html` oculta secciones (por ejemplo,
  la bitácora de seguridad) si el rol no tiene el permiso correspondiente,
  y `admin.html` llama a `requerirSesion(["administrador"])`, redirigiendo
  a cualquier otro rol.
- **Por qué**: el principio de **mínimo privilegio** — cada rol ve y hace
  solo lo necesario para su función.
- **Límite conocido**: al ser validación en el cliente, es "seguridad de
  interfaz", no una barrera real: alguien podría abrir `admin.html`
  directamente y, aunque será redirigido por el script, un backend real
  debe **revalidar el permiso en cada endpoint del servidor**, no confiar
  en que el frontend oculte los botones.

---

## 4. Protección contra XSS (Cross-Site Scripting)

- **Decisión**: toda información dinámica que proviene de la
  "base de datos" (nombres, correos, roles) se inserta en el DOM a través
  de la función `escapeHTML()`, que convierte caracteres como `<`, `>` y
  `"` en sus entidades HTML antes de escribirlos.
- **Por qué**: aunque los datos son ficticios y fijos, se modela la
  disciplina de **nunca insertar texto no controlado directamente en el
  HTML**, que es la causa más común de XSS en aplicaciones reales.

---

## 5. Protección conceptual contra CSRF

- **Decisión**: el formulario de login incluye un campo oculto
  `csrf_token`, generado con `crypto.randomUUID()` y almacenado en
  `sessionStorage`. Al enviar el formulario, `auth.js` valida que el token
  del formulario coincida con el guardado, y genera uno nuevo tras cada
  intento.
- **Por qué**: se busca representar el patrón de un token anti-CSRF por
  formulario, aunque en este proyecto no exista un servidor que reciba
  peticiones de estado (no hay operación que un atacante externo pudiera
  forjar).
- **Límite conocido**: un token CSRF solo tiene sentido de protección real
  cuando lo **emite y valida el servidor** en cada envío de formulario que
  modifique estado. Aquí es un ejercicio didáctico del patrón, no una
  protección efectiva por sí sola.

---

## 6. Validación de entradas

- **Decisión**: el correo se valida con una expresión regular antes de
  intentar autenticar (`validarFormatoCorreo`), y la contraseña recibe
  retroalimentación de fuerza en tiempo real (`fuerzaPassword`) sin
  bloquear el envío, para no filtrar la política exacta de contraseñas a
  un posible atacante.
- **Por qué**: reduce solicitudes malformadas y ayuda a que las cuentas de
  demostración usen contraseñas razonablemente robustas.

---

## 7. Bitácora de seguridad (auditoría)

- **Decisión**: `PERRONI_DB.bitacora` simula un registro de eventos
  (accesos correctos, intentos fallidos, bloqueos, cambios de rol),
  visible solo para los roles `soporte` y `administrador` dentro del
  dashboard.
- **Por qué**: la trazabilidad es un control de seguridad en sí mismo —
  permite detectar y explicar incidentes después de que ocurren.
- **Límite conocido**: es un arreglo estático de ejemplo; en producción
  cada evento (login, cambio de rol, bloqueo) debe escribirse en un
  registro de auditoría del lado del servidor, con marca de tiempo,
  dirección IP y usuario responsable, protegido contra modificación.

---

## 8. Resumen de límites generales de esta implementación

Por ser un proyecto **100% del lado del cliente**, ningún control aquí
sustituye una implementación real de backend. La tabla resume el
paralelismo entre lo simulado y lo que correspondería en producción:

| Control simulado en el portal        | Equivalente recomendado en producción              |
|---------------------------------------|------------------------------------------------------|
| Hash SHA-256 + salt en el navegador   | bcrypt/Argon2 calculado y verificado en el servidor  |
| Token de sesión en `sessionStorage`   | Cookie `httpOnly` + `Secure` + `SameSite`, o JWT firmado |
| Bloqueo por intentos en el cliente    | *Rate limiting* por cuenta + IP en el servidor/WAF   |
| RBAC que oculta botones en el DOM     | Autorización revalidada en cada endpoint del backend |
| Token CSRF simulado                   | Token CSRF emitido y validado por el servidor        |
| Bitácora estática de ejemplo          | Registro de auditoría persistente y protegido        |

Este enfoque permite demostrar el **razonamiento y las decisiones de
diseño de seguridad** propias de un portal corporativo, dentro de las
restricciones de un proyecto académico sin infraestructura de servidor.
