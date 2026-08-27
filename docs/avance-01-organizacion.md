# Avance 01 — Organización y estructura del proyecto integrador

**Materia:** Ciberseguridad · Universidad Autónoma de Aguascalientes
**Evidencia individual de:** Arenas Maciel Carlos Saúl
**Equipo:** Covarrubias Delgado Ángel Iván · Carrillo Lima Rafael Alberto ·
Arenas Maciel Carlos Saúl · Rodríguez Acosta Joshua Rafael · Vela Puebla Simón
**Repositorio del equipo:** <https://github.com/IZTREK/Telecomunicaciones>
**Fecha:** 27 de agosto de 2026

---

## 1. Propósito y alcance de este avance

Este documento corresponde a la primera etapa del Proyecto Integrador. Su
objetivo es **establecer las bases del proyecto, organizar el trabajo del
equipo y comenzar a conocer la organización que será analizada**. No pretende
presentar un proyecto terminado.

| Sí forma parte de este avance | Deliberadamente **no** forma parte |
|---|---|
| Identificación de la organización a analizar | Análisis de riesgos formal |
| Contexto tecnológico preliminar | Auditoría de seguridad |
| Organización y roles del equipo | Análisis forense |
| Estructura del proyecto y forma de trabajo | Pruebas técnicas u ofensivas |
| Plan de trabajo por etapas | Conclusiones o recomendaciones finales |

Los contenidos de la columna derecha se abordarán conforme la materia avance
sobre esos temas.

## 2. Declaración de supuestos académicos

La organización analizada, **PerroniPhone Comunicaciones**, es una **empresa
ficticia** construida por el equipo como caso de estudio. En consecuencia:

> **Ningún dato de este proyecto proviene de una organización real.** La
> totalidad de la información sobre PerroniPhone —su infraestructura, sus
> sistemas, sus usuarios, sus cifras y su estructura interna— constituye un
> **supuesto académico** elaborado por el equipo con fines didácticos, y así
> debe entenderse en todos los documentos del proyecto.

Lo único que no es supuesto es el **marco normativo mexicano** que se toma como
referencia (IFT, INAI/LFPDPPP, PROFECO), que sí corresponde a regulación
pública y verificable, aunque su aplicación a PerroniPhone sea hipotética.

Esta decisión —trabajar sobre una empresa ficticia en lugar de una real— se
tomó para poder modelar libremente escenarios de seguridad sin exponer ni
comprometer a ninguna organización existente. **Queda sujeta a confirmación del
docente**: si el proyecto requiere una organización real analizada solo con
fuentes públicas, el equipo reencuadraría el caso conservando la estructura de
trabajo aquí descrita.

## 3. Consideraciones éticas y de alcance técnico

El equipo se compromete a los siguientes límites, que se mantendrán durante
todo el semestre:

1. **No se realizarán pruebas, escaneos ni actividades de seguridad sobre
   sistemas reales de terceros.** Ninguna.
2. Las prácticas técnicas se ejecutarán únicamente sobre el **prototipo propio
   del equipo**, alojado en el repositorio del proyecto y ejecutado en local.
3. Los usuarios, correos y contraseñas del prototipo son **ficticios y de
   demostración**; no corresponden a personas ni cuentas reales.
4. Cualquier afirmación que no pueda comprobarse con fuente pública se marcará
   explícitamente como supuesto académico, como se hizo en la sección 2.

## 4. La organización analizada: PerroniPhone Comunicaciones

### 4.1 Perfil general

| Campo | Valor | Naturaleza |
|---|---|---|
| Razón social | PerroniPhone Comunicaciones S.A. de C.V. | Supuesto académico |
| Sector | Telecomunicaciones | Supuesto académico |
| Actividad principal | Conectividad a internet, telefonía y soporte técnico | Supuesto académico |
| Alcance geográfico | Nacional e internacional | Supuesto académico |
| Sede operativa principal | Aguascalientes | Supuesto académico |
| Otras sedes | Guadalajara, Monterrey, Querétaro | Supuesto académico |
| Año de fundación | 2011 | Supuesto académico |

**Líneas de negocio:** telefonía móvil, internet fijo residencial, telefonía
empresarial e IoT/M2M.

### 4.2 Por qué se eligió este giro

El sector de telecomunicaciones resulta especialmente útil como caso de estudio
de ciberseguridad por una razón concreta: **el servicio que la empresa vende es
la disponibilidad misma**. En una organización de otro giro, un incidente de
seguridad degrada un proceso interno; aquí, degrada directamente el producto
que el cliente paga. Eso obliga a razonar la seguridad en términos de
continuidad del servicio y no solo de confidencialidad de datos, lo que da
material para casi todos los temas de la materia.

Además, es un sector con obligaciones regulatorias claras y públicas en México,
lo que permite anclar el análisis a normativa real aunque la empresa sea
ficticia.

### 4.3 Contexto tecnológico preliminar

Se asume que los procesos críticos de negocio —**facturación** y **soporte
técnico**— dependen de la siguiente infraestructura:

| Componente | Función | Naturaleza |
|---|---|---|
| CRM sobre base de datos MySQL | Gestión y seguimiento de cuentas, folios de soporte, datos de facturación | Supuesto académico |
| Servidores Nginx | Servidor web y proxy inverso del portal | Supuesto académico |
| Aplicaciones Laravel | Lógica del portal de clientes y sus APIs | Supuesto académico |
| Contenedores Docker | Empaquetado y despliegue de los servicios web | Supuesto académico |
| Infraestructura de enrutamiento | Entrega del servicio de internet y telefonía al suscriptor | Supuesto académico |
| Equipos de diagnóstico | Herramienta de campo del personal de soporte | Supuesto académico |

### 4.4 Marco normativo de referencia

A diferencia de todo lo anterior, esta sección **sí corresponde a normativa
pública y verificable**. Su aplicación al caso es hipotética.

| Regulador / norma | Relevancia para el proyecto |
|---|---|
| Instituto Federal de Telecomunicaciones (IFT) | Neutralidad de la red y lineamientos de calidad del servicio |
| INAI — LFPDPPP | Protección de datos personales de clientes y Derechos ARCO |
| PROFECO — NOM-184-SCFI | Disponibilidad de sistemas para atender quejas y garantías |
| Agenda Digital del Estado de Aguascalientes (SEDECYT) | Contexto estatal de conectividad e infraestructura |

## 5. Organización del equipo

### 5.1 Integrantes y responsabilidades

La siguiente distribución es la **propuesta de trabajo del equipo** para el
semestre. Está sujeta a ajuste conforme avancen los temas de la materia.

| Integrante | Área de responsabilidad propuesta |
|---|---|
| Covarrubias Delgado Ángel Iván | Documentación del contexto organizacional |
| Carrillo Lima Rafael Alberto | Marco normativo y cumplimiento |
| Arenas Maciel Carlos Saúl | Documentación técnica, estructura del repositorio e integración del prototipo |
| Rodríguez Acosta Joshua Rafael | Apoyo en análisis y revisión de entregables |
| Vela Puebla Simón | Desarrollo inicial del prototipo web |

> **Nota de honestidad sobre esta tabla:** las responsabilidades de Carlos Saúl
> Arenas Maciel y de Vela Puebla Simón están respaldadas por el historial del
> repositorio (sección 7). Las demás filas reflejan el reparto acordado por el
> equipo y aún no tienen evidencia técnica asociada, porque el trabajo de esos
> integrantes se ha dado hasta ahora en la redacción conjunta del documento de
> contexto.

### 5.2 Forma de trabajo

El equipo adoptó un flujo de trabajo basado en control de versiones, con tres
reglas explícitas:

1. **Un solo proyecto, un solo repositorio.** No se duplican documentos por
   integrante. El proyecto es uno y se construye en equipo.
2. **Cambios pequeños y revisables.** Cada pieza de trabajo entra por una rama
   propia y un *pull request* acotado, en lugar de acumular todo en un cambio
   grande. Esto permite ver qué se agregó y revertir algo puntual si hace falta.
3. **Revisión cruzada.** Ningún integrante integra su propio *pull request*: lo
   revisa e integra otro miembro del equipo. Así queda registro de quién
   propuso y quién aprobó cada cambio.

### 5.3 Herramientas

| Herramienta | Uso en el proyecto |
|---|---|
| Git | Control de versiones e historial de autoría |
| GitHub (organización IZTREK) | Repositorio remoto, *pull requests* y revisión |
| Markdown | Documentación del proyecto en `docs/` |
| HTML, CSS y JavaScript | Prototipo del portal corporativo |
| Node.js | Ejecución del validador de consistencia documental |

## 6. Estructura del proyecto

### 6.1 Organización del repositorio

```
Telecomunicaciones/
├── docs/                    Documentación del proyecto
│   ├── 01-contexto.md
│   ├── 02-gobierno-ti.md
│   ├── 03-alineacion-estrategica.md
│   ├── 04-matriz-activos.md
│   ├── 05-matriz-riesgos.md
│   ├── 06-matriz-controles.md
│   └── avance-01-organizacion.md      (este documento)
├── assets/
│   ├── css/styles.css       Sistema de diseño del prototipo
│   └── js/
│       ├── db.js            Datos ficticios de demostración
│       ├── auth.js          Módulo de autenticación
│       └── grc.js           Matrices como datos
├── tools/
│   └── validar-matrices.js  Verificación de consistencia documental
├── index.html               Portal público
├── login.html               Inicio de sesión
├── dashboard.html           Panel de usuario
├── gobierno.html            Tablero de gobierno
├── admin.html               Gestión de usuarios y roles
├── README.md                Índice y guía del proyecto
└── SECURITY.md              Decisiones de seguridad del prototipo
```

### 6.2 Criterio de organización

La documentación se numeró de forma que **el orden de los archivos sea el orden
de lectura del proyecto**: del contexto general hacia el detalle técnico. Cada
documento es autocontenido pero enlaza a los demás, de modo que el conjunto se
lee como un solo trabajo y no como fragmentos sueltos.

El prototipo se mantiene en la raíz porque es un sitio estático que se abre
directamente en el navegador, sin instalación ni servidor.

## 7. Participación individual — Arenas Maciel Carlos Saúl

Esta sección corresponde a la evidencia individual solicitada. Todo lo
enumerado es **verificable en el historial público del repositorio**.

### 7.1 Aportaciones concretas

| # | Aportación | Evidencia |
|---|---|---|
| 1 | Documentación del contexto de la organización, gobierno de TI y alineación estratégica | *Pull request* [#1](https://github.com/IZTREK/Telecomunicaciones/pull/1) |
| 2 | Documentación de las matrices de activos, riesgos y controles | *Pull request* [#2](https://github.com/IZTREK/Telecomunicaciones/pull/2) |
| 3 | Tablero de gobierno en el prototipo, con control de acceso por rol | *Pull request* [#3](https://github.com/IZTREK/Telecomunicaciones/pull/3) |
| 4 | Resumen del análisis en el portal público del prototipo | *Pull request* [#4](https://github.com/IZTREK/Telecomunicaciones/pull/4) |
| 5 | Guía de uso y estructura del proyecto en el README | *Pull request* [#5](https://github.com/IZTREK/Telecomunicaciones/pull/5) |
| 6 | Definición de la estructura de carpetas y del flujo de trabajo con ramas y *pull requests* | Organización de `docs/`, `tools/` y `assets/` |
| 7 | Redacción de este documento de Avance 01 | Este archivo |

### 7.2 Aportación metodológica

Más allá de los archivos, mi participación consistió en **proponer y sostener
la forma de trabajo** descrita en la sección 5.2:

- Establecí que el proyecto viviera en un repositorio único con documentación
  versionada, en lugar de documentos sueltos por integrante.
- Introduje la práctica de entregar en **incrementos pequeños**: cada uno de
  los cinco *pull requests* cubre una pieza acotada y coherente, con su
  descripción de qué cambia y por qué.
- Propuse que **ningún integrante integre su propio *pull request***, para que
  todo cambio quede revisado por otra persona.
- Escribí un **validador automático** (`tools/validar-matrices.js`) que
  comprueba que la documentación y los datos del prototipo no se contradigan
  entre sí. Es una medida de calidad documental, no una prueba de seguridad.

### 7.3 Contribución al documento de contexto del equipo

Participé junto con el resto del equipo en la elaboración del documento inicial
de contexto del proyecto, del cual aparezco como coautor, y posteriormente me
encargué de trasladar ese contenido al repositorio en formato versionado y
estructurado.

## 8. Estado actual y trabajo pendiente

### 8.1 Lo que ya existe

- Organización identificada y caracterizada, con sus supuestos declarados.
- Contexto tecnológico preliminar documentado.
- Estructura del proyecto y flujo de trabajo del equipo definidos y en uso.
- Prototipo funcional de portal corporativo como plataforma de práctica propia.

### 8.2 Nota sobre material adelantado

El equipo elaboró previamente unas **matrices preliminares de activos, riesgos
y controles**, que ya están en el repositorio. Dado que esta actividad indica
que el análisis de riesgos aún no corresponde, **ese material no se presenta
como parte de este avance**: queda registrado como trabajo preliminar y será
revisado, corregido y formalizado cuando la materia aborde el tema con la
metodología que se indique en clase.

### 8.3 Próximos pasos

| Etapa | Actividad prevista |
|---|---|
| Siguiente avance | Profundizar en el conocimiento de la organización y sus procesos críticos |
| Conforme al temario | Retomar y formalizar el inventario de activos con la metodología vista en clase |
| Conforme al temario | Desarrollar el análisis de riesgos con criterios formales |
| Conforme al temario | Incorporar los temas de auditoría y análisis forense |
| Cierre | Integrar todos los avances en el documento final del proyecto |

---

*Documento elaborado como evidencia individual del Avance 01. La organización
analizada es ficticia y todos sus datos constituyen supuestos académicos, según
se declara en la sección 2.*
