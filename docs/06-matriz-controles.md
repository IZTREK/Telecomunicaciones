# 6. Matriz de controles

Un **control** es la medida que se implementa para reducir un riesgo, ya sea
disminuyendo su probabilidad o su impacto. Esta matriz cierra el ciclo del
análisis: cada control responde a un riesgo concreto de la
[matriz de riesgos](05-matriz-riesgos.md).

## 6.1 Clasificación de controles

### Por tipo

| Tipo | Definición | Ejemplo en PerroniPhone |
|---|---|---|
| **Técnico** | Se implementa con tecnología, dentro de los sistemas. | MFA, respaldos automatizados, redundancia. |
| **Administrativo** | Se implementa con políticas, procedimientos y personas. | Plan de continuidad, revisión bimestral de accesos. |
| **Físico** | Protege el entorno material donde viven los activos. | UPS industriales y plantas de emergencia. |

### Por función

| Función | Qué hace | Momento |
|---|---|---|
| **Preventivo** | Evita que el incidente ocurra. | Antes |
| **Detectivo** | Descubre que el incidente está ocurriendo u ocurrió. | Durante |
| **Correctivo** | Restablece la operación después del incidente. | Después |

## 6.2 Matriz de controles

| ID | Control | Tipo | Función | Riesgo que mitiga | Propósito | Responsable |
|---|---|---|---|---|---|---|
| **C-01** | Autenticación Multifactor (MFA) | Técnico | Preventivo | R-01 | Fortalecer la autenticación de acceso al CRM y a la configuración de red. | CISO |
| **C-02** | Respaldos automatizados de base de datos | Técnico | Correctivo | R-01, R-02 | Reducir el impacto de una pérdida de información y permitir la recuperación. | CIO / DBA |
| **C-03** | Plan de continuidad del negocio | Administrativo | Correctivo | R-03 | Favorecer la recuperación ordenada del servicio ante una interrupción. | Responsable de BCP |
| **C-04** | Redundancia multi-zona y monitoreo | Técnico | Preventivo / Detectivo | R-02 | Eliminar el punto único de falla del portal y detectar degradación antes que el cliente. | CIO |
| **C-05** | Auditoría con logs inmutables (Zero Trust) | Técnico / Administrativo | Detectivo | R-01 | Dejar rastro verificable de cada acceso al CRM y detectar abuso de privilegios. | CISO |
| **C-06** | Respaldo de energía en nodos críticos | Físico | Preventivo | R-03 | Sostener la operación de los nodos ante micro-cortes y variaciones de voltaje. | Líder de Operaciones de Red |

## 6.3 Detalle de los controles principales

### C-01 — Autenticación Multifactor (MFA)

- **Tipo**: técnico · **Función**: preventivo · **Mitiga**: R-01
- **Propósito**: fortalecer la autenticación. Una contraseña robada deja de ser
  suficiente para entrar al CRM.
- **Implementación**: segundo factor obligatorio para el 100 % del personal con
  acceso a A-01 (CRM) y a la configuración de A-03 (red), tanto remoto como en
  las oficinas de Aguascalientes. Bajo el modelo **Zero Trust** se valida
  además el dispositivo y la ubicación desde la que se accede, aplicando
  **privilegios mínimos**.
- **Verificación**: revisión bimestral de los logs de acceso al CRM por parte
  del CISO; la meta del KPI de cobertura de MFA es 100 %.
- **En el portal**: `login.html` implementa la parte de autenticación
  (hash SHA-256 con salt, mensajes genéricos, bloqueo por intentos) y
  `admin.html` la de privilegios mínimos por rol. El segundo factor no se
  simula porque requiere un canal fuera del navegador; sus límites se explican
  en [SECURITY.md](../SECURITY.md).

### C-02 — Respaldos automatizados

- **Tipo**: técnico · **Función**: correctivo · **Mitiga**: R-01, R-02
- **Propósito**: reducir el impacto de una pérdida de información y permitir la
  recuperación del ciclo de facturación.
- **Implementación**: respaldos **lógicos** (volcado de la base) y **físicos**
  (imagen del volumen) automatizados, con rutinas estrictas de validación de
  integridad.
- **Verificación**: prueba de restauración **trimestral** que debe demostrar un
  **RTO menor a 4 horas** para restablecer facturación. La evidencia es la
  bitácora de la prueba, con hora de inicio, hora de fin e integridad validada.
- **Nota**: un respaldo que nunca se ha restaurado no cuenta como control
  efectivo; por eso el KPI se mide sobre la restauración, no sobre la
  generación del archivo.

### C-03 — Plan de continuidad del negocio

- **Tipo**: administrativo · **Función**: correctivo · **Mitiga**: R-03
- **Propósito**: favorecer la recuperación del servicio ante interrupciones de
  red e internet.
- **Implementación**: procedimiento documentado que define roles de crisis,
  árbol de escalamiento, comunicación a clientes y al IFT, y orden de
  restablecimiento de nodos por prioridad.
- **Verificación**: el Responsable de BCP actualiza y ejercita el plan; los
  resultados se presentan al Comité de Dirección de TI.

### C-04 — Redundancia multi-zona y monitoreo

- **Tipo**: técnico · **Función**: preventivo y detectivo · **Mitiga**: R-02
- **Propósito**: eliminar el punto único de falla de la plataforma web y
  detectar la degradación antes de que el cliente la perciba.
- **Implementación**: despliegue de la arquitectura dockerizada en **múltiples
  zonas de disponibilidad** sobre centros de datos **Tier III y Tier IV** del
  Bajío (Querétaro / Aguascalientes), con observabilidad que mide tiempos de
  respuesta y rendimiento por contenedor, alertas por umbral y **escalamiento
  automático de recursos**.
- **Verificación**: KPI de disponibilidad del servicio, con meta de 99.9 %
  mensual y reporte inmediato al comité ante cualquier caída por debajo de esa
  cifra.

### C-05 — Auditoría con logs inmutables

- **Tipo**: técnico y administrativo · **Función**: detectivo · **Mitiga**: R-01
- **Propósito**: garantizar trazabilidad. Si ocurre un incidente, debe poder
  reconstruirse quién accedió, cuándo y a qué.
- **Implementación**: registro de auditoría de la base de datos en almacenamiento
  **inmutable** (no modificable ni borrable por quien opera el sistema), con
  marca de tiempo, usuario y dirección IP.
- **Verificación**: revisión bimestral de accesos al CRM; los hallazgos se
  presentan al comité.
- **En el portal**: la bitácora de `dashboard.html` demuestra el patrón de
  registro y su visibilidad restringida a los roles `soporte` y
  `administrador`.

### C-06 — Respaldo de energía en nodos críticos

- **Tipo**: físico · **Función**: preventivo · **Mitiga**: R-03
- **Propósito**: sostener la operación de los nodos de A-03 ante interrupciones
  del suministro eléctrico.
- **Implementación**: **UPS industriales** y **plantas de emergencia**
  dimensionadas para las variaciones de voltaje y micro-cortes de la **CFE** en
  zonas metropolitanas e industriales, según lo instruido por el Gobierno de TI.
- **Verificación**: pruebas de conmutación a energía de respaldo dentro del
  ejercicio del plan de continuidad.

## 6.4 Trazabilidad completa

| Activo | Riesgo | Nivel | Controles | Riesgo residual |
|---|---|---|---|---|
| A-01 Base de datos del CRM | R-01 Acceso no autorizado | Alto | C-01, C-02, C-05 | Medio |
| A-02 Infraestructura web | R-02 DDoS / interrupción | Alto | C-04, C-02 | Bajo |
| A-03 Infraestructura de red | R-03 Interrupción del servicio | Alto | C-03, C-06 | Medio |
| A-04 Equipos de diagnóstico | Contribuye a R-01 | Medio | C-01 | Bajo |

Ningún control lleva el riesgo a cero: el objetivo del Gobierno de TI es
**dejarlo dentro del apetito definido** y mantenerlo bajo vigilancia mediante
los KPI de la sección [2.5](02-gobierno-ti.md#25-auditoría-y-medición-del-desempeño).
