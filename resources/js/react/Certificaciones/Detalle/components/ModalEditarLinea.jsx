import React, { useMemo, useState } from "react";
import { formatEuro, formatNumero, redondear } from "../../utils/formato";

/**
 * Edici\u00f3n de una l\u00ednea de certificaci\u00f3n existente.
 * Concepto y unidad vienen de la partida vinculada: se muestran read-only.
 * Cantidad y precio unitario son editables.
 * Bloqueo suave: si la cantidad excede el pendiente (excluyendo esta l\u00ednea),
 * se abre confirmaci\u00f3n expl\u00edcita antes de enviar con `forzar=true`.
 */
export default function ModalEditarLinea({
    linea,
    partidasVenta = [],
    onGuardar,
    onCancelar,
    guardando,
}) {
    const partidaVinculada = useMemo(
        () =>
            partidasVenta.find(
                (p) => p.id === linea.presupuesto_venta_partida_id,
            ) ?? null,
        [partidasVenta, linea],
    );

    // Pendiente disponible para editar = pendiente + lo que ya consume esta l\u00ednea
    const pendienteDisponible = partidaVinculada
        ? partidaVinculada.pendiente + parseFloat(linea.cantidad || 0)
        : null;

    const [form, setForm] = useState({
        cantidad: redondear(linea.cantidad, 4),
        precio_unitario: redondear(linea.precio_unitario, 4),
        comentario: linea.comentario ?? "",
    });
    const [error, setError] = useState(null);
    const [confirmandoExceso, setConfirmandoExceso] = useState(false);

    const cantidadNum = parseFloat(form.cantidad) || 0;
    const precioNum = parseFloat(form.precio_unitario) || 0;

    const excede =
        pendienteDisponible !== null && cantidadNum > pendienteDisponible;

    const importePreview = Math.round(cantidadNum * precioNum * 100) / 100;

    const enviar = async (forzar = false) => {
        await onGuardar(
            linea.id,
            {
                concepto: linea.concepto,
                unidad: linea.unidad,
                cantidad: cantidadNum,
                precio_unitario: precioNum,
                comentario: form.comentario.trim() || null,
            },
            { forzar },
        );
    };

    const handleGuardar = async () => {
        if (cantidadNum <= 0) {
            setError("La cantidad debe ser mayor que 0.");
            return;
        }
        if (precioNum < 0) {
            setError("El precio no puede ser negativo.");
            return;
        }
        setError(null);

        if (excede) {
            setConfirmandoExceso(true);
            return;
        }

        await enviar(false);
    };

    const handleConfirmarExceso = async () => {
        setConfirmandoExceso(false);
        await enviar(true);
    };

    return (
        <>
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div className="w-full max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-2xl">
                    <h3 className="mb-5 flex items-center gap-2 text-lg font-semibold text-primary">
                        <span className="h-5 w-1.5 rounded bg-primary"></span>
                        Editar línea
                    </h3>

                    <div className="mb-4 space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-sm">
                        <div className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Concepto
                        </div>
                        <p className="font-medium text-slate-800">
                            {linea.concepto}
                        </p>
                        <div className="flex items-center gap-4 text-xs text-slate-500">
                            <span>Ud: {linea.unidad || "—"}</span>
                            {partidaVinculada && (
                                <span>
                                    Pendiente disponible:{" "}
                                    <strong className="text-slate-700">
                                        {formatNumero(pendienteDisponible)}{" "}
                                        {linea.unidad}
                                    </strong>
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="mb-1 block text-xs font-medium text-slate-600">
                                Cantidad <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.0001"
                                value={form.cantidad}
                                onChange={(e) => {
                                    setForm((p) => ({
                                        ...p,
                                        cantidad: e.target.value,
                                    }));
                                    setError(null);
                                }}
                                autoFocus
                                className={`w-full rounded-lg border px-3 py-2 text-right text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 ${
                                    excede
                                        ? "border-amber-400 bg-amber-50"
                                        : "border-gray-300"
                                }`}
                            />
                            {excede && (
                                <p className="mt-1 text-xs text-amber-600">
                                    ⚠️ Supera el pendiente (
                                    {formatNumero(pendienteDisponible)}). Al
                                    guardar se pedirá confirmación.
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="mb-1 block text-xs font-medium text-slate-600">
                                Precio unitario
                            </label>
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                value={form.precio_unitario}
                                onChange={(e) => {
                                    setForm((p) => ({
                                        ...p,
                                        precio_unitario: e.target.value,
                                    }));
                                    setError(null);
                                }}
                                className="w-full rounded-lg border border-gray-300 px-3 py-2 text-right text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </div>
                    </div>

                    <div className="mt-4 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <span className="text-slate-600">Importe resultante</span>
                        <span className="text-base font-semibold text-slate-900">
                            {formatEuro(importePreview)}
                        </span>
                    </div>

                    <div className="mt-4">
                        <label className="mb-1 block text-xs font-medium text-slate-600">
                            Comentario{" "}
                            <span className="text-slate-400">(opcional)</span>
                        </label>
                        <textarea
                            rows={2}
                            value={form.comentario}
                            onChange={(e) =>
                                setForm((p) => ({
                                    ...p,
                                    comentario: e.target.value,
                                }))
                            }
                            maxLength={1000}
                            placeholder="Nota u observación para esta línea…"
                            className="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </div>

                    {error && (
                        <p className="mt-3 text-sm text-red-500">{error}</p>
                    )}

                    <div className="mt-5 flex justify-end gap-2">
                        <button
                            onClick={onCancelar}
                            disabled={guardando}
                            className="rounded-lg bg-gray-200 px-4 py-2 text-sm transition hover:bg-gray-300 disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            onClick={handleGuardar}
                            disabled={guardando}
                            className="rounded-lg bg-primary px-4 py-2 text-sm text-white shadow transition hover:bg-primary/90 disabled:opacity-50"
                        >
                            {guardando ? "Guardando…" : "Guardar cambios"}
                        </button>
                    </div>
                </div>
            </div>

            {confirmandoExceso && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm">
                    <div className="w-full max-w-md rounded-2xl border border-amber-200 bg-white p-6 shadow-2xl">
                        <div className="mb-4 flex items-center gap-3">
                            <span className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                <i className="mgc_warning_line text-xl"></i>
                            </span>
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Supera lo contratado
                                </h3>
                                <p className="text-sm text-slate-500">
                                    Vas a certificar más de lo pendiente.
                                </p>
                            </div>
                        </div>

                        <div className="mb-4 space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                            <div className="flex justify-between">
                                <span>Pendiente disponible:</span>
                                <span className="font-semibold">
                                    {formatNumero(pendienteDisponible)}{" "}
                                    {linea.unidad}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span>Nueva cantidad:</span>
                                <span className="font-semibold">
                                    {formatNumero(cantidadNum)} {linea.unidad}
                                </span>
                            </div>
                        </div>

                        <p className="mb-4 text-sm leading-relaxed text-slate-600">
                            Si hay ampliación real de medición, primero aumenta
                            la partida de venta. Continúa solo si estás seguro.
                        </p>

                        <div className="flex items-center justify-end gap-2">
                            <button
                                onClick={() => setConfirmandoExceso(false)}
                                disabled={guardando}
                                className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                            >
                                Cancelar
                            </button>
                            <button
                                onClick={handleConfirmarExceso}
                                disabled={guardando}
                                className="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60"
                            >
                                {guardando
                                    ? "Guardando…"
                                    : "Sí, guardar igualmente"}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
