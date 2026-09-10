/**
 * PerroniPhone — Cliente de la API (backend Laravel + MySQL, vía Docker)
 * ----------------------------------------------------------------
 * La API ahora corre detrás de nginx (contenedor Docker), expuesta en
 * el puerto 8080 de tu máquina. Si cambian el puerto en docker-compose.yml
 * (la línea "8080:80" del servicio nginx), actualiza también esta constante.
 */
const API_BASE = "http://localhost:8080/api";

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
      ? { "Content-Type": "application/json", Authorization: `Bearer ${token}` }
      : { "Content-Type": "application/json" };
  }

  async function login(correo, password) {
    const resp = await fetch(`${API_BASE}/auth/login`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
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
    const resp = await fetch(`${API_BASE}/planes`);
    return manejarRespuesta(resp);
  }

  return { login, obtenerUsuarios, actualizarRol, actualizarEstado, obtenerBitacora, obtenerPlanes };
})();
