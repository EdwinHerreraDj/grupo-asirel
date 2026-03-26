import React, { useState } from "react";

export default function ModalNuevoCapitulo({
    cert,
    oficios,
    onGuardar,
    onCancelar,
}) {
    const [oficioId, setOficioId] = useState("");
    const [error, setError] = useState(null);
    const [guardando, setGuardando] = useState(false);

    const handleGuardar = async () => {
        if (!oficioId) {
            setError("Selecciona un oficio.");
            return;
        }
        setGuardando(true);
        try {
            await onGuardar({
                certificacion_id: cert.id,
                obra_gasto_categoria_id: oficioId,
            });
        } finally {
            setGuardando(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
            <div className="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.28)]">
                <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-cyan-50/50 px-6 py-5">
                    <div className="flex items-start gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-[0_10px_24px_rgba(37,99,235,0.25)]">
                            <i className="mgc_add_line text-lg"></i>
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-lg font-semibold tracking-tight text-slate-900">
                                Nuevo capítulo
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                Añade un nuevo capítulo a la certificación
                                seleccionada.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="px-6 py-5">
                    <div className="mb-5 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <p className="text-sm leading-6 text-slate-600">
                            Certificación{" "}
                            <span className="font-semibold text-slate-900">
                                {cert.numero_certificacion}
                            </span>{" "}
                            — Se heredarán los datos fiscales y el cliente.
                        </p>
                    </div>

                    <div className="mb-6">
                        <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Oficio <span className="text-red-500">*</span>
                        </label>

                        <select
                            value={oficioId}
                            onChange={(e) => {
                                setOficioId(e.target.value);
                                setError(null);
                            }}
                            className={`h-11 w-full rounded-xl border bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 ${
                                error
                                    ? "border-red-400 focus:border-red-400"
                                    : "border-slate-300 focus:border-cyan-500"
                            }`}
                        >
                            <option value="">— Selecciona oficio —</option>
                            {oficios.map((o) => (
                                <option key={o.id} value={o.id}>
                                    {o.nombre}
                                </option>
                            ))}
                        </select>

                        {error && (
                            <p className="mt-2 text-xs font-medium text-red-500">
                                {error}
                            </p>
                        )}
                    </div>

                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button
                            onClick={onCancelar}
                            disabled={guardando}
                            className="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                        >
                            Cancelar
                        </button>

                        <button
                            onClick={handleGuardar}
                            disabled={guardando}
                            className="inline-flex h-11 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500 disabled:opacity-50"
                        >
                            {guardando ? "Creando..." : "Crear capítulo"}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
