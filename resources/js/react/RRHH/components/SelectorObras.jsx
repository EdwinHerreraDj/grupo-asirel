import React, { useEffect, useRef, useState } from "react";
import api from "../../shared/api";
import { ESTADOS_OBRA, inputBase, inputError } from "../utils";

/**
 * Selector de obras con buscador (consulta al servidor, máx. 20 resultados),
 * pensado para cuando haya muchas obras.
 *
 *  - multiple: value = [{id, nombre, estado}], onChange(array)
 *  - simple:   value = {id, nombre, estado} | null, onChange(obra|null)
 *  - enLinea:  la lista de resultados ocupa espacio (dentro de modales con
 *              scroll) en vez de flotar encima.
 */
export default function SelectorObras({
    multiple = false,
    value,
    onChange,
    placeholder = "Buscar obra…",
    error = false,
    enLinea = false,
    id,
}) {
    const [texto, setTexto] = useState("");
    const [abierto, setAbierto] = useState(false);
    const [resultados, setResultados] = useState([]);
    const [cargando, setCargando] = useState(false);
    const [activo, setActivo] = useState(0);

    const contenedor = useRef(null);
    const input = useRef(null);
    const peticion = useRef(0);

    const seleccionadas = multiple ? value || [] : value ? [value] : [];
    const idsSeleccionados = new Set(seleccionadas.map((o) => o.id));

    // Búsqueda con retardo; se ignoran respuestas de búsquedas anteriores.
    useEffect(() => {
        if (!abierto) return;
        const numero = ++peticion.current;
        setCargando(true);
        const t = setTimeout(async () => {
            try {
                const { data } = await api.get("/rrhh/obras", { params: { search: texto.trim() || undefined } });
                if (numero === peticion.current) {
                    setResultados(data.obras || []);
                    setActivo(0);
                }
            } catch {
                if (numero === peticion.current) setResultados([]);
            } finally {
                if (numero === peticion.current) setCargando(false);
            }
        }, 250);
        return () => clearTimeout(t);
    }, [texto, abierto]);

    // Cerrar al pulsar fuera.
    useEffect(() => {
        if (!abierto) return;
        const fuera = (e) => contenedor.current && !contenedor.current.contains(e.target) && setAbierto(false);
        document.addEventListener("mousedown", fuera);
        return () => document.removeEventListener("mousedown", fuera);
    }, [abierto]);

    const elegir = (obra) => {
        if (multiple) {
            onChange(idsSeleccionados.has(obra.id) ? seleccionadas.filter((o) => o.id !== obra.id) : [...seleccionadas, obra]);
            setTexto("");
            input.current?.focus();
        } else {
            onChange(obra);
            setTexto("");
            setAbierto(false);
        }
    };

    const quitar = (obra) => onChange(multiple ? seleccionadas.filter((o) => o.id !== obra.id) : null);

    const teclado = (e) => {
        if (e.key === "ArrowDown") {
            e.preventDefault();
            setAbierto(true);
            setActivo((a) => Math.min(a + 1, resultados.length - 1));
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            setActivo((a) => Math.max(a - 1, 0));
        } else if (e.key === "Enter") {
            e.preventDefault();
            if (abierto && resultados[activo]) elegir(resultados[activo]);
        } else if (e.key === "Escape") {
            if (abierto) {
                e.stopPropagation();
                setAbierto(false);
            }
        } else if (e.key === "Backspace" && !texto && multiple && seleccionadas.length) {
            quitar(seleccionadas[seleccionadas.length - 1]);
        }
    };

    const lista = abierto && (
        <div
            className={`${enLinea ? "mt-2" : "absolute left-0 right-0 top-full z-30 mt-1 shadow-xl"} overflow-hidden rounded-xl border border-slate-200 bg-white`}
        >
            <ul className="max-h-64 overflow-y-auto overscroll-contain py-1" role="listbox">
                {cargando && resultados.length === 0 ? (
                    <li className="flex items-center gap-2 px-3 py-3 text-sm text-slate-500">
                        <i className="mgc_loading_line animate-spin"></i> Buscando…
                    </li>
                ) : resultados.length === 0 ? (
                    <li className="px-3 py-3 text-sm text-slate-500">
                        {texto ? `No hay obras que coincidan con «${texto}».` : "No hay obras."}
                    </li>
                ) : (
                    resultados.map((o, i) => {
                        const marcada = idsSeleccionados.has(o.id);
                        const estado = ESTADOS_OBRA[o.estado];
                        return (
                            <li key={o.id} role="option" aria-selected={marcada}>
                                <button
                                    type="button"
                                    onMouseDown={(e) => e.preventDefault()}
                                    onClick={() => elegir(o)}
                                    onMouseEnter={() => setActivo(i)}
                                    className={`flex w-full items-center gap-3 px-3 py-2 text-left text-sm transition ${
                                        i === activo ? "bg-cyan-50" : ""
                                    }`}
                                >
                                    {multiple && (
                                        <span
                                            className={`flex h-4 w-4 shrink-0 items-center justify-center rounded border ${
                                                marcada ? "border-cyan-600 bg-cyan-600 text-white" : "border-slate-300 bg-white"
                                            }`}
                                        >
                                            {marcada && <i className="mgc_check_line text-[11px]"></i>}
                                        </span>
                                    )}
                                    <span className={`min-w-0 flex-1 break-words ${marcada ? "font-semibold text-slate-900" : "text-slate-700"}`}>
                                        {o.nombre}
                                    </span>
                                    {estado && (
                                        <span className={`shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold ${estado.clases}`}>
                                            {estado.texto}
                                        </span>
                                    )}
                                </button>
                            </li>
                        );
                    })
                )}
            </ul>
            {resultados.length === 20 && (
                <p className="border-t border-slate-100 bg-slate-50 px-3 py-1.5 text-[11px] text-slate-500">
                    Se muestran las 20 primeras: escribe para afinar la búsqueda.
                </p>
            )}
        </div>
    );

    // Selector simple con una obra elegida: se muestra como un "chip" grande.
    if (!multiple && value && !abierto) {
        return (
            <div
                className={`flex min-h-[42px] w-full items-center gap-2 rounded-xl border bg-white px-3 py-1.5 text-sm ${
                    error ? "border-red-400" : "border-slate-300"
                }`}
            >
                <i className="mgc_building_2_line text-slate-400"></i>
                <button
                    type="button"
                    onClick={() => {
                        setAbierto(true);
                        setTimeout(() => input.current?.focus(), 0);
                    }}
                    className="min-w-0 flex-1 truncate text-left text-slate-800"
                    title={value.nombre}
                >
                    {value.nombre}
                </button>
                <button type="button" onClick={() => quitar(value)} aria-label="Quitar obra" className="text-slate-400 hover:text-slate-700">
                    <i className="mgc_close_line"></i>
                </button>
            </div>
        );
    }

    return (
        <div ref={contenedor} className="relative">
            <div
                onClick={() => {
                    setAbierto(true);
                    input.current?.focus();
                }}
                className={`${inputBase} ${error ? inputError : ""} flex min-h-[42px] cursor-text flex-wrap items-center gap-1.5 border bg-white px-2.5 py-1.5`}
            >
                <i className="mgc_search_line text-slate-400"></i>
                {multiple &&
                    seleccionadas.map((o) => (
                        <span
                            key={o.id}
                            className="inline-flex max-w-full items-center gap-1 rounded-lg border border-cyan-200 bg-cyan-50 py-0.5 pl-2 pr-1 text-xs font-semibold text-cyan-800"
                        >
                            <span className="truncate">{o.nombre}</span>
                            <button
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    quitar(o);
                                }}
                                aria-label={`Quitar ${o.nombre}`}
                                className="rounded p-0.5 text-cyan-600 hover:bg-cyan-100"
                            >
                                <i className="mgc_close_line text-[11px]"></i>
                            </button>
                        </span>
                    ))}
                <input
                    ref={input}
                    id={id}
                    value={texto}
                    onChange={(e) => {
                        setTexto(e.target.value);
                        setAbierto(true);
                    }}
                    onFocus={() => setAbierto(true)}
                    onKeyDown={teclado}
                    placeholder={seleccionadas.length && multiple ? "Añadir otra obra…" : placeholder}
                    autoComplete="off"
                    className="min-w-[8rem] flex-1 border-0 bg-transparent p-1 text-sm focus:outline-none focus:ring-0"
                />
            </div>
            {lista}
        </div>
    );
}
