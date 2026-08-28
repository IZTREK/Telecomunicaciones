# 2. Gobierno de TI

El Gobierno de TI define **quién decide**, **bajo qué reglas** y **cómo se
mide** el uso de la tecnología en PerroniPhone. No es lo mismo que la gestión
de TI: la gestión ejecuta la operación diaria, mientras que el gobierno
establece la dirección, aprueba el apetito de riesgo y vigila el cumplimiento.

## 2.1 Principios rectores y dirección estratégica

El Gobierno de TI de PerroniPhone se rige bajo el marco de **continuidad del
servicio y resiliencia**. Dado que los servicios provistos —internet, telefonía
y soporte— son considerados esenciales, las decisiones estratégicas de TI deben
asegurar **alta disponibilidad (SLA del 99.9 %)** en la infraestructura de
enrutamiento (A-03) y en los sistemas de gestión (A-01, A-02).

La estrategia prioriza la **retención de clientes** y la **competitividad en el
mercado nacional** mediante el uso de arquitecturas ágiles (contenedores Docker
y aplicaciones Laravel) que permitan un despliegue rápido de actualizaciones
sin afectar la operación.

De ahí se derivan cuatro principios rectores:

| # | Principio | Implicación práctica |
|---|---|---|
| P-1 | **La disponibilidad es el producto** | Ningún cambio se aprueba si no puede desplegarse sin interrumpir el servicio. |
| P-2 | **Mínimo privilegio por defecto** | Todo acceso nace denegado; se concede por rol y se revisa periódicamente. |
| P-3 | **Todo cambio deja rastro** | Despliegues, cambios de rol y accesos al CRM quedan registrados en bitácora. |
| P-4 | **Cumplir es condición de operar** | Un incumplimiento regulatorio se trata con la misma severidad que una caída. |

## 2.2 Marco regulatorio y cumplimiento (compliance 2026)

Para operar legalmente y evitar sanciones que paralicen la empresa, el Gobierno
de TI vigila el cumplimiento estricto de las siguientes normativas vigentes en
México:

### Instituto Federal de Telecomunicaciones (IFT)

Las políticas de TI deben garantizar la **neutralidad de la red** y el
cumplimiento de los lineamientos de **calidad del servicio** móvil y fijo
estipulados por el IFT para 2026. La interrupción del servicio (R-03) no solo
afecta al negocio: es motivo de **multas federales**.

### INAI y LFPDPPP

La Ley Federal de Protección de Datos Personales en Posesión de los
Particulares aplica directamente sobre la base de datos MySQL del CRM (A-01),
que contiene **datos personales de clientes**. El Gobierno de TI establece
como obligatorios:

- La atención de los **Derechos ARCO** (Acceso, Rectificación, Cancelación y
  Oposición) dentro de los plazos de ley.
- El **cifrado de la base de datos** en reposo y en tránsito, para mitigar el
  riesgo de multas por accesos no autorizados (R-01).
- El aviso de privacidad publicado y vigente en todos los puntos de captura.

### PROFECO (NOM-184-SCFI)

Los sistemas de facturación y soporte técnico soportados por la infraestructura
web (A-02) deben estar **siempre disponibles** para atender quejas y folios de
garantía dentro de los tiempos legales estipulados por la norma.

### Contexto estatal (Aguascalientes)

Alineación con la **Agenda Digital del Estado de Aguascalientes** y
colaboración con la **Secretaría de Desarrollo Económico, Ciencia y Tecnología
(SEDECYT)**. La infraestructura de red y soporte técnico (A-03, A-04) deberá
aprovechar los nodos de conectividad del estado y prever la orografía y el
desarrollo de los parques industriales del Bajío para la expansión de la fibra
óptica.

### Resumen de obligaciones

| Regulador | Obligación principal | Activo/riesgo asociado | Consecuencia de incumplir |
|---|---|---|---|
| IFT | Neutralidad de la red y calidad del servicio | A-03 / R-03 | Multa federal |
| INAI (LFPDPPP) | Derechos ARCO y cifrado de datos personales | A-01 / R-01 | Multa millonaria |
| PROFECO (NOM-184-SCFI) | Disponibilidad para quejas y garantías | A-02 / R-02 | Sanción y reposición |
| SEDECYT (estatal) | Alineación con la Agenda Digital estatal | A-03, A-04 | Pérdida de convenios |

## 2.3 Estructura y comités de decisión

Para supervisar la matriz de riesgos y la aplicación de controles se establece
el **Comité de Dirección de TI**, que sesiona **mensualmente** en la sede
operativa de Aguascalientes.

| Rol | Responsabilidad dentro del gobierno de TI |
|---|---|
| **CIO** (Chief Information Officer) | Garantizar que la infraestructura contenerizada (Nginx / Laravel / Docker) escale según la demanda nacional. Preside el comité. |
| **CISO** (Chief Information Security Officer) | Supervisar la matriz de riesgos. Responsable directo de auditar la implementación del **MFA** para el acceso al CRM y de verificar las pruebas de penetración contra ataques de **denegación de servicio (DDoS)** sobre la infraestructura web (R-02). |
| **Responsable de Continuidad de Negocio (BCP)** | Ejecutar y actualizar el plan de continuidad ante interrupciones de red e internet (R-03). |
| **Responsable de Cumplimiento** | Dar seguimiento a las obligaciones ante IFT, INAI y PROFECO, y preparar la evidencia de auditoría. |
| **Líder de Operaciones de Red** | Custodio operativo de A-03; ejecuta los cambios de configuración aprobados por el comité. |

### Flujo de decisión

1. El **CISO** presenta al comité el estado de la matriz de riesgos y los
   hallazgos de auditoría del periodo.
2. El comité evalúa si cada riesgo se mantiene dentro del apetito definido.
3. Si un riesgo excede el umbral, el comité **autoriza presupuesto y
   responsable** para el control correspondiente.
4. El **CIO** integra el control aprobado al plan de trabajo de TI.
5. En la siguiente sesión se revisa el avance mediante los KPI de la sección
   2.5.

## 2.4 Gestión de riesgos adaptada a la realidad operativa (2026)

La supervisión de riesgos bajo este Gobierno de TI toma en cuenta factores del
entorno nacional, no solo el catálogo teórico de amenazas:

### R-01 — Base de datos del CRM

El riesgo de acceso no autorizado se mitiga con **MFA**, pero el Gobierno de TI
exige además políticas de **Zero Trust** (cero confianza) y **auditoría de la
base de datos mediante logs inmutables**, algo vital ante el aumento de ataques
de **ransomware** dirigidos a corporativos en México.

### R-02 — Infraestructura web

Ante la falta de redundancia, el Gobierno de TI autoriza presupuesto para
desplegar la arquitectura dockerizada en **múltiples zonas de disponibilidad**,
aprovechando la infraestructura de centros de datos **Tier III y Tier IV** que
operan en la región del Bajío (Querétaro / Aguascalientes) para asegurar baja
latencia.

### R-03 — Infraestructura de red

La probabilidad se catalogó como **baja**, pero el impacto es **crítico**. El
Gobierno de TI instruye que el plan de continuidad administrativa incluya
**sistemas de respaldo de energía** (UPS industriales y plantas de emergencia)
para los nodos críticos (A-03), considerando las variaciones de voltaje y los
micro-cortes de la **Comisión Federal de Electricidad (CFE)** en zonas
metropolitanas e industriales.

### Apetito de riesgo

| Nivel de riesgo | Decisión del comité |
|---|---|
| **Crítico** | Se detiene o se limita el servicio afectado hasta mitigar. Escalamiento inmediato a dirección general. |
| **Alto** | Requiere control aprobado con responsable y fecha compromiso en la misma sesión del comité. |
| **Medio** | Se acepta temporalmente con seguimiento mensual documentado. |
| **Bajo** | Se acepta y se revisa en la evaluación anual. |

## 2.5 Auditoría y medición del desempeño

El Gobierno de TI no solo dicta las reglas: **mide su cumplimiento** a través de
indicadores clave de desempeño (KPI).

| KPI | Definición | Meta | Frecuencia | Responsable |
|---|---|---|---|---|
| **RTO / RPO de facturación** | Tiempo de recuperación y punto de recuperación de la base de datos tras una caída. Los respaldos automatizados deben probarse restaurándolos, no solo generándolos. | RTO < 4 h | Prueba trimestral | BCP |
| **Disponibilidad del servicio** | Porcentaje de disponibilidad mensual de los servidores Nginx, medido con monitoreo 24/7 mediante tableros de control. Cualquier caída por debajo de la meta se reporta de inmediato al comité. | 99.9 % mensual o más | Continua (24/7) | CIO |
| **Cobertura de MFA** | Porcentaje de empleados —remotos y en oficinas de Aguascalientes— que efectivamente utilizan Autenticación Multifactor para acceder al CRM (A-01), verificado en los logs de acceso. | 100 % | Revisión bimestral | CISO |
| **Cierre de hallazgos** | Porcentaje de hallazgos de auditoría cerrados dentro de la fecha compromiso acordada en el comité. | 90 % o más | Mensual | Cumplimiento |

### Evidencia de auditoría

Cada KPI debe poder demostrarse ante un auditor con evidencia verificable:

- **RTO / RPO**: bitácora de la prueba de restauración trimestral, con hora de
  inicio, hora de finalización e integridad validada del respaldo.
- **Disponibilidad**: exportación del tablero de monitoreo del periodo.
- **Cobertura de MFA**: extracto de los logs de acceso al CRM de la revisión
  bimestral.
- **Cierre de hallazgos**: minutas firmadas del Comité de Dirección de TI.
