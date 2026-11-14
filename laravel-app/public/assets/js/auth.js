/**
 * PerroniPhone — Sesión del lado del cliente
 * ------------------------------------------
 * El token de sesión es un JWT FIRMADO por el servidor. Los permisos del
 * usuario llegan en la respuesta del login (vienen de la tabla "permisos",
 * sincronizada desde laravel-app/database/data/permisos.json), así que el
 * frontend no tiene su propia copia de la matriz.
 *
 * Todo lo que hace este archivo es solo para mostrar/ocultar UI: el control
 * de acceso real ocurre en cada endpoint de la API (middleware "permiso").
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
      // Sesiones guardadas por una versión anterior (sin permisos) no sirven.
      if (new Date(datos.expira) <= new Date() || !Array.isArray(datos.usuario?.permisos)) {
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

  function tienePermiso(permiso) {
    return obtenerSesion()?.usuario.permisos.includes(permiso) ?? false;
  }

  // Protege una página: exige sesión vigente y, opcionalmente, un permiso.
  function requerirSesion(permiso = null) {
    const sesion = obtenerSesion();
    if (!sesion) {
      window.location.href = "login.html?motivo=sesion";
      return null;
    }
    if (permiso && !sesion.usuario.permisos.includes(permiso)) {
      window.location.href = "dashboard.html?motivo=permiso";
      return null;
    }
    return sesion;
  }

  /**
   * Prepara la barra lateral común a las páginas internas:
   *  - tarjeta con nombre y rol de la sesión,
   *  - botón de cerrar sesión,
   *  - enlaces con data-permiso="..." bloqueados si el rol no lo tiene.
   */
  function prepararMenu(sesion) {
    const u = sesion.usuario;
    document.getElementById("session-card").innerHTML = `
      <div class="who">${escapeHTML(u.nombre)}</div>
      <span class="role-pill ${escapeHTML(u.rol)}">${escapeHTML(u.rol)}</span>`;

    document.getElementById("btn-logout").addEventListener("click", () => {
      cerrarSesion();
      window.location.href = "login.html";
    });

    document.querySelectorAll(".app-nav a[data-permiso]").forEach((link) => {
      if (u.permisos.includes(link.dataset.permiso)) return;
      link.classList.add("locked");
      link.removeAttribute("href");
      link.title = "Tu rol no tiene acceso a esta sección";
    });
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
    prepararMenu,
  };
})();
