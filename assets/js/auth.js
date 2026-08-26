/**
 * PerroniPhone — Módulo de autenticación y controles de seguridad
 * -----------------------------------------------------------
 * Este es un portal 100% estático (HTML/CSS/JS) sin servidor real,
 * por lo que estos controles son una SIMULACIÓN pedagógica de
 * prácticas de seguridad. En SECURITY.md se documenta, control por
 * control, qué se está demostrando y cómo debería implementarse
 * en un entorno de producción con backend.
 */

const PERRONI_AUTH = (() => {
  const SESSION_KEY = "perroni_session";
  const CSRF_KEY = "perroni_csrf";
  const MAX_INTENTOS = 5;
  const SESSION_MINUTOS = 20;

  // ---------- Utilidades de seguridad ----------

  // Previene inyección de HTML/XSS al pintar datos dinámicos en el DOM.
  function escapeHTML(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // Hash SHA-256 (Web Crypto API) — las contraseñas nunca viajan ni se
  // comparan en texto plano, incluso en este contexto de demostración.
  async function sha256Hex(texto) {
    const buffer = new TextEncoder().encode(texto);
    const hashBuffer = await crypto.subtle.digest("SHA-256", buffer);
    return Array.from(new Uint8Array(hashBuffer))
      .map((b) => b.toString(16).padStart(2, "0"))
      .join("");
  }

  async function verificarPassword(usuario, passwordIntento) {
    const hashIntento = await sha256Hex(usuario.salt + passwordIntento);
    return hashIntento === usuario.hash;
  }

  function validarFormatoCorreo(correo) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
  }

  // Política mínima de complejidad (se usa solo para retroalimentación visual).
  function fuerzaPassword(pw) {
    let puntos = 0;
    if (pw.length >= 8) puntos++;
    if (/[A-Z]/.test(pw)) puntos++;
    if (/[0-9]/.test(pw)) puntos++;
    if (/[^A-Za-z0-9]/.test(pw)) puntos++;
    return puntos; // 0-4
  }

  // Token de sesión simulado: en producción esto debe ser un JWT firmado
  // por el servidor o, preferentemente, una cookie httpOnly + Secure +
  // SameSite=Strict con el estado de sesión guardado en el servidor.
  function crearSesion(usuario) {
    const payload = {
      id: usuario.id,
      nombre: usuario.nombre,
      correo: usuario.correo,
      rol: usuario.rol,
      iat: Date.now(),
      exp: Date.now() + SESSION_MINUTOS * 60 * 1000,
    };
    sessionStorage.setItem(SESSION_KEY, btoa(JSON.stringify(payload)));
    return payload;
  }

  function obtenerSesion() {
    const raw = sessionStorage.getItem(SESSION_KEY);
    if (!raw) return null;
    try {
      const payload = JSON.parse(atob(raw));
      if (Date.now() > payload.exp) {
        cerrarSesion();
        return null;
      }
      return payload;
    } catch {
      cerrarSesion();
      return null;
    }
  }

  function cerrarSesion() {
    sessionStorage.removeItem(SESSION_KEY);
  }

  // Token anti-CSRF de un solo uso por formulario. En un backend real,
  // el servidor lo emite y lo valida en cada envío; aquí se demuestra
  // el patrón cliente-servidor de forma simplificada.
  function generarTokenCSRF() {
    const token = crypto.randomUUID();
    sessionStorage.setItem(CSRF_KEY, token);
    return token;
  }

  function validarTokenCSRF(token) {
    return token && token === sessionStorage.getItem(CSRF_KEY);
  }

  // ---------- Control de intentos (fuerza bruta) ----------
  // Contador por pestaña/sesión de navegador; en producción debe vivir
  // en el servidor (por IP + cuenta) para no depender del cliente.
  function obtenerIntentos(correo) {
    const raw = sessionStorage.getItem("intentos_" + correo);
    return raw ? JSON.parse(raw) : { conteo: 0, bloqueadoHasta: 0 };
  }

  function registrarIntentoFallido(correo) {
    const data = obtenerIntentos(correo);
    data.conteo += 1;
    if (data.conteo >= MAX_INTENTOS) {
      data.bloqueadoHasta = Date.now() + 2 * 60 * 1000; // 2 min de bloqueo temporal
    }
    sessionStorage.setItem("intentos_" + correo, JSON.stringify(data));
    return data;
  }

  function limpiarIntentos(correo) {
    sessionStorage.removeItem("intentos_" + correo);
  }

  // ---------- Autenticación ----------
  async function iniciarSesion(correo, password, csrfToken) {
    if (!validarTokenCSRF(csrfToken)) {
      return { ok: false, motivo: "csrf", mensaje: "La solicitud no pudo validarse. Recarga la página e inténtalo de nuevo." };
    }
    if (!validarFormatoCorreo(correo)) {
      return { ok: false, motivo: "formato", mensaje: "Ingresa un correo institucional válido." };
    }

    const intentos = obtenerIntentos(correo);
    if (intentos.bloqueadoHasta > Date.now()) {
      const restante = Math.ceil((intentos.bloqueadoHasta - Date.now()) / 1000);
      return { ok: false, motivo: "bloqueo_temporal", mensaje: `Demasiados intentos. Intenta de nuevo en ${restante}s.` };
    }

    const usuario = PERRONI_DB.usuarios.find((u) => u.correo.toLowerCase() === correo.toLowerCase());

    // Mensaje genérico (no revela si el correo existe) para no facilitar enumeración de cuentas.
    const credencialesInvalidas = { ok: false, motivo: "credenciales", mensaje: "Correo o contraseña incorrectos." };

    if (!usuario) {
      registrarIntentoFallido(correo);
      return credencialesInvalidas;
    }
    if (usuario.estado === "bloqueado") {
      return { ok: false, motivo: "cuenta_bloqueada", mensaje: "Esta cuenta está bloqueada. Contacta a un administrador." };
    }

    const passwordCorrecto = await verificarPassword(usuario, password);
    if (!passwordCorrecto) {
      const data = registrarIntentoFallido(correo);
      if (data.conteo >= MAX_INTENTOS) {
        return { ok: false, motivo: "bloqueo_temporal", mensaje: "Demasiados intentos fallidos. Cuenta bloqueada temporalmente por 2 minutos." };
      }
      return credencialesInvalidas;
    }

    limpiarIntentos(correo);
    const sesion = crearSesion(usuario);
    return { ok: true, sesion };
  }

  // Protege una página: exige sesión vigente y, opcionalmente, un rol permitido.
  function requerirSesion(rolesPermitidos = null) {
    const sesion = obtenerSesion();
    if (!sesion) {
      window.location.href = "login.html?motivo=sesion";
      return null;
    }
    if (rolesPermitidos && !rolesPermitidos.includes(sesion.rol)) {
      window.location.href = "dashboard.html?motivo=permiso";
      return null;
    }
    return sesion;
  }

  function tienePermiso(rol, permiso) {
    return (PERRONI_DB.permisos[rol] || []).includes(permiso);
  }

  return {
    escapeHTML,
    sha256Hex,
    fuerzaPassword,
    validarFormatoCorreo,
    iniciarSesion,
    obtenerSesion,
    cerrarSesion,
    requerirSesion,
    tienePermiso,
    generarTokenCSRF,
  };
})();
