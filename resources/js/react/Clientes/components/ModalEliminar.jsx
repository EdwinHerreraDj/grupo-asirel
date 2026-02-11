// resources/js/react/Clientes/components/ModalEliminar.jsx
import React from "react";

export default function ModalEliminar({ cliente, onConfirmar, onCancelar }) {
    return (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
            <div className="bg-white dark:bg-gray-800 w-full max-w-md rounded-xl shadow-2xl p-6 border border-gray-200 dark:border-gray-700">
                {/* Icono de advertencia */}
                <div className="flex justify-center mb-4">
                    <div className="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i className="mgc_alert_line text-3xl text-red-600 dark:text-red-400"></i>
                    </div>
                </div>

                {/* Título */}
                <h3 className="text-xl font-bold text-gray-800 dark:text-white text-center mb-2">
                    Eliminar Cliente
                </h3>

                {/* Mensaje */}
                <p className="text-gray-600 dark:text-gray-400 text-center mb-6">
                    ¿Estás seguro de que deseas eliminar al cliente{" "}
                    <strong className="text-red-600 dark:text-red-400">
                        {cliente?.nombre}
                    </strong>
                    ?
                    <br />
                    <span className="text-sm">
                        Esta acción no se puede deshacer.
                    </span>
                </p>

                {/* Botones */}
                <div className="flex gap-3">
                    <button
                        onClick={onCancelar}
                        className="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={onConfirmar}
                        className="flex-1 px-4 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    );
}
