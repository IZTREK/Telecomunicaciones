/**
 * PerroniPhone — Datos de Gobierno, Riesgo y Cumplimiento (GRC)
 * -------------------------------------------------------------
 * Refleja en el portal las matrices documentadas en /docs:
 *   - docs/04-matriz-activos.md
 *   - docs/05-matriz-riesgos.md
 *   - docs/06-matriz-controles.md
 *
 * Si una matriz cambia en la documentación, debe actualizarse aquí para que
 * el portal y el entregable escrito no se contradigan.
 */

const PERRONI_GRC = {
  // ---------- Escalas de valoración ----------
  escalas: {
    probabilidad: ["Baja", "Media", "Alta"],
    impacto: ["Bajo", "Medio", "Alto", "Crítico"],
  },

  /**
   * Matriz de calor: nivel de riesgo resultante de cruzar probabilidad e
   * impacto. Coincide con la tabla de docs/05-matriz-riesgos.md.
   */
  matrizCalor: {
    Alta: { Bajo: "Medio", Medio: "Alto", Alto: "Alto", Crítico: "Crítico" },
    Media: { Bajo: "Bajo", Medio: "Medio", Alto: "Alto", Crítico: "Crítico" },
    Baja: { Bajo: "Bajo", Medio: "Bajo", Alto: "Medio", Crítico: "Alto" },
  },

  // ---------- 4. Matriz de activos ----------
  activos: [
    {
      id: "A-01",
      nombre: "Base de datos MySQL del CRM",
      tipo: "Información",
      criticidad: "Alta",
      propietario: "Dirección Comercial",
      custodio: "CIO / DBA",
      cia: { confidencialidad: "Alta", integridad: "Alta", disponibilidad: "Alta" },
      descripcion:
        "Concentra datos personales de clientes, historiales de fallas y datos de facturación. Queda bajo el alcance de la LFPDPPP.",
      riesgos: ["R-01"],
      controles: ["C-01", "C-02", "C-05"],
    },
    {
      id: "A-02",
      nombre: "Infraestructura web contenerizada (Nginx / Laravel / Docker)",
      tipo: "Sistema",
      criticidad: "Alta",
      propietario: "CIO",
      custodio: "Equipo de DevOps",
      cia: { confidencialidad: "Media", integridad: "Alta", disponibilidad: "Alta" },
      descripcion:
        "Publica el portal de clientes, el levantamiento de reportes y la consulta de facturación exigida por la NOM-184-SCFI.",
      riesgos: ["R-02"],
      controles: ["C-04", "C-02"],
    },
    {
      id: "A-03",
      nombre: "Infraestructura de enrutamiento de red e internet",
      tipo: "Hardware / Sistema",
      criticidad: "Crítica",
      propietario: "Dirección de Operaciones",
      custodio: "Líder de Operaciones de Red",
      cia: { confidencialidad: "Media", integridad: "Alta", disponibilidad: "Crítica" },
      descripcion:
        "Es el producto que la empresa vende. Su caída deja al cliente sin servicio y activa responsabilidad ante el IFT.",
      riesgos: ["R-03"],
      controles: ["C-03", "C-06", "C-01"],
    },
    {
      id: "A-04",
      nombre: "Equipos de diagnóstico para soporte técnico",
      tipo: "Hardware",
      criticidad: "Media",
      propietario: "Gerencia de Soporte",
      custodio: "Coordinación de campo",
      cia: { confidencialidad: "Baja", integridad: "Media", disponibilidad: "Media" },
      descripcion:
        "Equipo de medición usado en campo. Un equipo extraviado es también una vía de acceso a la configuración de A-03.",
      riesgos: ["R-01"],
      controles: ["C-01"],
    },
  ],

  // ---------- 5. Matriz de riesgos ----------
  riesgos: [
    {
      id: "R-01",
      activo: "A-01",
      titulo: "Base de datos del CRM",
      amenaza: "Acceso no autorizado (ransomware, phishing o abuso de privilegios)",
      vulnerabilidad: "Control de acceso insuficiente",
      probabilidad: "Media",
      impacto: "Alto",
      nivel: "Alto",
      residual: "Medio",
      tratamiento: "Mitigar",
      controles: ["C-01", "C-02", "C-05"],
      regulador: "INAI (LFPDPPP)",
    },
    {
      id: "R-02",
      activo: "A-02",
      titulo: "Infraestructura web",
      amenaza: "Denegación de servicio (DDoS) o interrupción de la plataforma",
      vulnerabilidad: "Falta de redundancia",
      probabilidad: "Media",
      impacto: "Alto",
      nivel: "Alto",
      residual: "Bajo",
      tratamiento: "Mitigar",
      controles: ["C-04", "C-02"],
      regulador: "PROFECO (NOM-184-SCFI)",
    },
    {
      id: "R-03",
      activo: "A-03",
      titulo: "Infraestructura de red",
      amenaza: "Interrupción del servicio de internet y telefonía",
      vulnerabilidad: "Fallas de cobertura o de energía",
      probabilidad: "Baja",
      impacto: "Crítico",
      nivel: "Alto",
      residual: "Medio",
      tratamiento: "Mitigar",
      controles: ["C-03", "C-06"],
      regulador: "IFT",
    },
  ],

  // ---------- 6. Matriz de controles ----------
  controles: [
    {
      id: "C-01",
      nombre: "Autenticación Multifactor (MFA)",
      tipo: "Técnico",
      funcion: "Preventivo",
      mitiga: ["R-01"],
      proposito: "Fortalecer la autenticación de acceso al CRM y a la configuración de red.",
      responsable: "CISO",
      verificacion: "Revisión bimestral de logs de acceso al CRM.",
      enElPortal: true,
    },
    {
      id: "C-02",
      nombre: "Respaldos automatizados de base de datos",
      tipo: "Técnico",
      funcion: "Correctivo",
      mitiga: ["R-01", "R-02"],
      proposito: "Reducir el impacto de una pérdida de información y permitir la recuperación.",
      responsable: "CIO / DBA",
      verificacion: "Prueba de restauración trimestral con RTO menor a 4 horas.",
      enElPortal: false,
    },
    {
      id: "C-03",
      nombre: "Plan de continuidad del negocio",
      tipo: "Administrativo",
      funcion: "Correctivo",
      mitiga: ["R-03"],
      proposito: "Favorecer la recuperación ordenada del servicio ante una interrupción.",
      responsable: "Responsable de BCP",
      verificacion: "Ejercicio del plan y presentación de resultados al comité.",
      enElPortal: false,
    },
    {
      id: "C-04",
      nombre: "Redundancia multi-zona y monitoreo",
      tipo: "Técnico",
      funcion: "Preventivo / Detectivo",
      mitiga: ["R-02"],
      proposito: "Eliminar el punto único de falla del portal y detectar degradación antes que el cliente.",
      responsable: "CIO",
      verificacion: "KPI de disponibilidad mensual del 99.9 %.",
      enElPortal: false,
    },
    {
      id: "C-05",
      nombre: "Auditoría con logs inmutables (Zero Trust)",
      tipo: "Técnico / Administrativo",
      funcion: "Detectivo",
      mitiga: ["R-01"],
      proposito: "Dejar rastro verificable de cada acceso al CRM y detectar abuso de privilegios.",
      responsable: "CISO",
      verificacion: "Revisión bimestral de accesos; hallazgos al comité.",
      enElPortal: true,
    },
    {
      id: "C-06",
      nombre: "Respaldo de energía en nodos críticos",
      tipo: "Físico",
      funcion: "Preventivo",
      mitiga: ["R-03"],
      proposito: "Sostener la operación de los nodos ante micro-cortes y variaciones de voltaje de la CFE.",
      responsable: "Líder de Operaciones de Red",
      verificacion: "Pruebas de conmutación a energía de respaldo.",
      enElPortal: false,
    },
  ],

  // ---------- 2.5 KPI de auditoría ----------
  kpis: [
    {
      nombre: "Disponibilidad del servicio",
      valor: "99.94 %",
      meta: "99.9 % mensual",
      estado: "ok",
      frecuencia: "Continua (24/7)",
      responsable: "CIO",
    },
    {
      nombre: "RTO de facturación",
      valor: "3 h 10 m",
      meta: "Menos de 4 h",
      estado: "ok",
      frecuencia: "Prueba trimestral",
      responsable: "BCP",
    },
    {
      nombre: "Cobertura de MFA en el CRM",
      valor: "92 %",
      meta: "100 %",
      estado: "warn",
      frecuencia: "Revisión bimestral",
      responsable: "CISO",
    },
    {
      nombre: "Cierre de hallazgos en fecha",
      valor: "87 %",
      meta: "90 % o más",
      estado: "warn",
      frecuencia: "Mensual",
      responsable: "Cumplimiento",
    },
  ],

  // Devuelve el nivel de riesgo que corresponde a una combinación dada.
  nivelDe(probabilidad, impacto) {
    return (this.matrizCalor[probabilidad] || {})[impacto] || "—";
  },

  // Riesgos que caen en una celda concreta de la matriz de calor.
  riesgosEn(probabilidad, impacto) {
    return this.riesgos.filter(
      (r) => r.probabilidad === probabilidad && r.impacto === impacto
    );
  },
};
