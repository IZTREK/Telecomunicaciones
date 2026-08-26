# PerroniPhone — Proyecto de Ciberseguridad

Proyecto de la materia de **Ciberseguridad** de la Universidad Autónoma de
Aguascalientes. Analiza la postura de seguridad de **PerroniPhone
Comunicaciones**, una empresa **ficticia** del sector de telecomunicaciones, y
la materializa en dos entregables complementarios:

1. **La documentación de gobierno, riesgo y cumplimiento (GRC)** — el análisis
   escrito: contexto, gobierno de TI, alineación estratégica y las matrices de
   activos, riesgos y controles.
2. **El portal corporativo** — un sitio estático (HTML + CSS + JavaScript) que
   implementa los controles de seguridad descritos en la documentación:
   autenticación con hash, control de acceso por rol, bloqueo por intentos y
   bitácora de auditoría.

> **Aviso**: la empresa, los usuarios, los correos, las contraseñas y todos los
> datos de este repositorio son ficticios y existen únicamente con fines
> educativos. No hay backend ni datos reales detrás del portal.

## Documentación del proyecto

| # | Documento | Contenido |
|---|---|---|
| 1 | [Contexto del proyecto](docs/01-contexto.md) | Información general de la organización, contexto tecnológico y alcance del análisis. |
| 2 | [Gobierno de TI](docs/02-gobierno-ti.md) | Principios rectores, marco regulatorio (IFT, INAI, PROFECO, SEDECYT), comités de decisión, gestión de riesgos y KPI de auditoría. |
| 3 | [Alineación estratégica](docs/03-alineacion-estrategica.md) | Cómo cada iniciativa de TI sostiene los procesos de facturación y soporte técnico. |
| 4 | [Matriz de activos](docs/04-matriz-activos.md) | Inventario A-01 a A-04 con tipo, criticidad y responsable. |
| 5 | [Matriz de riesgos](docs/05-matriz-riesgos.md) | Riesgos R-01 a R-03 con amenaza, vulnerabilidad, probabilidad, impacto y nivel. |
| 6 | [Matriz de controles](docs/06-matriz-controles.md) | Controles C-01 a C-0n, su tipo y el riesgo que mitigan. |
| — | [Decisiones de seguridad del portal](SECURITY.md) | Qué control implementa el código del portal, por qué y cuáles son sus límites. |

## El portal

| Página | Descripción | Acceso |
|---|---|---|
| [`index.html`](index.html) | Portal público: organización, planes y resumen de controles. | Público |
| [`login.html`](login.html) | Autenticación con hash SHA-256 + salt, token CSRF y bloqueo por intentos. | Público |
| [`dashboard.html`](dashboard.html) | Panel de usuario y bitácora de seguridad según el rol. | Requiere sesión |
| [`admin.html`](admin.html) | Gestión de usuarios, roles y bloqueo de cuentas. | Solo `administrador` |

### Cómo ejecutarlo

El portal es estático y no requiere instalación. Basta con abrir `index.html`
en el navegador. Para que el módulo de autenticación funcione con la Web Crypto
API conviene servirlo por HTTP en lugar de abrirlo como archivo local:

```bash
# Con Python (cualquier sistema)
python -m http.server 8080

# Con Node.js
npx serve .
```

Después, abrir <http://localhost:8080>.

### Cuentas de demostración

| Correo | Contraseña | Rol | Estado |
|---|---|---|---|
| `admin@perroniphone.edu.mx` | `Admin#2026` | administrador | activo |
| `soporte@perroniphone.edu.mx` | `Soporte#2026` | soporte | activo |
| `cliente@perroniphone.edu.mx` | `Cliente#2026` | cliente | activo |
| `emilio.farias@perroniphone.edu.mx` | `Cliente#2026` | cliente | bloqueado |

La última cuenta existe a propósito para demostrar el estado "bloqueado" y el
mensaje de error correspondiente.

## Estructura del repositorio

```
.
├── docs/                  Documentación GRC del proyecto
├── assets/
│   ├── css/styles.css     Sistema de diseño del portal
│   └── js/
│       ├── db.js          Base de datos ficticia (usuarios, roles, planes)
│       └── auth.js        Módulo de autenticación y controles de seguridad
├── index.html             Portal público
├── login.html             Inicio de sesión
├── dashboard.html         Panel de usuario
├── admin.html             Gestión de usuarios y roles
└── SECURITY.md            Decisiones de seguridad del portal
```

## Integrantes

- Covarrubias Delgado Ángel Iván
- Carrillo Lima Rafael Alberto
- Arenas Maciel Carlos Saúl
- Rodríguez Acosta Joshua Rafael
- Vela Puebla Simón
