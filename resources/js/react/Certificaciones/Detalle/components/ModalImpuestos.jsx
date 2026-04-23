import React, { useState } from "react";
import { redondear } from "../../utils/formato";

export default function ModalImpuestos({
    certificacion,
    onGuardar,
    onCancelar,
    guardando,
}) {
    const [form, setForm] = useState({
        iva_porcentaje: redondear(certificacion.iva_porcentaje),
        retencion_porcentaje: redondear(certificacion.retencion_porcentaje),
    });

    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm border border-gray-200">
                <h3 className="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                    <span className="w-1.5 h-5 bg-primary rounded"></span>
                    Modificar impuestos
                </h3>

                <p className="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-4">
                    Se actualizarán todos los capítulos de la certificación{" "}
                    {certificacion.numero_certificacion}.
                </p>

                <div className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            IVA %
                        </label>
                        <input
                            type="number"
                            value={form.iva_porcentaje}
                            onChange={(e) =>
                                setForm((p) => ({
                                    ...p,
                                    iva_porcentaje: e.target.value,
                                }))
                            }
                            min="0"
                            step="0.01"
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Retención %
                        </label>
                        <input
                            type="number"
                            value={form.retencion_porcentaje}
                            onChange={(e) =>
                                setForm((p) => ({
                                    ...p,
                                    retencion_porcentaje: e.target.value,
                                }))
                            }
                            min="0"
                            step="0.01"
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </div>
                </div>

                <div className="flex justify-end gap-2 mt-6">
                    <button
                        onClick={onCancelar}
                        disabled={guardando}
                        className="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition text-sm disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={() =>
                            onGuardar({
                                iva_porcentaje:
                                    parseFloat(form.iva_porcentaje) || 0,
                                retencion_porcentaje:
                                    parseFloat(form.retencion_porcentaje) || 0,
                            })
                        }
                        disabled={guardando}
                        className="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 shadow text-sm disabled:opacity-50"
                    >
                        {guardando ? "Guardando..." : "Guardar"}
                    </button>
                </div>
            </div>
        </div>
    );
}
