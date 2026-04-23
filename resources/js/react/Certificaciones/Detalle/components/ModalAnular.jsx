import React, { useState } from "react";

export default function ModalAnular({ anulando, onConfirmar, onCancelar }) {
    const [motivo, setMotivo] = useState("");

    const handleConfirmar = () => {
        onConfirmar(motivo.trim() || null);
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div className="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl">
                <div className="mb-4 flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <i className="mgc_warning_line text-xl"></i>
                    </span>
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">
                            Anular aceptación
                        </h3>
                        <p className="text-sm text-slate-500">
                            La certificación volverá a estado pendiente.
                        </p>
                    </div>
                </div>

                <p className="mb-4 text-sm text-slate-600 leading-relaxed">
                    Al anular, podrás volver a editar líneas, cantidades e impuestos.
                    Las certificaciones facturadas no pueden anularse desde aquí.
                </p>

                <label className="mb-1 block text-sm font-medium text-slate-700">
                    Motivo (opcional)
                </label>
                <textarea
                    value={motivo}
                    onChange={(e) => setMotivo(e.target.value)}
                    rows={3}
                    maxLength={500}
                    placeholder="Ej: corregir cantidad errónea en línea 3"
                    className="mb-4 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-amber-400 focus:ring-4 focus:ring-amber-100"
                    disabled={anulando}
                />

                <div className="flex items-center justify-end gap-2">
                    <button
                        onClick={onCancelar}
                        disabled={anulando}
                        className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={handleConfirmar}
                        disabled={anulando}
                        className="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60"
                    >
                        {anulando ? "Anulando…" : "Anular aceptación"}
                    </button>
                </div>
            </div>
        </div>
    );
}
