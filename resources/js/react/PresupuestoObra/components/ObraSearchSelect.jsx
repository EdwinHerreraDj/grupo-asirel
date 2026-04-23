import React, { useEffect, useMemo, useRef, useState } from "react";

/**
 * Combobox con buscador para seleccionar una obra.
 * Props:
 *  - obras: [{ id, nombre, estado? }]
 *  - value: id actualmente seleccionado (string o number)
 *  - onChange: (obra | null) => void
 *  - accent: 'cyan' | 'amber' | 'emerald' (tema de color)
 */
export default function ObraSearchSelect({
    obras = [],
    value = null,
    onChange,
    accent = "cyan",
}) {
    const [open, setOpen] = useState(false);
    const [busqueda, setBusqueda] = useState("");
    const [highlight, setHighlight] = useState(0);
    const containerRef = useRef(null);
    const inputRef = useRef(null);
    const listRef = useRef(null);

    const seleccionada = useMemo(
        () => obras.find((o) => String(o.id) === String(value)) || null,
        [obras, value],
    );

    const filtradas = useMemo(() => {
        const t = busqueda.trim().toLowerCase();
        if (!t) return obras;
        return obras.filter((o) =>
            [o.nombre, o.estado]
                .filter(Boolean)
                .some((v) => String(v).toLowerCase().includes(t)),
        );
    }, [obras, busqueda]);

    useEffect(() => {
        const handler = (e) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(e.target)
            ) {
                setOpen(false);
            }
        };
        document.addEventListener("mousedown", handler);
        return () => document.removeEventListener("mousedown", handler);
    }, []);

    useEffect(() => {
        if (open && inputRef.current) {
            inputRef.current.focus();
            setHighlight(0);
        }
    }, [open]);

    useEffect(() => {
        if (!open || !listRef.current) return;
        const el = listRef.current.querySelector(`[data-idx="${highlight}"]`);
        if (el) el.scrollIntoView({ block: "nearest" });
    }, [highlight, open, filtradas]);

    const seleccionar = (obra) => {
        onChange?.(obra);
        setOpen(false);
        setBusqueda("");
    };

    const limpiar = (e) => {
        e.stopPropagation();
        onChange?.(null);
        setBusqueda("");
    };

    const onKey = (e) => {
        if (e.key === "Escape") {
            e.preventDefault();
            setOpen(false);
            return;
        }
        if (e.key === "ArrowDown") {
            e.preventDefault();
            setHighlight((h) => Math.min(h + 1, filtradas.length - 1));
            return;
        }
        if (e.key === "ArrowUp") {
            e.preventDefault();
            setHighlight((h) => Math.max(h - 1, 0));
            return;
        }
        if (e.key === "Enter") {
            e.preventDefault();
            if (filtradas[highlight]) seleccionar(filtradas[highlight]);
        }
    };

    const theme = {
        cyan: {
            border: "border-cyan-200",
            bg: "bg-cyan-50/40",
            ring: "ring-cyan-500/30 border-cyan-500",
            icon: "text-cyan-600",
            label: "text-cyan-700",
            active: "bg-cyan-50",
            check: "text-cyan-600",
            focus: "focus:border-cyan-500 focus:ring-cyan-500",
        },
        amber: {
            border: "border-amber-200",
            bg: "bg-amber-50/40",
            ring: "ring-amber-500/30 border-amber-500",
            icon: "text-amber-600",
            label: "text-amber-700",
            active: "bg-amber-50",
            check: "text-amber-600",
            focus: "focus:border-amber-500 focus:ring-amber-500",
        },
        emerald: {
            border: "border-emerald-200",
            bg: "bg-emerald-50/40",
            ring: "ring-emerald-500/30 border-emerald-500",
            icon: "text-emerald-600",
            label: "text-emerald-700",
            active: "bg-emerald-50",
            check: "text-emerald-600",
            focus: "focus:border-emerald-500 focus:ring-emerald-500",
        },
    }[accent] ?? {};

    return (
        <div
            ref={containerRef}
            className={`rounded-2xl border ${theme.border} ${theme.bg} p-3 sm:p-4`}
        >
            <div className="flex items-center gap-2 mb-2">
                <span
                    className={`text-xs font-semibold uppercase tracking-wide ${theme.label}`}
                >
                    <i className="mgc_building_2_line mr-1"></i> Obra
                </span>
                {!seleccionada && (
                    <span className="text-[11px] text-slate-500">
                        Selecciona una para empezar
                    </span>
                )}
            </div>

            <div className="relative">
                {/* Trigger */}
                <button
                    type="button"
                    onClick={() => setOpen((o) => !o)}
                    className={`w-full flex items-center justify-between gap-2 rounded-xl border bg-white px-3 py-2.5 text-left text-sm transition ${
                        open
                            ? `${theme.ring} ring-2`
                            : `${theme.border} hover:border-slate-400`
                    }`}
                >
                    <div className="flex items-center gap-2 min-w-0 flex-1">
                        <i
                            className={`mgc_building_2_line shrink-0 ${theme.icon}`}
                        ></i>
                        {seleccionada ? (
                            <div className="min-w-0">
                                <div className="font-semibold text-slate-900 truncate">
                                    {seleccionada.nombre}
                                </div>
                                {seleccionada.estado && (
                                    <div className="text-[11px] text-slate-500">
                                        {seleccionada.estado}
                                    </div>
                                )}
                            </div>
                        ) : (
                            <span className="text-slate-400">
                                — Selecciona una obra —
                            </span>
                        )}
                    </div>
                    <div className="flex items-center gap-1 shrink-0">
                        {seleccionada && (
                            <span
                                role="button"
                                title="Quitar selección"
                                onClick={limpiar}
                                className="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                            >
                                <i className="mgc_close_line text-sm"></i>
                            </span>
                        )}
                        <i
                            className={`mgc_down_line text-slate-400 transition-transform ${
                                open ? "rotate-180" : ""
                            }`}
                        ></i>
                    </div>
                </button>

                {/* Dropdown */}
                {open && (
                    <div className="absolute left-0 right-0 mt-2 z-30 rounded-xl border border-slate-200 bg-white shadow-2xl overflow-hidden">
                        {/* Buscador */}
                        <div className="p-2 border-b border-slate-100 bg-slate-50/60">
                            <div className="relative">
                                <i className="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input
                                    ref={inputRef}
                                    type="text"
                                    value={busqueda}
                                    onChange={(e) => {
                                        setBusqueda(e.target.value);
                                        setHighlight(0);
                                    }}
                                    onKeyDown={onKey}
                                    placeholder="Buscar obra…"
                                    className={`w-full rounded-lg border-slate-300 pl-9 text-sm bg-white ${theme.focus}`}
                                />
                            </div>
                        </div>

                        {/* Lista */}
                        <div
                            ref={listRef}
                            className="max-h-72 overflow-y-auto overscroll-contain"
                            role="listbox"
                        >
                            {filtradas.length === 0 ? (
                                <div className="px-4 py-8 text-center text-sm text-slate-500">
                                    <i className="mgc_search_line text-2xl text-slate-300 block mb-2"></i>
                                    Sin coincidencias
                                </div>
                            ) : (
                                filtradas.map((obra, idx) => {
                                    const isActive = idx === highlight;
                                    const isSelected =
                                        String(obra.id) === String(value);
                                    return (
                                        <button
                                            key={obra.id}
                                            type="button"
                                            data-idx={idx}
                                            onClick={() => seleccionar(obra)}
                                            onMouseEnter={() =>
                                                setHighlight(idx)
                                            }
                                            className={`w-full text-left px-4 py-2.5 flex items-center justify-between gap-3 transition ${
                                                isActive
                                                    ? theme.active
                                                    : "hover:bg-slate-50"
                                            }`}
                                        >
                                            <div className="min-w-0">
                                                <p className="text-sm font-medium text-slate-900 truncate">
                                                    {obra.nombre}
                                                </p>
                                                {obra.estado && (
                                                    <p className="text-[11px] text-slate-500">
                                                        {obra.estado}
                                                    </p>
                                                )}
                                            </div>
                                            {isSelected && (
                                                <i
                                                    className={`mgc_check_line ${theme.check} shrink-0`}
                                                ></i>
                                            )}
                                        </button>
                                    );
                                })
                            )}
                        </div>

                        {/* Footer contador */}
                        <div className="px-3 py-1.5 border-t border-slate-100 bg-slate-50/60 text-[10px] text-slate-500 text-right">
                            {filtradas.length} de {obras.length}{" "}
                            {obras.length === 1 ? "obra" : "obras"}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
