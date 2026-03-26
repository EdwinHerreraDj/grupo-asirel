import React from "react";

export default function ModalAceptar({ onConfirmar, onCancelar, aceptando }) {
    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">
                <h3 className="text-lg font-semibold text-green-700 mb-4 flex items-center gap-2">
                    <span className="w-1.5 h-5 bg-green-600 rounded"></span>
                    Aceptar certificación
                </h3>

                <p className="text-sm text-gray-700 mb-2">
                    Al aceptar esta certificación:
                </p>
                <ul className="text-sm text-gray-600 space-y-1 mb-6 list-disc list-inside">
                    <li>No se podrán añadir ni eliminar líneas</li>
                    <li>No se podrán modificar los impuestos</li>
                    <li>Quedará disponible para facturación</li>
                </ul>

                <div className="flex justify-end gap-2">
                    <button
                        onClick={onCancelar}
                        disabled={aceptando}
                        className="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition text-sm disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={onConfirmar}
                        disabled={aceptando}
                        className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow text-sm disabled:opacity-50"
                    >
                        {aceptando ? "Aceptando..." : "Confirmar aceptación"}
                    </button>
                </div>
            </div>
        </div>
    );
}
