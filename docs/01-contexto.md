# 1. Contexto del proyecto

## 1.1 Información general de la organización

**PerroniPhone Comunicaciones S.A. de C.V.** es la organización analizada en
este proyecto. Se trata de una empresa **ficticia**, construida como supuesto
académico para la materia de Ciberseguridad, que opera en el sector de las
**telecomunicaciones**.

| Campo | Valor |
|---|---|
| Razón social | PerroniPhone Comunicaciones S.A. de C.V. (ficticia) |
| Sector | Telecomunicaciones |
| Alcance geográfico | Nacional e internacional |
| Sede operativa principal | Aguascalientes, Aguascalientes |
| Otras sedes | Guadalajara, Monterrey, Querétaro |
| Año de fundación | 2011 |

Su actividad principal es **proveer conectividad a internet, telefonía y
soporte técnico**. De esa actividad se desprenden cuatro líneas de negocio:

1. **Telefonía móvil** — planes de voz y datos para clientes finales.
2. **Internet fijo residencial** — enlaces de fibra óptica y última milla.
3. **Telefonía empresarial** — troncales SIP y enlaces dedicados a empresas.
4. **IoT / M2M** — conectividad para dispositivos industriales y telemetría.

Al ser un proveedor de servicios de telecomunicaciones, la organización opera
bajo una premisa que condiciona todo el resto del análisis: **el servicio que
vende es la disponibilidad misma**. Un incidente de seguridad que interrumpa
la red no degrada un proceso interno, sino el producto que el cliente paga.

## 1.2 Contexto tecnológico

Se asume como supuesto académico que los procesos críticos de negocio dependen
de una infraestructura de TI robusta, compuesta por los siguientes elementos:

### Sistemas de gestión (CRM)

El **CRM** es el sistema de registro de la operación comercial. Se utiliza
para la gestión y el seguimiento de cuentas de clientes, el registro de folios
de soporte técnico y la generación de reportes operativos. Su base de datos
**MySQL** concentra datos personales de clientes, historiales de fallas e
información de facturación, lo que lo convierte en el activo de información
más sensible de la organización.

### Plataformas web

Los servicios en línea (portal de clientes, levantamiento de reportes,
consulta de facturación) se operan mediante una arquitectura basada en:

| Componente | Rol en la arquitectura |
|---|---|
| **Docker** | Contenerización de los servicios; permite desplegar y escalar sin reinstalar servidores. |
| **Nginx** | Servidor web y proxy inverso; punto de entrada del tráfico HTTP/HTTPS. |
| **Laravel** | Framework de aplicación (PHP) donde vive la lógica del portal y las APIs. |
| **MySQL** | Motor de base de datos relacional que respalda al CRM y al portal. |

### Infraestructura de red

Además del cómputo, la organización opera equipo de **enrutamiento y
conmutación** que entrega el servicio de internet y telefonía a los
suscriptores, así como **equipos de diagnóstico** que utiliza el personal de
soporte técnico en campo.

### Dependencia crítica

Una interrupción en estos sistemas **paralizaría la capacidad de facturación y
de soporte técnico** de la empresa. Concretamente:

- Si cae el **CRM**, el personal de soporte no puede consultar ni registrar
  folios, y el área de cobranza no puede emitir facturas.
- Si cae la **infraestructura web**, el cliente pierde el canal de autoservicio
  y el volumen de llamadas al centro de contacto se dispara.
- Si cae la **infraestructura de enrutamiento**, el cliente pierde el servicio
  contratado, lo que además tiene consecuencias regulatorias.

Esta dependencia es la que justifica el nivel de criticidad asignado a cada
activo en la [matriz de activos](04-matriz-activos.md) y la severidad de los
riesgos identificados en la [matriz de riesgos](05-matriz-riesgos.md).

## 1.3 Alcance del análisis

| Dentro del alcance | Fuera del alcance |
|---|---|
| Base de datos MySQL del CRM (A-01) | Sistemas de nómina y recursos humanos |
| Infraestructura web contenerizada (A-02) | Red de oficinas administrativas (ofimática) |
| Infraestructura de enrutamiento de red e internet (A-03) | Aplicaciones móviles de cliente |
| Equipos de diagnóstico de soporte técnico (A-04) | Proveedores de última milla de terceros |

El análisis se concentra en los cuatro activos que sostienen directamente los
procesos de **facturación** y **soporte técnico**, por ser los que la
organización identificó como críticos para la continuidad del negocio.
