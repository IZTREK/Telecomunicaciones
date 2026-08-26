# 4. Matriz de activos

Un **activo de información** es cualquier recurso que tiene valor para la
organización y que, por lo tanto, merece protección. Antes de hablar de
riesgos hay que saber **qué se está protegiendo**: esa es la función de esta
matriz.

## 4.1 Criterio de valoración

La criticidad de cada activo se determinó evaluando el impacto que tendría su
pérdida sobre las tres propiedades clásicas de la seguridad de la información:

| Propiedad | Pregunta que responde |
|---|---|
| **Confidencialidad** | ¿Qué tan grave es que alguien no autorizado lo vea? |
| **Integridad** | ¿Qué tan grave es que se altere sin autorización? |
| **Disponibilidad** | ¿Qué tan grave es que deje de estar accesible? |

La criticidad final es el valor **más alto** de las tres, porque basta con que
una propiedad se vea comprometida para afectar al negocio.

| Nivel | Significado para PerroniPhone |
|---|---|
| **Crítica** | Su pérdida interrumpe el servicio vendido al cliente y expone a sanción del regulador. |
| **Alta** | Su pérdida detiene un proceso de negocio (facturación o soporte) pero no la red. |
| **Media** | Su pérdida degrada la operación y se puede sustituir temporalmente. |
| **Baja** | Su pérdida es una molestia operativa sin efecto en el servicio. |

## 4.2 Matriz de activos

| ID | Activo | Tipo | C | I | D | Criticidad | Propietario | Custodio |
|---|---|---|---|---|---|---|---|---|
| **A-01** | Base de datos MySQL del CRM | Información | Alta | Alta | Alta | **Alta** | Dirección Comercial | CIO / DBA |
| **A-02** | Infraestructura web contenerizada (Nginx / Laravel / Docker) | Sistema | Media | Alta | Alta | **Alta** | CIO | Equipo de DevOps |
| **A-03** | Infraestructura de enrutamiento de red e internet | Hardware / Sistema | Media | Alta | Crítica | **Crítica** | Dirección de Operaciones | Líder de Operaciones de Red |
| **A-04** | Equipos de diagnóstico para soporte técnico | Hardware | Baja | Media | Media | **Media** | Gerencia de Soporte | Coordinación de campo |

## 4.3 Detalle de cada activo

### A-01 — Base de datos MySQL del CRM

- **Tipo**: Información
- **Criticidad**: Alta
- **Descripción**: motor MySQL que respalda el CRM. Almacena datos personales
  de clientes, historiales de fallas, folios de soporte y datos de facturación.
- **Por qué es crítico**: concentra la información con la que se factura y se
  atiende al cliente. Además, al contener datos personales, queda directamente
  bajo el alcance de la **LFPDPPP**, por lo que un acceso no autorizado no solo
  es un incidente técnico sino una **infracción sancionable por el INAI**.
- **Riesgo asociado**: [R-01](05-matriz-riesgos.md#r-01--base-de-datos-del-crm)
- **Controles aplicados**: C-01 (MFA), C-02 (respaldos), C-05 (auditoría con
  logs inmutables)

### A-02 — Infraestructura web contenerizada

- **Tipo**: Sistema
- **Criticidad**: Alta
- **Descripción**: conjunto de contenedores **Docker** que ejecutan la
  aplicación **Laravel** detrás de **Nginx**, y que publica el portal de
  clientes, el levantamiento de reportes y la consulta de facturación.
- **Por qué es crítico**: es el canal de autoservicio del cliente y el soporte
  de los sistemas de facturación y de quejas. La **NOM-184-SCFI** exige que
  esos sistemas estén disponibles para atender quejas y folios de garantía
  dentro de los tiempos legales.
- **Riesgo asociado**: [R-02](05-matriz-riesgos.md#r-02--infraestructura-web)
- **Controles aplicados**: C-04 (redundancia multi-zona y monitoreo), C-02
  (respaldos)

### A-03 — Infraestructura de enrutamiento de red e internet

- **Tipo**: Hardware / Sistema
- **Criticidad**: **Crítica**
- **Descripción**: routers, switches y enlaces que entregan el servicio de
  internet y telefonía a los suscriptores, incluidos los nodos de conectividad
  en Aguascalientes.
- **Por qué es crítico**: **es el producto que la empresa vende**. Su caída no
  degrada un proceso interno: deja al cliente sin servicio y activa
  responsabilidad ante el **IFT** por incumplimiento de los lineamientos de
  calidad del servicio. Es el único activo con criticidad Crítica.
- **Riesgo asociado**: [R-03](05-matriz-riesgos.md#r-03--infraestructura-de-red)
- **Controles aplicados**: C-03 (plan de continuidad), C-06 (respaldo de
  energía), C-01 (MFA para acceso a configuración)

### A-04 — Equipos de diagnóstico para soporte técnico

- **Tipo**: Hardware
- **Criticidad**: Media
- **Descripción**: equipo de medición y diagnóstico (analizadores de fibra,
  medidores de señal, terminales de configuración) que utiliza el personal de
  soporte en campo.
- **Por qué importa**: sin ellos el tiempo de resolución de fallas se dispara,
  aunque el servicio siga en pie. Además, algunos permiten conectarse a la
  configuración de red, por lo que un equipo extraviado es también una vía de
  acceso a A-03.
- **Riesgo asociado**: contribuye a [R-01](05-matriz-riesgos.md) por la vía de
  acceso a configuración.
- **Controles aplicados**: C-01 (MFA y acceso desde dispositivos autorizados)

## 4.4 Lectura de la matriz

Tres de los cuatro activos tienen criticidad Alta o Crítica, lo que confirma la
premisa del [contexto del proyecto](01-contexto.md): en una empresa de
telecomunicaciones **casi todo el inventario relevante sostiene directamente el
servicio vendido**. Esto justifica que el Gobierno de TI adopte la continuidad
del servicio como marco rector y no como un objetivo secundario.
