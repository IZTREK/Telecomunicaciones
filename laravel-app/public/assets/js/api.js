/**
 * PerroniPhone — Cliente de la API (backend Laravel + MySQL, vía Docker)
 * ----------------------------------------------------------------
 * Frontend y API ahora se sirven desde el MISMO origen (el mismo nginx,
 * puerto 8080), así que la URL es relativa: funciona igual en
 * http://localhost:8080 que detrás de un túnel de ngrok, sin tener que
 * cambiar nada entre ambiente local y público.
 */
const API_BASE = "/api";

// El plan gratuito de ngrok muestra una página de advertencia ("¿seguro
// que quieres visitar este sitio?") antes de dejar pasar la petición.
// Este header le dice a ngrok que la salte — sin él, cada fetch() a la
// API recibiría esa página HTML en vez de la respuesta JSON real.
const HEADERS_NGROK = { "ngrok-skip-browser-warning": "true" };

const PERRONI_API = (() => {
  async function manejarRespuesta(resp) {
    if (resp.status === 204) return null;
    let cuerpo = null;
    try { cuerpo = await resp.json(); } catch { /* respuesta sin cuerpo */ }

    if (!resp.ok) {
      const error = new Error(cuerpo?.mensaje || `Error HTTP ${resp.status}`);
      error.status = resp.status;
      throw error;
    }
    return cuerpo;
  }

  function headersAuth(token) {
    return token
      ? { "Content-Type": "application/json", Authorization: `Bearer ${token}`, ...HEADERS_NGROK }
      : { "Content-Type": "application/json", ...HEADERS_NGROK };
  }

  async function login(correo, password) {
    const resp = await fetch(`${API_BASE}/auth/login`, {
      method: "POST",
      headers: { "Content-Type": "application/json", ...HEADERS_NGROK },
      body: JSON.stringify({ correo, password }),
    });
    return manejarRespuesta(resp);
  }

  async function obtenerUsuarios(token) {
    const resp = await fetch(`${API_BASE}/usuarios`, { headers: headersAuth(token) });
    return manejarRespuesta(resp);
  }

  async function actualizarRol(token, id, rol) {
    const resp = await fetch(`${API_BASE}/usuarios/${id}/rol`, {
      method: "PUT",
      headers: headersAuth(token),
      body: JSON.stringify({ rol }),
    });
    return manejarRespuesta(resp);
  }

  async function actualizarEstado(token, id, estado) {
    const resp = await fetch(`${API_BASE}/usuarios/${id}/estado`, {
      method: "PUT",
      headers: headersAuth(token),
      body: JSON.stringify({ estado }),
    });
    return manejarRespuesta(resp);
  }

  async function obtenerBitacora(token) {
    const resp = await fetch(`${API_BASE}/bitacora`, { headers: headersAuth(token) });
    return manejarRespuesta(resp);
  }

  async function obtenerPlanes() {
    const resp = await fetch(`${API_BASE}/planes`, { headers: HEADERS_NGROK });
    return manejarRespuesta(resp);
  }

  async function obtenerContabilidad(token) {
    const resp = await fetch(`${API_BASE}/contabilidad`, { headers: headersAuth(token) });
    return manejarRespuesta(resp);
  }

  return {
    login, obtenerUsuarios, actualizarRol, actualizarEstado,
    obtenerBitacora, obtenerPlanes, obtenerContabilidad,
  };
})();