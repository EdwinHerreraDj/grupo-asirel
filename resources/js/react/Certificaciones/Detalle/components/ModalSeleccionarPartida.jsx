import React, { useState } from "react";
import { formatEuro } from "../../utils/formato";

export default function ModalSeleccionarPartida({
    partidasVenta,
    onGuardar,
    onCancelar,
    guardando,
}) {
    const [partidaSeleccionada, setPartidaSeleccionada] = useState(null);
    const [cantidad, setCantidad] = useState("");
    const [error, setError] = useState(null);

    const handleSeleccionar = (partida) => {
        setPartidaSeleccionada(partida);
        setCantidad("");
        setError(null);
    };

    const importePreview = partidaSeleccionada
        ? Math.round(
              (parseFloat(cantidad) || 0) *
                  partidaSeleccionada.precio_unitario *
                  100,
          ) / 100
        : 0;

    const excede = partidaSeleccionada
        ? (parseFloat(cantidad) || 0) > partidaSeleccionada.pendiente
        : false;

    const handleGuardar = async () => {
        if (!partidaSeleccionada) {
            setError("Selecciona una partida.");
            return;
        }
        if (!cantidad || parseFloat(cantidad) <= 0) {
            setError("Introduce una cantidad válida.");
            return;
        }
        setError(null);
        await onGuardar({
            presupuesto_venta_partida_id: partidaSeleccionada.id,
            cantidad: parseFloat(cantidad),
        });
    };

    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-2xl border border-gray-200 max-h-[90vh] overflow-y-auto">
                <h3 className="text-lg font-semibold text-primary mb-5 flex items-center gap-2">
                    <span className="w-1.5 h-5 bg-primary rounded"></span>
                    Seleccionar partida a certificar
                </h3>

                {/* LISTA DE PARTIDAS */}
                <div className="space-y-2 mb-5 max-h-72 overflow-y-auto pr-1">
                    {partidasVenta.length === 0 ? (
                        <p className="text-sm text-gray-400 italic text-center py-4">
                            No hay partidas de presupuesto de venta para este
                            capítulo.
                        </p>
                    ) : (
                        partidasVenta.map((partida) => {
                            const seleccionada =
                                partidaSeleccionada?.id === partida.id;
                            const agotada = partida.pendiente <= 0;

                            return (
                                <div
                                    key={partida.id}
                                    onClick={() =>
                                        !agotada && handleSeleccionar(partida)
                                    }
                                    className={`border rounded-xl p-3 transition cursor-pointer ${
                                        agotada
                                            ? "opacity-50 cursor-not-allowed bg-gray-50 border-gray-200"
                                            : seleccionada
                                              ? "border-primary bg-blue-50"
                                              : "border-gray-200 hover:border-primary/50 hover:bg-gray-50"
                                    }`}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2">
                                                {partida.codigo && (
                                                    <span className="text-xs text-gray-400 font-mono">
                                                        {partida.codigo}
                                                    </span>
                                                )}
                                                <span className="text-sm font-medium text-gray-800 truncate">
                                                    {partida.descripcion}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                                <span>
                                                    Ud: {partida.unidad || "—"}
                                                </span>
                                                <span>
                                                    P.u:{" "}
                                                    {formatEuro(
                                                        partida.precio_unitario,
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="text-right text-xs shrink-0">
                                            <div className="text-gray-500">
                                                Contratado:{" "}
                                                <span className="font-medium text-gray-700">
                                                    {partida.medicion}
                                                </span>
                                            </div>
                                            <div className="text-gray-500">
                                                Certificado:{" "}
                                                <span className="font-medium text-orange-600">
                                                    {
                                                        partida.certificado_acumulado
                                                    }
                                                </span>
                                            </div>
                                            <div
                                                className={`font-semibold ${agotada ? "text-red-500" : "text-emerald-600"}`}
                                            >
                                                Pendiente: {partida.pendiente}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>

                {/* FORMULARIO DE CANTIDAD */}
                {partidaSeleccionada && (
                    <div className="border border-gray-200 rounded-xl p-4 bg-gray-50 space-y-3">
                        <p className="text-sm font-medium text-gray-700">
                            {partidaSeleccionada.descripcion}
                        </p>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    Cantidad a certificar{" "}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    step="0.0001"
                                    value={cantidad}
                                    onChange={(e) => {
                                        setCantidad(e.target.value);
                                        setError(null);
                                    }}
                                    placeholder="0.0000"
                                    autoFocus
                                    className={`w-full border rounded-lg px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary/30 ${
                                        excede
                                            ? "border-amber-400 bg-amber-50"
                                            : "border-gray-300"
                                    }`}
                                />
                                {excede && (
                                    <p className="text-amber-600 text-xs mt-1">
                                        ⚠️ Supera el pendiente (
                                        {partidaSeleccionada.pendiente})
                                    </p>
                                )}
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">
                                    Importe estimado
                                </label>
                                <div className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-right bg-white font-semibold text-gray-800">
                                    {formatEuro(importePreview)}
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {error && <p className="text-red-500 text-sm mt-3">{error}</p>}

                <div className="flex justify-end gap-2 mt-5">
                    <button
                        onClick={onCancelar}
                        disabled={guardando}
                        className="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition text-sm disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={handleGuardar}
                        disabled={
                            guardando || !partidaSeleccionada || !cantidad
                        }
                        className="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 shadow text-sm disabled:opacity-50"
                    >
                        {guardando ? "Guardando..." : "Añadir línea"}
                    </button>
                </div>
            </div>
        </div>
    );
}
