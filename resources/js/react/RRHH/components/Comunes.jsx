import React, { useEffect } from "react";
import useLockBodyScroll from "../../shared/useLockBodyScroll";
import { ESTADOS_DOCUMENTO, inputBase, inputError } from "../utils";

/**
 * Modal del módulo: bloquea el scroll del body, se cierra con Escape y en
 * móvil aparece como hoja inferior a pantalla completa de ancho.
 */
export function Modal({ etiqueta, titulo, subtitulo, onCerrar, children, pie, ancho = "max-w-2xl", refCuerpo, cabeceraExtra }) {
    useLockBodyScroll(true);

    useEffect(() => {
        const alPulsar = (e) => e.key === "Escape" && onCerrar?.();
        document.addEventListener("keydown", alPulsar);
        return () => document.removeEventListener("keydown", alPulsar);
    }, [onCerrar]);

    return (
        <div
            className="fixed inset-0 z-[999] flex items-end justify-center bg-slate-900/60 backdrop-blur-sm sm:items-center sm:px-4 sm:py-6"
            role="dialog"
            aria-modal="true"
        >
            <div className={`flex max-h-[92vh] w-full ${ancho} flex-col overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl sm:rounded-3xl`}>
                <div className="shrink-0 border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-4 sm:px-6 sm:py-5">
                    <div className="flex items-start justify-between gap-3">
                        <div className="min-w-0">
                            {etiqueta && (
                                <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                    <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                    {etiqueta}
                                </div>
                            )}
                            <h3 className="mt-2 break-words text-lg font-semibold text-slate-900">{titulo}</h3>
                            {subtitulo && <p className="mt-0.5 text-sm text-slate-500">{subtitulo}</p>}
                            {cabeceraExtra}
                        </div>
                        <button
                            type="button"
                            onClick={onCerrar}
                            aria-label="Cerrar"
                            className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        >
                            <i className="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                <div ref={refCuerpo} className="relative overflow-y-auto overscroll-contain px-5 py-5 sm:px-6">{children}</div>

                {pie && (
                    <div className="shrink-0 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">{pie}</div>
                    </div>
                )}
            </div>
        </div>
    );
}

/** Campo de formulario con etiqueta y error. */
export function Campo({ etiqueta, obligatorio = false, error, ayuda, className = "", children }) {
    return (
        <div className={className}>
            <label className="mb-1 block text-sm font-medium text-slate-700">
                {etiqueta} {obligatorio && <span className="text-red-500">*</span>}
            </label>
            {children}
            {error ? (
                <p className="mt-1 text-xs text-red-600">{error}</p>
            ) : (
                ayuda && <p className="mt-1 text-xs text-slate-500">{ayuda}</p>
            )}
        </div>
    );
}

/** Clases de input según haya error. */
export const claseInput = (error, extra = "") => `${inputBase} ${error ? inputError : ""} ${extra}`;

export function EstadoDocumento({ estado, className = "" }) {
    const info = ESTADOS_DOCUMENTO[estado] ?? ESTADOS_DOCUMENTO.vacio;
    return (
        <span className={`inline-flex items-center gap-1 whitespace-nowrap rounded-full border px-2.5 py-0.5 text-xs font-semibold ${info.clases} ${className}`}>
            <i className={info.icono}></i>
            {info.texto}
        </span>
    );
}

export function EstadoEmpleado({ estado }) {
    return estado === "activo" ? (
        <span className="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            De alta
        </span>
    ) : (
        <span className="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
            <span className="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
            De baja
        </span>
    );
}

/** Resumen compacto de problemas de documentación de un empleado. */
export function ResumenDocumentacion({ resumen }) {
    if (!resumen) return <span className="text-xs text-slate-400">—</span>;

    if (!resumen.total) {
        return (
            <span className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                <i className="mgc_check_circle_line"></i> Al día
            </span>
        );
    }

    const partes = [
        resumen.faltan && { n: resumen.faltan, t: "faltan", c: "bg-rose-50 text-rose-700 border-rose-200" },
        resumen.vencidos && { n: resumen.vencidos, t: resumen.vencidos === 1 ? "caducado" : "caducados", c: "bg-rose-50 text-rose-700 border-rose-200" },
        resumen.proximos && { n: resumen.proximos, t: "caducan pronto", c: "bg-amber-50 text-amber-700 border-amber-200" },
        resumen.sin_fecha && { n: resumen.sin_fecha, t: "sin fecha", c: "bg-amber-50 text-amber-700 border-amber-200" },
    ].filter(Boolean);

    return (
        <div className="flex flex-wrap gap-1">
            {partes.map((p) => (
                <span key={p.t} className={`whitespace-nowrap rounded-full border px-2 py-0.5 text-[11px] font-semibold ${p.c}`}>
                    {p.n} {p.t}
                </span>
            ))}
        </div>
    );
}

export function Cargando({ texto = "Cargando…" }) {
    return (
        <div className="flex items-center justify-center gap-2 py-12 text-sm text-slate-500">
            <i className="mgc_loading_line animate-spin text-lg"></i>
            {texto}
        </div>
    );
}

export function Vacio({ icono = "mgc_group_line", titulo, texto, children }) {
    return (
        <div className="flex flex-col items-center justify-center px-4 py-12 text-center">
            <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <i className={`${icono} text-2xl`}></i>
            </div>
            <p className="mt-3 font-semibold text-slate-700">{titulo}</p>
            {texto && <p className="mt-1 max-w-md text-sm text-slate-500">{texto}</p>}
            {children && <div className="mt-4">{children}</div>}
        </div>
    );
}

/** Colores de icono de las secciones (clases completas para Tailwind). */
export const COLORES_SECCION = {
    cyan: "bg-cyan-100 text-cyan-700",
    violet: "bg-violet-100 text-violet-700",
    amber: "bg-amber-100 text-amber-700",
    emerald: "bg-emerald-100 text-emerald-700",
    indigo: "bg-indigo-100 text-indigo-700",
    rose: "bg-rose-100 text-rose-700",
    slate: "bg-slate-100 text-slate-600",
    sky: "bg-sky-100 text-sky-700",
};

/**
 * Apartado de un formulario dentro de un modal: tarjeta con icono, título,
 * descripción y aviso si tiene errores.
 */
export const SeccionFormulario = React.forwardRef(function SeccionFormulario(
    { id, titulo, descripcion, icono, color = "cyan", conError = false, children, className = "" },
    ref,
) {
    return (
        <section
            id={id}
            ref={ref}
            className={`scroll-mt-20 overflow-hidden rounded-2xl border bg-white md:scroll-mt-2 ${
                conError ? "border-red-200 ring-1 ring-red-100" : "border-slate-200"
            } ${className}`}
        >
            <header className="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 sm:px-5">
                <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${COLORES_SECCION[color] ?? COLORES_SECCION.cyan}`}>
                    <i className={`${icono} text-lg`}></i>
                </span>
                <div className="min-w-0 flex-1">
                    <h4 className="text-sm font-semibold text-slate-900">{titulo}</h4>
                    {descripcion && <p className="text-xs text-slate-500">{descripcion}</p>}
                </div>
                {conError && (
                    <span className="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600">
                        <i className="mgc_warning_line"></i> Revisar
                    </span>
                )}
            </header>
            <div className="p-4 sm:p-5">{children}</div>
        </section>
    );
});

/** Obras del empleado como etiquetas; con `max` se resume el resto en "+N". */
export function ObrasChips({ obras, max = null, vacio = "—" }) {
    if (!obras?.length) return <span className="text-slate-400">{vacio}</span>;
    const visibles = max ? obras.slice(0, max) : obras;
    const resto = obras.length - visibles.length;
    return (
        <div className="flex flex-wrap gap-1">
            {visibles.map((o) => (
                <span
                    key={o.id ?? o}
                    className="inline-flex max-w-[14rem] items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-700"
                    title={o.nombre ?? o}
                >
                    <i className="mgc_building_2_line text-slate-400"></i>
                    <span className="truncate">{o.nombre ?? o}</span>
                </span>
            ))}
            {resto > 0 && (
                <span
                    className="rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-xs font-semibold text-slate-500"
                    title={obras.slice(visibles.length).map((o) => o.nombre ?? o).join(", ")}
                >
                    +{resto}
                </span>
            )}
        </div>
    );
}

/** Aviso informativo dentro de un modal. */
export function Aviso({ tipo = "info", icono, children }) {
    const estilos = {
        info: "border-sky-200 bg-sky-50 text-sky-800",
        aviso: "border-amber-200 bg-amber-50 text-amber-800",
        peligro: "border-rose-200 bg-rose-50 text-rose-800",
    };
    const iconos = { info: "mgc_information_line", aviso: "mgc_warning_line", peligro: "mgc_warning_line" };
    return (
        <div className={`flex gap-2.5 rounded-xl border px-3.5 py-3 text-sm ${estilos[tipo]}`}>
            <i className={`${icono ?? iconos[tipo]} mt-0.5 shrink-0 text-base`}></i>
            <div className="min-w-0">{children}</div>
        </div>
    );
}
