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
