import React, { useState, useEffect, useMemo, useRef } from "react";
import api from "../../shared/api";
import useLockBodyScroll from "../../shared/useLockBodyScroll";

function ClienteSearchSelect({ clientes, value, onChange, disabled }) {
    const [open, setOpen] = useState(false);
    const [busqueda, setBusqueda] = useState("");
    const [highlight, setHighlight] = useState(0);
    const containerRef = useRef(null);
    const inputRef = useRef(null);
    const listRef = useRef(null);

    const seleccionado = useMemo(
        () => clientes.find((c) => String(c.id) === String(value)) || null,
        [clientes, value],
    );

    const filtrados = useMemo(() => {
        const t = busqueda.trim().toLowerCase();
        if (!t) return clientes;
        return clientes.filter((c) =>
            [c.nombre, c.cif, c.email]
                .filter(Boolean)
                .some((v) => v.toLowerCase().includes(t)),
        );
    }, [clientes, busqueda]);

    // Cerrar al click fuera
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

    // Focus al abrir
    useEffect(() => {
        if (open && inputRef.current) {
            inputRef.current.focus();
            setHighlight(0);
        }
    }, [open]);

    // Scroll del item activo a la vista
    useEffect(() => {
        if (!open || !listRef.current) return;
        const el = listRef.current.querySelector(
            `[data-idx="${highlight}"]`,
        );
        if (el) el.scrollIntoView({ block: "nearest" });
    }, [highlight, open, filtrados]);

    const seleccionar = (c) => {
        onChange(String(c.id));
        setOpen(false);
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
            setHighlight((h) => Math.min(h + 1, filtrados.length - 1));
            return;
        }
        if (e.key === "ArrowUp") {
            e.preventDefault();
            setHighlight((h) => Math.max(h - 1, 0));
            return;
        }
        if (e.key === "Enter") {
            e.preventDefault();
            if (filtrados[highlight]) seleccionar(filtrados[highlight]);
        }
    };

    return (
        <div ref={containerRef} className="relative">
            {/* Trigger */}
            <button
                type="button"
                onClick={() => !disabled && setOpen((o) => !o)}
                disabled={disabled}
                className={`w-full flex items-center justify-between gap-2 rounded-xl border text-left px-3 py-2.5 text-sm transition ${
                    open
                        ? "border-cyan-500 ring-2 ring-cyan-500/30 bg-white"
                        : "border-slate-300 bg-white hover:border-slate-400"
                } ${disabled ? "opacity-60 cursor-not-allowed" : ""}`}
            >
                <div className="flex items-center gap-2 min-w-0 flex-1">
                    <i className="mgc_user_3_line text-slate-400 shrink-0"></i>
                    {seleccionado ? (
                        <div className="min-w-0">
                            <span className="font-medium text-slate-900 truncate block">
                                {seleccionado.nombre}
                            </span>
                            {seleccionado.cif && (
                                <span className="text-[11px] text-slate-500 font-mono">
                                    {seleccionado.cif}
                                </span>
                            )}
                        </div>
                    ) : (
                        <span className="text-slate-400">
                            Selecciona un cliente…
                        </span>
                    )}
                </div>
                <i
                    className={`mgc_down_line text-slate-400 transition-transform ${
                        open ? "rotate-180" : ""
                    }`}
                ></i>
            </button>

            {/* Dropdown */}
            {open && (
                <div className="absolute left-0 right-0 mt-2 z-20 rounded-xl border border-slate-200 bg-white shadow-xl overflow-hidden">
                    {/* Search dentro del dropdown */}
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
                                placeholder="Buscar por nombre, CIF o email…"
                                className="w-full rounded-lg border-slate-300 pl-9 text-sm focus:border-cyan-500 focus:ring-cyan-500 bg-white"
                            />
                        </div>
                    </div>

                    {/* Lista */}
                    <div
                        ref={listRef}
                        className="max-h-64 overflow-y-auto"
                        role="listbox"
                    >
                        {filtrados.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-slate-500">
                                <i className="mgc_search_line text-2xl text-slate-300 block mb-2"></i>
                                Sin coincidencias
                            </div>
                        ) : (
                            filtrados.map((c, idx) => {
                                const isActive = idx === highlight;
                                const isSelected =
                                    String(c.id) === String(value);
                                return (
                                    <button
                                        key={c.id}
                                        type="button"
                                        data-idx={idx}
                                        onClick={() => seleccionar(c)}
                                        onMouseEnter={() => setHighlight(idx)}
                                        className={`w-full text-left px-4 py-2.5 flex items-center justify-between gap-3 transition ${
                                            isActive
                                                ? "bg-cyan-50"
                                                : "hover:bg-slate-50"
                                        }`}
                                    >
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-slate-900 truncate">
                                                {c.nombre}
                                            </p>
                                            <div className="flex flex-wrap gap-x-2 gap-y-0.5 text-[11px] text-slate-500">
                                                {c.cif && (
                                                    <span className="font-mono">
                                                        {c.cif}
                                                    </span>
                                                )}
                                                {c.email && (
                                                    <span className="truncate">
                                                        {c.email}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        {isSelected && (
                                            <i className="mgc_check_line text-cyan-600 shrink-0"></i>
                                        )}
                                    </button>
                                );
                            })
                        )}
                    </div>

                    {/* Footer con contador */}
                    <div className="px-3 py-1.5 border-t border-slate-100 bg-slate-50/60 text-[10px] text-slate-500 text-right">
                        {filtrados.length} de {clientes.length}{" "}
                        {clientes.length === 1 ? "cliente" : "clientes"}
                    </div>
                </div>
            )}
        </div>
    );
}

export default function ModalPdf({ obraId, onCancelar }) {
    useLockBodyScroll(true);

    const [clientes, setClientes] = useState([]);
    const [clienteId, setClienteId] = useState("");
    const [comentario, setComentario] = useState("");
    const [loading, setLoading] = useState(true);
    const [descargando, setDescargando] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        let cancelled = false;
        api.get("/clientes", { params: { per_page: 500 } })
            .then(({ data }) => {
                if (cancelled) return;
                const lista = data.data ?? data.clientes ?? [];
                setClientes(Array.isArray(lista) ? lista : []);
            })
            .catch((err) => {
                if (cancelled) return;
                console.error("Error cargando clientes:", err);
                setError("No se pudieron cargar los clientes.");
            })
            .finally(() => {
                if (!cancelled) setLoading(false);
            });
        return () => {
            cancelled = true;
        };
    }, []);

    const clienteSeleccionado = useMemo(
        () => clientes.find((c) => String(c.id) === String(clienteId)),
        [clientes, clienteId],
    );

    const handleDescargar = async () => {
        if (!clienteId) return;
        setDescargando(true);
        setError(null);
        try {
            const response = await api.get(
                `/obras/${obraId}/presupuesto-venta/pdf`,
                {
                    params: {
                        cliente_id: clienteId,
                        comentario: comentario.trim() || undefined,
                    },
                    responseType: "blob",
                },
            );

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", `Presupuesto_${obraId}.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            onCancelar();
        } catch (err) {
            console.error("Error al generar PDF", err);
            setError(
                err.response?.data?.message ||
                    "Ocurrió un error al generar el PDF. Inténtalo de nuevo.",
            );
        } finally {
            setDescargando(false);
        }
    };

    return (
        <>
            {/* Preloader a pantalla completa mientras genera */}
            {descargando && (
                <div className="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4">
                    <div className="flex flex-col items-center gap-4 rounded-3xl bg-white px-8 py-7 shadow-2xl max-w-sm w-full text-center">
                        <div className="relative h-14 w-14">
                            <div className="absolute inset-0 rounded-full border-4 border-slate-200"></div>
                            <div className="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-cyan-600"></div>
                        </div>
                        <div>
                            <p className="text-sm font-semibold text-slate-800">
                                Generando presupuesto…
                            </p>
                            <p className="mt-1 text-xs text-slate-500 leading-relaxed">
                                Esto puede tardar unos segundos mientras
                                preparamos el PDF. No cierres esta ventana.
                            </p>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal principal */}
            <div
                className="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
                role="dialog"
                aria-modal="true"
            >
                <div className="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col">
                    {/* Header */}
                    <div className="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5 shrink-0">
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                    <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                    PDF
                                </div>
                                <h3 className="mt-2 text-lg font-semibold text-slate-900">
                                    Generar presupuesto
                                </h3>
                                <p className="mt-0.5 text-sm text-slate-500">
                                    Selecciona el cliente al que va dirigido el
                                    presupuesto.
                                </p>
                            </div>
                            <button
                                onClick={onCancelar}
                                disabled={descargando}
                                aria-label="Cerrar"
                                className="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <i className="mgc_close_line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    {/* Body */}
                    <div className="overflow-y-auto overscroll-contain px-6 py-5 space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Cliente <span className="text-red-500">*</span>
                            </label>

                            {loading ? (
                                <div className="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                    <div className="h-4 w-4 border-2 border-slate-300 border-t-cyan-500 rounded-full animate-spin"></div>
                                    Cargando clientes…
                                </div>
                            ) : clientes.length === 0 ? (
                                <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                    <i className="mgc_warning_line mr-1"></i>
                                    No hay clientes registrados. Crea uno desde
                                    el apartado de clientes.
                                </div>
                            ) : (
                                <ClienteSearchSelect
                                    clientes={clientes}
                                    value={clienteId}
                                    onChange={setClienteId}
                                    disabled={descargando}
                                />
                            )}
                        </div>

                        {/* Resumen cliente seleccionado */}
                        {clienteSeleccionado && (
                            <div className="rounded-xl border border-cyan-200 bg-cyan-50/60 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-wide text-cyan-700">
                                    Cliente seleccionado
                                </p>
                                <p className="mt-0.5 text-sm font-semibold text-slate-900">
                                    {clienteSeleccionado.nombre}
                                </p>
                                <div className="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-600">
                                    {clienteSeleccionado.cif && (
                                        <span className="inline-flex items-center gap-1 font-mono">
                                            <i className="mgc_id_card_line text-slate-400"></i>
                                            {clienteSeleccionado.cif}
                                        </span>
                                    )}
                                    {clienteSeleccionado.email && (
                                        <span className="inline-flex items-center gap-1 truncate">
                                            <i className="mgc_mail_line text-slate-400"></i>
                                            {clienteSeleccionado.email}
                                        </span>
                                    )}
                                    {clienteSeleccionado.telefono && (
                                        <span className="inline-flex items-center gap-1">
                                            <i className="mgc_phone_line text-slate-400"></i>
                                            {clienteSeleccionado.telefono}
                                        </span>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Comentario opcional para el PDF */}
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Comentario{" "}
                                <span className="text-slate-400 font-normal">
                                    (opcional)
                                </span>
                            </label>
                            <textarea
                                rows={3}
                                value={comentario}
                                onChange={(e) => setComentario(e.target.value)}
                                maxLength={2000}
                                disabled={descargando}
                                placeholder="Este texto aparecerá en el PDF (condiciones, notas, validez de la oferta…)."
                                className="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30 resize-none disabled:opacity-60"
                            />
                        </div>

                        {/* Aviso de generación */}
                        <div className="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                            <i className="mgc_information_line text-slate-500 text-sm mt-0.5"></i>
                            <span>
                                El PDF se generará con los datos de la empresa
                                y colores configurados en{" "}
                                <strong>Configuración</strong>. Si hay muchas
                                partidas, la generación puede tardar unos
                                segundos.
                            </span>
                        </div>

                        {error && (
                            <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-start gap-2">
                                <i className="mgc_warning_line text-red-500 text-sm mt-0.5"></i>
                                <span>{error}</span>
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    <div className="border-t border-slate-200 bg-slate-50/40 px-6 py-4 shrink-0">
                        <div className="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                            <button
                                onClick={onCancelar}
                                disabled={descargando}
                                className="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:opacity-60"
                            >
                                Cancelar
                            </button>
                            <button
                                onClick={handleDescargar}
                                disabled={!clienteId || descargando || loading}
                                className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow hover:from-cyan-500 hover:to-blue-500 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <i className="mgc_download_2_line"></i>
                                Descargar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
