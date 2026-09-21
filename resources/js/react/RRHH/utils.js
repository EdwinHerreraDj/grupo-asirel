// Utilidades compartidas del módulo de Recursos humanos.

/** Estados de un apartado de documentación (ver EstadoDocumentacion.php). */
export const ESTADOS_DOCUMENTO = {
    falta: {
        texto: "Falta",
        clases: "border-rose-200 bg-rose-50 text-rose-700",
        icono: "mgc_close_circle_line",
    },
    vencido: {
        texto: "Caducado",
        clases: "border-rose-200 bg-rose-50 text-rose-700",
        icono: "mgc_warning_line",
    },
    proximo: {
        texto: "Caduca pronto",
        clases: "border-amber-200 bg-amber-50 text-amber-700",
        icono: "mgc_time_line",
    },
    sin_fecha: {
        texto: "Sin fecha de caducidad",
        clases: "border-amber-200 bg-amber-50 text-amber-700",
        icono: "mgc_calendar_line",
    },
    vigente: {
        texto: "Vigente",
        clases: "border-emerald-200 bg-emerald-50 text-emerald-700",
        icono: "mgc_check_circle_line",
    },
    entregado: {
        texto: "Entregado",
        clases: "border-emerald-200 bg-emerald-50 text-emerald-700",
        icono: "mgc_check_circle_line",
    },
    vacio: {
        texto: "Opcional",
        clases: "border-slate-200 bg-slate-50 text-slate-500",
        icono: "mgc_minus_circle_line",
    },
};

/** "2026-09-21" (o ISO) → "21/09/2026", sin desfases de zona horaria. */
export const fechaCorta = (valor) => {
    if (!valor) return "—";
    const [a, m, d] = String(valor).slice(0, 10).split("-");
    return a && m && d ? `${d}/${m}/${a}` : "—";
};

export const hoyISO = () => {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

/** "ES9121000418450200051332" → "ES91 2100 0418 4502 0005 1332" */
export const ibanLegible = (iban) =>
    iban ? String(iban).replace(/(.{4})/g, "$1 ").trim() : "";

/** Solo los 4 últimos dígitos visibles. */
export const ibanOculto = (iban) =>
    iban ? `${String(iban).slice(0, 4)} •••• •••• •••• •••• ${String(iban).slice(-4)}` : "";

/** Texto de los días que faltan para caducar. */
export const textoDias = (dias) => {
    if (dias === null || dias === undefined) return "";
    if (dias < 0) return `hace ${Math.abs(dias)} ${Math.abs(dias) === 1 ? "día" : "días"}`;
    if (dias === 0) return "hoy";
    return `en ${dias} ${dias === 1 ? "día" : "días"}`;
};

/** Primer error de validación de Laravel por campo. */
export const erroresDeValidacion = (error) => {
    const errores = error?.response?.data?.errors;
    if (!errores) return {};
    return Object.fromEntries(
        Object.entries(errores).map(([campo, mensajes]) => [
            campo,
            Array.isArray(mensajes) ? mensajes[0] : String(mensajes),
        ]),
    );
};

export const mensajeDeError = (error, porDefecto) =>
    error?.response?.data?.message || porDefecto;

export const inputBase =
    "w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500";
export const inputError = "border-red-400 focus:border-red-500 focus:ring-red-500";

export const botonPrimario =
    "inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] transition hover:from-cyan-500 hover:to-blue-500 disabled:cursor-not-allowed disabled:opacity-60";
export const botonSecundario =
    "inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60";
export const botonPeligro =
    "inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(225,29,72,0.22)] transition hover:from-rose-500 hover:to-red-500 disabled:cursor-not-allowed disabled:opacity-60";

/** Estados de obra (obras.estado). */
export const ESTADOS_OBRA = {
    ejecucion: { texto: "En ejecución", clases: "bg-emerald-50 text-emerald-700 border-emerald-200" },
    planificacion: { texto: "Planificación", clases: "bg-sky-50 text-sky-700 border-sky-200" },
    en_pausa: { texto: "En pausa", clases: "bg-amber-50 text-amber-700 border-amber-200" },
    finalizada: { texto: "Finalizada", clases: "bg-slate-100 text-slate-500 border-slate-200" },
};

/** Normaliza como el servidor: mayúsculas y sin espacios ni separadores. */
export const limpiarDni = (v) => String(v || "").toUpperCase().replace(/[\s.\-]/g, "");
export const limpiarIban = (v) => String(v || "").toUpperCase().replace(/\s+/g, "");

/** Comprobación previa (la definitiva la hace el servidor). null = vacío. */
export const dniValido = (valor) => {
    const v = limpiarDni(valor);
    if (!v) return null;
    const m = v.match(/^([XYZ]?)(\d{7,8})([A-Z])$/);
    if (!m || (m[1] ? m[2].length !== 7 : m[2].length !== 8)) return false;
    const numero = Number(({ X: "0", Y: "1", Z: "2" }[m[1]] ?? "") + m[2]);
    return "TRWAGMYFPDXBNJZSQVHLCKE"[numero % 23] === m[3];
};

export const ibanValido = (valor) => {
    const v = limpiarIban(valor);
    if (!v) return null;
    if (!/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/.test(v) || (v.startsWith("ES") && v.length !== 24)) return false;
    const numerico = (v.slice(4) + v.slice(0, 4)).replace(/[A-Z]/g, (c) => String(c.charCodeAt(0) - 55));
    let resto = 0;
    for (let i = 0; i < numerico.length; i += 7) resto = Number(String(resto) + numerico.slice(i, i + 7)) % 97;
    return resto === 1;
};

/** Colores de los tipos de ausencia (clases completas para Tailwind). */
export const COLORES_AUSENCIA = {
    cyan: { celda: "bg-cyan-500", suave: "border-cyan-200 bg-cyan-50 text-cyan-800", punto: "bg-cyan-500", barra: "bg-cyan-500", barraSuave: "bg-cyan-200" },
    sky: { celda: "bg-sky-500", suave: "border-sky-200 bg-sky-50 text-sky-800", punto: "bg-sky-500", barra: "bg-sky-500", barraSuave: "bg-sky-200" },
    blue: { celda: "bg-blue-500", suave: "border-blue-200 bg-blue-50 text-blue-800", punto: "bg-blue-500", barra: "bg-blue-500", barraSuave: "bg-blue-200" },
    indigo: { celda: "bg-indigo-500", suave: "border-indigo-200 bg-indigo-50 text-indigo-800", punto: "bg-indigo-500", barra: "bg-indigo-500", barraSuave: "bg-indigo-200" },
    violet: { celda: "bg-violet-500", suave: "border-violet-200 bg-violet-50 text-violet-800", punto: "bg-violet-500", barra: "bg-violet-500", barraSuave: "bg-violet-200" },
    pink: { celda: "bg-pink-500", suave: "border-pink-200 bg-pink-50 text-pink-800", punto: "bg-pink-500", barra: "bg-pink-500", barraSuave: "bg-pink-200" },
    rose: { celda: "bg-rose-500", suave: "border-rose-200 bg-rose-50 text-rose-800", punto: "bg-rose-500", barra: "bg-rose-500", barraSuave: "bg-rose-200" },
    red: { celda: "bg-red-600", suave: "border-red-200 bg-red-50 text-red-800", punto: "bg-red-600", barra: "bg-red-600", barraSuave: "bg-red-200" },
    orange: { celda: "bg-orange-500", suave: "border-orange-200 bg-orange-50 text-orange-800", punto: "bg-orange-500", barra: "bg-orange-500", barraSuave: "bg-orange-200" },
    amber: { celda: "bg-amber-500", suave: "border-amber-200 bg-amber-50 text-amber-800", punto: "bg-amber-500", barra: "bg-amber-500", barraSuave: "bg-amber-200" },
    emerald: { celda: "bg-emerald-500", suave: "border-emerald-200 bg-emerald-50 text-emerald-800", punto: "bg-emerald-500", barra: "bg-emerald-500", barraSuave: "bg-emerald-200" },
    teal: { celda: "bg-teal-500", suave: "border-teal-200 bg-teal-50 text-teal-800", punto: "bg-teal-500", barra: "bg-teal-500", barraSuave: "bg-teal-200" },
    slate: { celda: "bg-slate-500", suave: "border-slate-200 bg-slate-100 text-slate-700", punto: "bg-slate-500", barra: "bg-slate-500", barraSuave: "bg-slate-200" },
};

export const colorAusencia = (color) => COLORES_AUSENCIA[color] ?? COLORES_AUSENCIA.slate;

export const MESES = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
];

/** Iniciales de lunes (1) a domingo (7). */
export const DIAS_SEMANA = { 1: "L", 2: "M", 3: "X", 4: "J", 5: "V", 6: "S", 7: "D" };

/** Número con coma decimal y sin ceros sobrantes: 15.1 → "15,1", 14 → "14". */
export const numeroDias = (n) =>
    new Intl.NumberFormat("es-ES", { maximumFractionDigits: 1 }).format(Number(n) || 0);

/** Días naturales entre dos fechas ISO (ambas incluidas). */
export const diasNaturales = (desde, hasta) => {
    if (!desde || !hasta) return null;
    const d = (Date.parse(hasta) - Date.parse(desde)) / 86400000;
    return Number.isFinite(d) && d >= 0 ? Math.round(d) + 1 : null;
};

/**
 * FormData para la API: arrays como campo[], null/undefined como "" (el
 * servidor lo convierte en null), archivo opcional y método (PUT) simulado.
 */
export const aFormData = (datos, archivo = null, metodo = null) => {
    const f = new FormData();
    Object.entries(datos).forEach(([k, v]) => {
        if (Array.isArray(v)) v.forEach((x) => f.append(`${k}[]`, x));
        else if (typeof v === "boolean") f.append(k, v ? "1" : "0");
        else f.append(k, v ?? "");
    });
    if (archivo) f.append("archivo", archivo);
    if (metodo) f.append("_method", metodo);
    return f;
};

/** Número desde input ("" → 0). */
export const num = (v) => {
    const n = parseFloat(String(v ?? "").replace(",", "."));
    return Number.isFinite(n) ? n : 0;
};

/** Suma días a una fecha ISO (YYYY-MM-DD) sin problemas de zona horaria. */
export const sumarDias = (fecha, n) => {
    const [a, m, d] = fecha.split("-").map(Number);
    const r = new Date(a, m - 1, d + n);
    return `${r.getFullYear()}-${String(r.getMonth() + 1).padStart(2, "0")}-${String(r.getDate()).padStart(2, "0")}`;
};

/** Lunes de la semana de una fecha ISO. */
export const lunesDe = (fecha) => {
    const [a, m, d] = fecha.split("-").map(Number);
    const dia = new Date(a, m - 1, d).getDay(); // 0 = domingo
    return sumarDias(fecha, dia === 0 ? -6 : 1 - dia);
};

/** "7 ene" */
export const fechaDiaMes = (fecha) => {
    const [, m, d] = fecha.split("-").map(Number);
    return `${d} ${MESES[m - 1].slice(0, 3).toLowerCase()}`;
};

/** Horas de un horario HH:MM–HH:MM (+ segundo tramo) menos descanso. */
export const horasTurno = ({ hora_inicio, hora_fin, hora_inicio_2, hora_fin_2, descanso_minutos }) => {
    const min = (i, f) => {
        if (!i || !f) return 0;
        const [hi, mi] = i.split(":").map(Number);
        const [hf, mf] = f.split(":").map(Number);
        const diff = hf * 60 + mf - (hi * 60 + mi);
        return diff <= 0 ? diff + 1440 : diff;
    };
    const total = min(hora_inicio, hora_fin) + min(hora_inicio_2, hora_fin_2) - (Number(descanso_minutos) || 0);
    return Math.max(0, total) / 60;
};
