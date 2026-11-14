/**
 * PerroniPhone — Cliente de la API (backend Laravel + MySQL, vía Docker)
 * ----------------------------------------------------------------
 * Frontend y API se sirven desde el MISMO origen (el mismo nginx, puerto
 * 8080), así que la URL es relativa: funciona igual en
 * http://localhost:8080 que detrás de un túnel de ngrok.
 */
const API_BASE = "/api";

// El plan gratuito de ngrok muestra una página de advertencia antes de
// dejar pasar la petición. Este header le dice que la salte — sin él,
// cada fetch() recibiría esa página HTML en vez del JSON. En local no hace nada.
const HEADERS_NGROK = { "ngrok-skip-browser-warning": "true" };

const PERRONI_API = (() => {
  async function manejarRespuesta(resp, conToken = false) {
    if (resp.status === 204) return null;
    let cuerpo = null;
    try { cuerpo = await resp.json(); } catch { /* respuesta sin cuerpo */ }

    // 401 en una petición autenticada = el token ya no sirve (expiró, el
    // servidor se reinició con otra llave, o la cuenta fue bloqueada).
    // Se cierra la sesión local y se manda al login.
    if (resp.status === 401 && conToken && typeof PERRONI_AUTH !== "undefined") {
      PERRONI_AUTH.cerrarSesion();
      window.location.href = "login.html?motivo=sesion";
    }

    if (!resp.ok) {
      // Nuestros controladores responden { mensaje }; los errores propios
      // de Laravel (validación, 404 de ruta, etc.) responden { message }.
      const error = new Error(cuerpo?.mensaje || cuerpo?.message || `Error HTTP ${resp.status}`);
      error.status = resp.status;
      throw error;
    }
    return cuerpo;
  }

  function headers(token = null) {
    const h = { "Content-Type": "application/json", Accept: "application/json", ...HEADERS_NGROK };
    if (token) h.Authorization = `Bearer ${token}`;
    return h;
  }

  async function get(ruta, token = null) {
    const resp = await fetch(`${API_BASE}${ruta}`, { headers: headers(token) });
    return manejarRespuesta(resp, Boolean(token));
  }

  async function put(ruta, token, cuerpo) {
    const resp = await fetch(`${API_BASE}${ruta}`, {
      method: "PUT",
      headers: headers(token),
      body: JSON.stringify(cuerpo),
    });
    return manejarRespuesta(resp, true);
  }

  async function login(correo, password) {
    const resp = await fetch(`${API_BASE}/auth/login`, {
      method: "POST",
      headers: headers(),
      body: JSON.stringify({ correo, password }),
    });
    return manejarRespuesta(resp);
  }

  return {
    login,
    obtenerPlanes: () => get("/planes"),
    obtenerUsuarios: (token) => get("/usuarios", token),
    obtenerPermisos: (token) => get("/permisos", token),
    obtenerBitacora: (token) => get("/bitacora", token),
    obtenerContabilidad: (token) => get("/contabilidad", token),
    actualizarRol: (token, id, rol) => put(`/usuarios/${id}/rol`, token, { rol }),
    actualizarEstado: (token, id, estado) => put(`/usuarios/${id}/estado`, token, { estado }),
  };
})();
