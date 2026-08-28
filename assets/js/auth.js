/**
 * PerroniPhone — Sesión del lado del cliente (versión con backend real)
 * ------------------------------------------------------------------
 * A diferencia de la versión sin servidor, aquí:
 *  - La contraseña se envía al backend y se verifica ahí (PBKDF2).
 *  - El token de sesión es un JWT FIRMADO por el servidor: el cliente
 *    ya no puede alterar su rol o id sin invalidar la firma.
 *  - El control de acceso real ocurre en cada endpoint de la API
 *    ([Authorize(Roles=...)]); lo que hace este archivo en el
 *    navegador es solo para mostrar/ocultar UI, nunca es la barrera
 *    de seguridad real.
 */
const PERRONI_AUTH = (() => {
  const SESSION_KEY = "perroni_session";
 
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
 
  // Protege una página: exige sesión vigente y, opcionalmente, un rol permitido
  // (esto solo mejora la experiencia de UI; la API vuelve a validar todo).
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
    const mapa = {
      administrador: ["ver_dashboard", "gestionar_usuarios", "ver_bitacora", "editar_roles", "ver_grc"],
      soporte: ["ver_dashboard", "ver_bitacora", "ver_grc"],
      cliente: ["ver_dashboard"],
    };
    return (mapa[rol] || []).includes(permiso);
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
  };
})();