import React from "react";
import useLockBodyScroll from "../../shared/useLockBodyScroll";

export default function ModalEliminar({ tarea, onConfirmar, onCancelar }) {
    useLockBodyScroll(true);

    return (
        <div
            className="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4"
            role="dialog"
            aria-modal="true"
        >
            <div className="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div className="px-6 pt-6 text-center">
                    <div className="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i className="mgc_warning_line text-3xl"></i>
                    </div>
                    <h3 className="text-lg font-semibold text-slate-900">
                        Eliminar tarea
                    </h3>
                    <p className="mt-2 text-sm text-slate-600">
                        ¿Eliminar{" "}
                        <strong className="text-slate-900">
                            “{tarea?.titulo}”
                        </strong>
                        ? Esta acción no se puede deshacer.
                    </p>
                </div>
                <div className="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <button
                        onClick={onCancelar}
                        className="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={onConfirmar}
                        className="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    );
}
