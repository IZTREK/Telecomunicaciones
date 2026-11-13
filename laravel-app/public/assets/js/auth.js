/**
 * PerroniPhone — Sesión del lado del cliente (versión con backend real)
 * ------------------------------------------------------------------
 * El token de sesión es un JWT FIRMADO por el servidor; el control de
 * acceso real ocurre en cada endpoint de la API (EnsureRole), lo que
 * hace este archivo en el navegador es solo para mostrar/ocultar UI.
 */
const PERRONI_AUTH = (() => {
  const SESSION_KEY = "perroni_session";

  // Catálogo central de roles y permisos: ÚNICA fuente de verdad en el
  // frontend. admin.html y gobierno.html lo consultan en vez de tener
  // cada uno su propia copia (que es justo como se desincronizó antes).
  // Debe reflejar lo mismo que hay en la tabla "permisos" del backend.
  const MAPA_PERMISOS = {
    administrador: ["ver_dashboard", "gestionar_usuarios", "ver_bitacora", "editar_roles", "ver_grc", "ver_contabilidad"],
    soporte: ["ver_dashboard", "ver_bitacora"],
    redes: ["ver_dashboard", "ver_grc"],
    encargado: ["ver_dashboard", "ver_contabilidad","gestionar_usuarios","ver_grc"],
    contabilidad: ["ver_dashboard", "ver_contabilidad"],
    cliente: ["ver_dashboard"],
  };

  function escapeHTML(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function validarFormatoCorreo(correo) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
  }

  function fuerzaPassword(pw) {
    let puntos = 0;
    if (pw.length >= 8) puntos++;
    if (/[A-Z]/.test(pw)) puntos++;
    if (/[0-9]/.test(pw)) puntos++;
    if (/[^A-Za-z0-9]/.test(pw)) puntos++;
    return puntos;
  }

  function guardarSesion(token, expira, usuario) {
    sessionStorage.setItem(SESSION_KEY, JSON.stringify({ token, expira, usuario }));
  }

  function obtenerSesion() {
    const raw = sessionStorage.getItem(SESSION_KEY);
    if (!raw) return null;
    try {
      const datos = JSON.parse(raw);
      if (new Date(datos.expira) <= new Date()) {
        cerrarSesion();
        return null;
      }
      return datos;
    } catch {
      cerrarSesion();
      return null;
    }
  }

  function cerrarSesion() {
    sessionStorage.removeItem(SESSION_KEY);
  }

  async function iniciarSesion(correo, password) {
    if (!validarFormatoCorreo(correo)) {
      return { ok: false, mensaje: "Ingresa un correo institucional válido." };
    }
    try {
      const respuesta = await PERRONI_API.login(correo, password);
      guardarSesion(respuesta.token, respuesta.expira, respuesta.usuario);
      return { ok: true, sesion: respuesta };
    } catch (err) {
      return { ok: false, mensaje: err.message || "No se pudo iniciar sesión." };
    }
  }

  function requerirSesion(rolesPermitidos = null) {
    const sesion = obtenerSesion();
    if (!sesion) {
      window.location.href = "login.html?motivo=sesion";
      return null;
    }
    if (rolesPermitidos && !rolesPermitidos.includes(sesion.usuario.rol)) {
      window.location.href = "dashboard.html?motivo=permiso";
      return null;
    }
    return sesion;
  }

  function tienePermiso(rol, permiso) {
    return (MAPA_PERMISOS[rol] || []).includes(permiso);
  }

  // Qué roles tienen cierto permiso (ej. "¿quién puede ver Gobierno/GRC?").
  // Se deriva del catálogo en vez de escribirse a mano en cada página.
  function rolesConPermiso(permiso) {
    return Object.entries(MAPA_PERMISOS)
      .filter(([, permisos]) => permisos.includes(permiso))
      .map(([rol]) => rol);
  }

  function obtenerRolesDisponibles() {
    return Object.keys(MAPA_PERMISOS);
  }

  function obtenerMapaPermisos() {
    return Object.fromEntries(Object.entries(MAPA_PERMISOS).map(([r, p]) => [r, [...p]]));
  }

  return {
    escapeHTML,
    fuerzaPassword,
    validarFormatoCorreo,
    iniciarSesion,
    obtenerSesion,
    cerrarSesion,
    requerirSesion,
    tienePermiso,
    rolesConPermiso,
    obtenerRolesDisponibles,
    obtenerMapaPermisos,
  };
})();