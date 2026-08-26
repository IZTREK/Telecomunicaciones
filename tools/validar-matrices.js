/**
 * Validador de las matrices de GRC.
 * -----------------------------------
 * Comprueba que los datos de assets/js/grc.js sean internamente coherentes:
 * que el nivel de cada riesgo corresponda a la matriz de calor, que las
 * referencias cruzadas entre activos, riesgos y controles existan en ambos
 * sentidos, y que la criticidad de cada activo sea el maximo de C, I y D.
 *
 * Uso:  node tools/validar-matrices.js
 */

const fs = require("fs");
const path = require("path");
const raiz = path.join(__dirname, "..");
const leer = (p) => fs.readFileSync(path.join(raiz, p), "utf8");

// Los archivos del portal declaran const de nivel superior: se evaluan juntos
// y se exponen en globalThis para poder inspeccionarlos desde aqui.
eval(
  leer("assets/js/grc.js") +
  leer("assets/js/db.js") +
  ";globalThis.PERRONI_GRC = PERRONI_GRC; globalThis.PERRONI_DB = PERRONI_DB;"
);

let fallos = 0;
const err = (m) => { console.log("FALLA: " + m); fallos++; };

// 1. El nivel declarado de cada riesgo debe coincidir con la matriz de calor.
for (const r of PERRONI_GRC.riesgos) {
  const calculado = PERRONI_GRC.nivelDe(r.probabilidad, r.impacto);
  if (calculado !== r.nivel) err(`${r.id}: nivel declarado ${r.nivel} vs matriz de calor ${calculado}`);
}

// 2. Cada referencia cruzada apunta a un elemento existente.
const idsActivos = PERRONI_GRC.activos.map(a => a.id);
const idsRiesgos = PERRONI_GRC.riesgos.map(r => r.id);
const idsControles = PERRONI_GRC.controles.map(c => c.id);

for (const r of PERRONI_GRC.riesgos) {
  if (!idsActivos.includes(r.activo)) err(`${r.id} referencia activo inexistente ${r.activo}`);
  for (const c of r.controles) if (!idsControles.includes(c)) err(`${r.id} referencia control inexistente ${c}`);
}
for (const a of PERRONI_GRC.activos) {
  for (const x of a.riesgos) if (!idsRiesgos.includes(x)) err(`${a.id} referencia riesgo inexistente ${x}`);
  for (const c of a.controles) if (!idsControles.includes(c)) err(`${a.id} referencia control inexistente ${c}`);
}
for (const c of PERRONI_GRC.controles) {
  for (const m of c.mitiga) if (!idsRiesgos.includes(m)) err(`${c.id} mitiga riesgo inexistente ${m}`);
}

// 3. La relacion control <-> riesgo es consistente en ambos sentidos.
for (const r of PERRONI_GRC.riesgos) {
  for (const cid of r.controles) {
    const c = PERRONI_GRC.controles.find(x => x.id === cid);
    if (c && !c.mitiga.includes(r.id)) err(`${r.id} lista ${cid}, pero ${cid} no dice mitigar ${r.id}`);
  }
}

// 4. La criticidad del activo es el maximo de C, I y D.
const peso = { Bajo: 0, Baja: 0, Medio: 1, Media: 1, Alto: 2, Alta: 2, "Crítico": 3, "Crítica": 3 };
for (const a of PERRONI_GRC.activos) {
  const max = Math.max(...Object.values(a.cia).map(v => peso[v]));
  if (peso[a.criticidad] !== max) err(`${a.id}: criticidad ${a.criticidad} no es el maximo de C/I/D`);
}

// 5. El permiso ver_grc existe en la matriz de permisos del portal.
const conGrc = Object.entries(PERRONI_DB.permisos).filter(([, p]) => p.includes("ver_grc")).map(([r]) => r);
if (conGrc.length === 0) err("ningun rol tiene el permiso ver_grc");
console.log("Roles con acceso a gobierno.html:", conGrc.join(", "));

// 6. Toda celda de la matriz de calor tiene un nivel definido.
for (const p of PERRONI_GRC.escalas.probabilidad)
  for (const i of PERRONI_GRC.escalas.impacto)
    if (PERRONI_GRC.nivelDe(p, i) === "\u2014") err(`celda sin nivel: ${p} x ${i}`);

console.log(fallos === 0 ? "\nTodas las validaciones pasaron." : `\n${fallos} falla(s).`);
process.exit(fallos === 0 ? 0 : 1);
