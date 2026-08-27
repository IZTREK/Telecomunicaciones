# 3. Alineación estratégica

La alineación estratégica responde a una sola pregunta: **¿para qué le sirve al
negocio cada decisión de TI?** Un control que no protege un proceso de negocio
es gasto; un control alineado protege ingreso, cumplimiento o reputación.

En PerroniPhone, los procesos críticos de negocio son **facturación** y
**soporte técnico**, y ambos dependen por completo de la infraestructura
descrita en el [contexto tecnológico](01-contexto.md). Las tres iniciativas
siguientes son las que conectan la estrategia de TI con esos procesos.

## 3.1 Monitoreo proactivo para la disponibilidad del servicio

**Alineación con: soporte técnico.**

El negocio no puede permitirse que la atención a clientes o la gestión de
cuentas se detenga. La infraestructura web, al estar basada en una arquitectura
moderna con contenedores Docker, servidores Nginx y aplicaciones Laravel,
requiere que la estrategia de TI implemente **sistemas de observabilidad
avanzada**.

Esto significa que el monitoreo constante no solo revisa si el servidor está
"encendido", sino que evalúa **tiempos de respuesta** y **rendimiento de los
contenedores**. Así, los tableros de control y los sistemas de gestión de folios
o tickets de servicio operan de manera fluida.

Si un nodo de conectividad en Aguascalientes sufre latencia, el monitoreo debe
**disparar alertas y escalar recursos automáticamente** antes de que el cliente
perciba lentitud al levantar un reporte en el portal web.

| Elemento | Definición en PerroniPhone |
|---|---|
| Qué se mide | Disponibilidad, latencia de respuesta HTTP, consumo de CPU/memoria por contenedor |
| Umbral de alerta | Disponibilidad mensual por debajo de 99.9 % o latencia sostenida arriba de lo acordado |
| Acción automática | Escalamiento horizontal de contenedores y notificación al equipo de guardia |
| Beneficio de negocio | El cliente no percibe degradación; el centro de contacto no recibe el pico de llamadas |

## 3.2 Resiliencia de la información mediante respaldos automatizados

**Alineación con: facturación y continuidad del negocio.**

La base de datos MySQL del CRM es **el corazón operativo**: almacena información
de clientes, historiales de fallas y datos de facturación. La estrategia de TI
se alinea con la supervivencia del negocio mediante la implementación de
**respaldos automatizados**, tanto lógicos como físicos.

La alineación exige que se definan **rutinas estrictas de validación de
integridad** de esos respaldos y **tiempos de recuperación (RTO y RPO)
agresivos**. Esto asegura que, ante un fallo crítico en la infraestructura, la
base de datos pueda restaurarse rápidamente en un entorno de contingencia,
garantizando que el ciclo de facturación y el seguimiento de cuentas no se
paralicen.

| Elemento | Definición en PerroniPhone |
|---|---|
| Tipo de respaldo | Lógico (volcado de la base) y físico (imagen del volumen) |
| Validación | Restauración de prueba trimestral con verificación de integridad |
| RTO objetivo | Menos de 4 horas para restablecer facturación |
| Beneficio de negocio | El ciclo de facturación no se detiene; no hay pérdida de ingreso ni de historial de folios |

Un respaldo que nunca se ha restaurado **no es un respaldo, es una suposición**.
Por eso la meta se mide sobre la prueba de restauración, no sobre la generación
del archivo.

## 3.3 Controles de acceso estrictos

**Alineación con: protección de activos y confianza del cliente.**

Proteger la infraestructura de enrutamiento de red y el CRM contra accesos no
autorizados (R-01) es una prioridad que trasciende a TI: es una necesidad para
mantener la confianza de los clientes a nivel nacional e internacional.

La implementación de la **Autenticación Multifactor (MFA)** se alinea
estratégicamente al establecer una política de **privilegios mínimos**. Esto
asegura que el personal de soporte técnico y los administradores de red accedan
a los equipos de diagnóstico (A-04) y a la configuración de la red (A-03)
únicamente desde **dispositivos y ubicaciones autorizadas**, cerrando brechas de
vulnerabilidad que podrían derivar en una caída masiva del servicio de internet.

| Elemento | Definición en PerroniPhone |
|---|---|
| Alcance | 100 % del personal con acceso al CRM (A-01) y a configuración de red (A-03) |
| Modelo | Zero Trust: se valida usuario, dispositivo y ubicación en cada acceso |
| Verificación | Revisión bimestral de logs de acceso por parte del CISO |
| Beneficio de negocio | Se evitan multas del INAI por acceso no autorizado a datos personales y se preserva la confianza del cliente |

## 3.4 Trazabilidad: de la estrategia al control

La siguiente tabla cierra el ciclo y muestra cómo cada iniciativa estratégica
aterriza en un activo, un riesgo, un control y un indicador medible.

| Iniciativa estratégica | Proceso de negocio | Activo | Riesgo | Control | KPI que lo mide |
|---|---|---|---|---|---|
| Monitoreo proactivo | Soporte técnico | A-02 | R-02 | C-04 Redundancia y monitoreo | Disponibilidad del servicio |
| Respaldos automatizados | Facturación | A-01 | R-01 | C-02 Respaldos automatizados | RTO / RPO de facturación |
| Controles de acceso estrictos | Protección de activos | A-01, A-03, A-04 | R-01 | C-01 Autenticación Multifactor | Cobertura de MFA |
| Plan de continuidad | Operación de red | A-03 | R-03 | C-03 Plan de continuidad | RTO / RPO y pruebas del BCP |

El detalle de cada activo, riesgo y control se desarrolla en los documentos
[04](04-matriz-activos.md), [05](05-matriz-riesgos.md) y
[06](06-matriz-controles.md).
