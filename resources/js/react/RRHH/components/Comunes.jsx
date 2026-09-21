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

/**
 * Adjuntar un archivo (se guarda en la carpeta del empleado en el Drive).
 * Muestra el actual (`actual` = {id, nombre}) si lo hay.
 */
export function CampoAdjunto({ archivo, onChange, actual = null, texto = "Adjuntar archivo", carpeta, error, maxMb = 50 }) {
    return (
        <div>
            {actual && !archivo && (
                <a
                    href={`/drive/ver/${actual.id}`}
                    target="_blank"
                    rel="noopener"
                    className="mb-3 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-cyan-700 hover:bg-cyan-50"
                >
                    <i className="mgc_file_line"></i>
                    <span className="min-w-0 flex-1 break-all">{actual.nombre}</span>
                    <i className="mgc_eye_line"></i>
                </a>
            )}
            <label className="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 px-4 py-3 transition hover:border-cyan-400 hover:bg-cyan-50/40">
                <i className="mgc_upload_2_line text-xl text-cyan-600"></i>
                <span className="min-w-0 flex-1 text-sm">
                    <span className="block break-all font-medium text-slate-700">
                        {archivo ? archivo.name : actual ? "Sustituir el archivo" : texto}
                    </span>
                    <span className="block text-xs text-slate-500">
                        {carpeta ? `Se guarda en su carpeta del Drive («${carpeta}») · ` : ""}máx. {maxMb} MB
                    </span>
                </span>
                {archivo && (
                    <button
                        type="button"
                        onClick={(e) => {
                            e.preventDefault();
                            onChange(null);
                        }}
                        aria-label="Quitar archivo"
                        className="text-slate-400 hover:text-rose-600"
                    >
                        <i className="mgc_close_line"></i>
                    </button>
                )}
                <input
                    type="file"
                    className="sr-only"
                    onChange={(e) => {
                        const f = e.target.files?.[0] ?? null;
                        e.target.value = "";
                        if (f && f.size > maxMb * 1024 * 1024) {
                            onChange(null, `El archivo supera ${maxMb} MB.`);
                            return;
                        }
                        onChange(f);
                    }}
                />
            </label>
            {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}

/** Input de importe con el símbolo € a la derecha. */
export function InputEuro({ value, onChange, error, disabled = false, placeholder = "0,00", ...resto }) {
    return (
        <div className="relative">
            <input
                type="number"
                step="0.01"
                min="0"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                disabled={disabled}
                placeholder={placeholder}
                className={claseInput(error, `pr-8 ${disabled ? "bg-slate-100 text-slate-500" : ""}`)}
                {...resto}
            />
            <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">€</span>
        </div>
    );
}

/** Selector de pocas opciones como botones. */
export function Segmentado({ opciones, value, onChange, columnas }) {
    const lista = Object.entries(opciones);
    return (
        <div className="grid gap-1 rounded-xl bg-slate-100 p-1" style={{ gridTemplateColumns: `repeat(${columnas ?? lista.length}, minmax(0, 1fr))` }}>
            {lista.map(([valor, texto]) => (
                <button
                    key={valor}
                    type="button"
                    onClick={() => onChange(valor)}
                    className={`rounded-lg px-2 py-1.5 text-sm font-semibold transition ${
                        value === valor ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-800"
                    }`}
                >
                    {texto}
                </button>
            ))}
        </div>
    );
}

/** Cabecera + contenido de una pestaña de la ficha. */
export function Panel({ titulo, icono, acciones, children }) {
    return (
        <section className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <h3 className="flex items-center gap-2 font-semibold text-slate-900">
                    <i className={`${icono} text-lg text-cyan-600`}></i>
                    {titulo}
                </h3>
                {acciones && <div className="flex flex-wrap items-center gap-2">{acciones}</div>}
            </div>
            {children}
        </section>
    );
}

/** Botón pequeño de acción en listas. */
export function BotonIcono({ icono, titulo, onClick, peligro = false, href, target }) {
    const clases = `inline-flex h-8 w-8 items-center justify-center rounded-lg transition ${
        peligro ? "text-rose-500 hover:bg-rose-50 hover:text-rose-700" : "text-slate-500 hover:bg-slate-100 hover:text-cyan-700"
    }`;
    return href ? (
        <a href={href} target={target} rel="noopener" title={titulo} aria-label={titulo} className={clases}>
            <i className={icono}></i>
        </a>
    ) : (
        <button type="button" onClick={onClick} title={titulo} aria-label={titulo} className={clases}>
            <i className={icono}></i>
        </button>
    );
}

/** Tarjeta de cifra resumen. */
export function Cifra({ etiqueta, valor, tono = "slate", detalle }) {
    const tonos = {
        slate: "border-slate-200 bg-slate-50/70 text-slate-900",
        emerald: "border-emerald-200 bg-emerald-50 text-emerald-800",
        amber: "border-amber-200 bg-amber-50 text-amber-800",
        rose: "border-rose-200 bg-rose-50 text-rose-800",
        cyan: "border-cyan-200 bg-cyan-50 text-cyan-800",
    };
    return (
        <div className={`rounded-2xl border px-4 py-3 ${tonos[tono]}`}>
            <p className="text-[11px] font-semibold uppercase tracking-[0.12em] opacity-70">{etiqueta}</p>
            <p className="mt-1 text-lg font-bold sm:text-xl">{valor}</p>
            {detalle && <p className="text-[11px] opacity-70">{detalle}</p>}
        </div>
    );
}
