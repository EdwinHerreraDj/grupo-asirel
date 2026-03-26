import React from "react";

export default function ModalConfirmar({
    titulo = "Confirmar acción",
    mensaje = "¿Estás seguro?",
    textoConfirmar = "Eliminar",
    textoCancelar = "Cancelar",
    peligroso = true,
    onConfirmar,
    onCancelar,
}) {
    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">
                <h3
                    className={`text-lg font-semibold mb-4 flex items-center gap-2 ${
                        peligroso ? "text-red-600" : "text-gray-800"
                    }`}
                >
                    <span
                        className={`w-1.5 h-5 rounded ${
                            peligroso ? "bg-red-600" : "bg-primary"
                        }`}
                    ></span>
                    {titulo}
                </h3>

                <p className="text-sm text-gray-700 mb-6">{mensaje}</p>

                <div className="flex justify-end gap-2">
                    <button
                        onClick={onCancelar}
                        className="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition text-sm"
                    >
                        {textoCancelar}
                    </button>
                    <button
                        onClick={onConfirmar}
                        className={`px-4 py-2 text-white rounded-md shadow text-sm transition ${
                            peligroso
                                ? "bg-red-600 hover:bg-red-700"
                                : "bg-primary hover:bg-primary/90"
                        }`}
                    >
                        {textoConfirmar}
                    </button>
                </div>
            </div>
        </div>
    );
}
