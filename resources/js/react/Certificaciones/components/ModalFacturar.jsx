import React, { useState, useEffect } from "react";
import api from "../../shared/api";
import { formatEuro } from "../utils/formato";

export default function ModalFacturar({
    obraId,
    numeroCertificacion,
    onFacturado,
    onCancelar,
}) {
    const [resumen, setResumen] = useState(null);
    const [series, setSeries] = useState([]);
    const [serieSeleccionada, setSerie] = useState("");
    const [loading, setLoading] = useState(true);
    const [emitiendo, setEmitiendo] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        api.get(`/obras/${obraId}/certificaciones/facturables`)
            .then(({ data }) => {
                const grupo = data.grupos.find(
                    (g) => g.numero_certificacion === numeroCertificacion,
                );
                setResumen(grupo ?? null);
                setSeries(data.series ?? []);
            })
            .finally(() => setLoading(false));
    }, []);

    const handleEmitir = async () => {
        if (!serieSeleccionada) {
            setError("Selecciona una serie.");
            return;
        }
        setEmitiendo(true);
        setError(null);
        try {
            const { data } = await api.post(
                `/obras/${obraId}/certificaciones/facturar`,
                {
                    numero_certificacion: numeroCertificacion,
                    serie: serieSeleccionada,
                },
            );
            onFacturado(data.redirect_url);
        } catch (err) {
            setError(
                err.response?.data?.message ?? "Error al emitir la factura.",
            );
        } finally {
            setEmitiendo(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">
                <h3 className="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                    <span className="w-1.5 h-5 bg-primary rounded"></span>
                    Emitir factura
                </h3>

                {loading ? (
                    <p className="text-sm text-gray-400 py-4 text-center">
                        Cargando...
                    </p>
                ) : !resumen ? (
                    <p className="text-sm text-red-600">
                        No se encontró la certificación.
                    </p>
                ) : (
                    <>
                        {/* RESUMEN */}
                        <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-5 space-y-1.5">
                            <p className="text-sm">
                                <span className="font-medium">Cliente:</span>{" "}
                                {resumen.cliente_nombre}
                            </p>
                            <p className="text-sm">
                                <span className="font-medium">
                                    Nº Certificación:
                                </span>{" "}
                                {numeroCertificacion}
                            </p>
                            <p className="text-sm">
                                <span className="font-medium">Capítulos:</span>{" "}
                                {resumen.total_capitulos}
                            </p>
                            <p className="text-sm">
                                <span className="font-medium">Base:</span>{" "}
                                {formatEuro(resumen.base)}
                            </p>
                            <p className="text-sm font-semibold border-t border-gray-200 pt-1.5 mt-1.5">
                                Total: {formatEuro(resumen.total)}
                            </p>
                        </div>

                        {/* SERIE */}
                        <div className="mb-5">
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Serie de facturación{" "}
                                <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={serieSeleccionada}
                                onChange={(e) => {
                                    setSerie(e.target.value);
                                    setError(null);
                                }}
                                className={`w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 ${error ? "border-red-400" : "border-gray-300"}`}
                            >
                                <option value="">— Selecciona serie —</option>
                                {series.map((s) => (
                                    <option key={s.id} value={s.serie}>
                                        {s.serie}
                                    </option>
                                ))}
                            </select>
                            {error && (
                                <p className="text-red-500 text-xs mt-1">
                                    {error}
                                </p>
                            )}
                        </div>
                    </>
                )}

                <div className="flex justify-end gap-2">
                    <button
                        onClick={onCancelar}
                        disabled={emitiendo}
                        className="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition text-sm disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    {resumen && (
                        <button
                            onClick={handleEmitir}
                            disabled={emitiendo || !serieSeleccionada}
                            className="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 shadow text-sm disabled:opacity-50"
                        >
                            {emitiendo ? "Emitiendo..." : "Emitir factura"}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}
