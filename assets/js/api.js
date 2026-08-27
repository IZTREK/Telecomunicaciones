/**
 * PerroniPhone — Cliente de la API (backend ASP.NET Core + SQL Server)
 * ----------------------------------------------------------------
 * Cambia API_BASE si tu API corre en otro puerto/URL. El puerto por
 * defecto en Properties/launchSettings.json es http://localhost:5205.
 * Verifica el puerto real en la consola cuando ejecutes `dotnet run`.
 */
const API_BASE = "http://localhost:5205/api";

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
