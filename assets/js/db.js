/**
 * PerroniPhone — Base de datos ficticia 
 */

const PERRONI_DB = {
  organizacion: {
    razonSocial: "PerroniPhone Comunicaciones S.A. de C.V. (ficticia)",
    fundacion: 2011,
    sedes: ["Aguascalientes", "Guadalajara", "Monterrey", "Querétaro"],
    lineas: ["Telefonía móvil", "Internet fijo", "Telefonía empresarial", "IoT / M2M"],
  },

  // Usuarios de demostración. Contraseñas de prueba en el formulario de acceso.
  usuarios: [
    {
      id: "U-1001",
      nombre: "Marina Ibarra Cordero",
      correo: "admin@perroniphone.edu.mx",
      rol: "administrador",
      estado: "activo",
      salt: "c4d452af985a59fd",
      hash: "224a95373757343867c16674f4152e66eb4bc2136be33407828d2e03f49dee5c",
      ultimoAcceso: "2026-08-12T09:14:00",
      intentosFallidos: 0,
    },
    {
      id: "U-1002",
      nombre: "Diego Salcedo Núñez",
      correo: "soporte@perroniphone.edu.mx",
      rol: "soporte",
      estado: "activo",
      salt: "6fd20e52b5847e2d",
      hash: "836c69af3fbd31917e6bfc69519d313c8292af225f3324cc2a39382d3fc643f8",
      ultimoAcceso: "2026-08-13T17:02:00",
      intentosFallidos: 0,
    },
    {
      id: "U-1003",
      nombre: "Renata Ochoa Villegas",
      correo: "cliente@perroniphone.edu.mx",
      rol: "cliente",
      estado: "activo",
      salt: "0a29313b70efbf33",
      hash: "352d357a9b6e71534b1cac3b8451ecaece56b0fa0eb7b6dd613e997964b73d9e",
      ultimoAcceso: "2026-08-14T08:47:00",
      intentosFallidos: 0,
    },
    {
      id: "U-1004",
      nombre: "Emilio Farías Reyes",
      correo: "emilio.farias@perroniphone.edu.mx",
      rol: "cliente",
      estado: "bloqueado",
      salt: "2dc0b41416e6f04b",
      hash: "58855d2242a209e5ee73fdc50c2ede19c9d5fad4fdd107ef6322735fbdf15191",
      ultimoAcceso: "2026-07-30T11:20:00",
      intentosFallidos: 5,
    },
  ],

  // Qué puede hacer cada rol dentro del portal (control de acceso basado en roles).
  permisos: {
    administrador: ["ver_dashboard", "gestionar_usuarios", "ver_bitacora", "editar_roles", "ver_grc"],
    soporte: ["ver_dashboard", "ver_bitacora", "ver_grc"],
    cliente: ["ver_dashboard"],
  },

  planes: [
    { id: "P-10", nombre: "PerroniPhone Esencial", velocidad: "50 Mbps", datosMoviles: "10 GB", precio: "$349 MXN" },
    { id: "P-20", nombre: "PerroniPhone Plus", velocidad: "150 Mbps", datosMoviles: "30 GB", precio: "$549 MXN" },
    { id: "P-30", nombre: "PerroniPhone Total", velocidad: "500 Mbps", datosMoviles: "Ilimitados", precio: "$799 MXN" },
  ],

  // Bitácora de seguridad ficticia mostrada en el dashboard/administración.
  bitacora: [
    { hora: "08:47:12", tipo: "ok", mensaje: "Inicio de sesión correcto — cliente@perroniphone.edu.mx" },
    { hora: "08:44:05", tipo: "warn", mensaje: "Intento de acceso con formato de correo inválido" },
    { hora: "07:58:41", tipo: "danger", mensaje: "5º intento fallido — cuenta bloqueada automáticamente (U-1004)" },
    { hora: "07:12:30", tipo: "ok", mensaje: "Cambio de rol aplicado por U-1001 (administrador)" },
    { hora: "06:30:02", tipo: "warn", mensaje: "Token de sesión expirado — se solicitó nuevo inicio de sesión" },
  ],
};
