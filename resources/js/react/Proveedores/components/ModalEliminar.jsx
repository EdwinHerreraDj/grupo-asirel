// resources/js/react/Proveedores/components/ModalEliminar.jsx
import React from "react";

export default function ModalEliminar({ proveedor, onConfirmar, onCancelar }) {
    return (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
            <div className="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 sm:p-8 animate-in fade-in zoom-in-95 duration-200">
                {/* Icono */}
                <div className="flex justify-center mb-6">
                    <div className="w-20 h-20 rounded-full bg-gradient-to-br from-rose-100 to-red-200 flex items-center justify-center shadow-inner">
                        <i className="mgc_alert_line text-4xl text-rose-600"></i>
                    </div>
                </div>

                {/* Título */}
                <h3 className="text-2xl font-bold text-slate-800 text-center mb-3">
                    Eliminar Proveedor
                </h3>

                {/* Mensaje */}
                <p className="text-slate-600 text-center leading-relaxed mb-8">
                    ¿Estás seguro de que deseas eliminar al proveedor{" "}
                    <strong className="text-rose-600 font-semibold">
                        {proveedor?.nombre}
                    </strong>
                    ?
                    <br />
                    <span className="text-sm text-slate-500">
                        Esta acción no se puede deshacer.
                    </span>
                </p>

                {/* Botones */}
                <div className="flex flex-col sm:flex-row gap-3">
                    <button
                        onClick={onCancelar}
                        className="w-full px-5 py-3 rounded-2xl bg-white border border-slate-300 text-slate-600 font-medium hover:bg-slate-100 transition-all shadow-sm"
                    >
                        Cancelar
                    </button>

                    <button
                        onClick={onConfirmar}
                        className="w-full px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 text-white font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    );
}
