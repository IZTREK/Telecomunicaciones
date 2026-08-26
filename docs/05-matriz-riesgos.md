# 5. Matriz de riesgos

Un **riesgo** es la combinación de una **amenaza** que aprovecha una
**vulnerabilidad** sobre un **activo**, con una probabilidad de ocurrencia y un
impacto determinados. Esta matriz evalúa los riesgos identificados sobre los
activos del documento [04](04-matriz-activos.md).

## 5.1 Metodología de evaluación

### Escala de probabilidad

| Nivel | Criterio |
|---|---|
| **Alta** | Se espera que ocurra varias veces al año. |
| **Media** | Podría ocurrir al menos una vez al año. |
| **Baja** | Poco probable en un horizonte anual, pero factible. |

### Escala de impacto

| Nivel | Criterio |
|---|---|
| **Crítico** | Interrumpe el servicio vendido al cliente y activa sanción del regulador. |
| **Alto** | Detiene facturación o soporte técnico; pérdida económica y de reputación relevante. |
| **Medio** | Degrada la operación sin detenerla. |
| **Bajo** | Molestia operativa absorbible. |

### Matriz de calor (probabilidad × impacto)

El nivel de riesgo resulta de cruzar ambas escalas:

| Probabilidad ↓ / Impacto → | Bajo | Medio | Alto | Crítico |
|---|---|---|---|---|
| **Alta** | Medio | Alto | Alto | Crítico |
| **Media** | Bajo | Medio | **Alto** | Crítico |
| **Baja** | Bajo | Bajo | Medio | **Alto** |

Las celdas en negritas son las que ocupan los riesgos identificados. Nótese que
**R-03 alcanza nivel Alto pese a tener probabilidad Baja**: cuando el impacto es
crítico, la baja frecuencia no basta para bajar la prioridad.

## 5.2 Matriz de riesgos

| ID | Activo | Amenaza | Vulnerabilidad | Probabilidad | Impacto | **Nivel** | Control |
|---|---|---|---|---|---|---|---|
| **R-01** | A-01 | Acceso no autorizado a la base de datos del CRM | Control de acceso insuficiente | Media | Alto | **Alto** | C-01, C-02, C-05 |
| **R-02** | A-02 | Denegación de servicio (DDoS) o interrupción de la plataforma web | Falta de redundancia | Media | Alto | **Alto** | C-04, C-02 |
| **R-03** | A-03 | Interrupción del servicio de red e internet | Fallas de cobertura o de energía | Baja | Crítico | **Alto** | C-03, C-06 |

## 5.3 Detalle de cada riesgo

### R-01 — Base de datos del CRM

- **Activo afectado**: A-01, base de datos MySQL del CRM.
- **Amenaza**: acceso no autorizado, ya sea externo (credenciales robadas,
  *phishing*, **ransomware**) o interno (abuso de privilegios).
- **Vulnerabilidad**: control de acceso insuficiente — credenciales de un solo
  factor y permisos otorgados por conveniencia y no por necesidad.
- **Probabilidad: Media.** El aumento sostenido de ataques de ransomware
  dirigidos a corporativos en México hace que este escenario sea plausible
  dentro del año.
- **Impacto: Alto.** Exposición de datos personales de clientes, con multa
  potencial del **INAI** bajo la LFPDPPP, además del daño reputacional y de la
  posible pérdida o cifrado del historial de facturación.
- **Nivel resultante: Alto.**
- **Tratamiento**: **mitigar**. Se aplica MFA (C-01), respaldos automatizados
  (C-02) y auditoría con logs inmutables bajo modelo Zero Trust (C-05).
- **Riesgo residual esperado**: Medio. Aun con MFA, subsiste el riesgo de abuso
  de privilegios por parte de personal autorizado, que se atiende con la
  revisión bimestral de logs.

### R-02 — Infraestructura web

- **Activo afectado**: A-02, infraestructura web contenerizada.
- **Amenaza**: denegación de servicio (**DDoS**) o interrupción de la
  plataforma.
- **Vulnerabilidad**: falta de redundancia — la arquitectura dockerizada opera
  en una sola zona de disponibilidad, por lo que un fallo ahí tumba todo el
  portal.
- **Probabilidad: Media.** Los portales de operadores de telecomunicaciones son
  blanco habitual de ataques volumétricos, y un fallo de infraestructura en un
  único sitio no requiere de un atacante para ocurrir.
- **Impacto: Alto.** El cliente pierde el canal de autoservicio y la empresa
  pierde el medio por el que atiende quejas y folios de garantía dentro de los
  plazos que exige la **NOM-184-SCFI** (PROFECO).
- **Nivel resultante: Alto.**
- **Tratamiento**: **mitigar**. El Comité de Dirección de TI autorizó
  presupuesto para despliegue en múltiples zonas de disponibilidad sobre
  centros de datos Tier III/Tier IV del Bajío, con monitoreo y escalamiento
  automático (C-04).
- **Riesgo residual esperado**: Bajo, una vez operando la redundancia
  multi-zona.

### R-03 — Infraestructura de red

- **Activo afectado**: A-03, infraestructura de enrutamiento de red e internet.
- **Amenaza**: interrupción del servicio de internet y telefonía.
- **Vulnerabilidad**: fallas de cobertura o de energía en los nodos críticos.
- **Probabilidad: Baja.** Los nodos son equipo redundante y de grado operador;
  las interrupciones totales son infrecuentes.
- **Impacto: Crítico.** Es el servicio que el cliente paga. Una caída
  prolongada implica incumplimiento del **SLA del 99.9 %**, responsabilidad
  ante el **IFT** por los lineamientos de calidad del servicio y fuga masiva de
  clientes hacia la competencia.
- **Nivel resultante: Alto** — la baja probabilidad no compensa un impacto
  crítico.
- **Tratamiento**: **mitigar**. Plan de continuidad del negocio (C-03) y
  sistemas de respaldo de energía —UPS industriales y plantas de emergencia—
  dimensionados para las variaciones de voltaje y micro-cortes de la **CFE** en
  zonas metropolitanas e industriales (C-06).
- **Riesgo residual esperado**: Medio. Ninguna medida elimina por completo la
  dependencia de la infraestructura eléctrica y de la última milla.

## 5.4 Conclusión de la evaluación

Los tres riesgos identificados quedan en nivel **Alto**, lo que según el
[apetito de riesgo](02-gobierno-ti.md#apetito-de-riesgo) definido por el
Gobierno de TI significa que **los tres requieren un control aprobado, con
responsable y fecha compromiso**, en la misma sesión del comité en que se
presentan. Ninguno puede simplemente aceptarse.

Su tratamiento se desarrolla en la [matriz de controles](06-matriz-controles.md).
