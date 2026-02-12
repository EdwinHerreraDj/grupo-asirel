import React from "react";

export default function ExpiringFilesModal({ show, onClose, files, loading }) {
    if (!show) return null;

    const now = new Date();

    const getEstado = (fecha) => {
        const expiry = new Date(fecha);
        const diff = Math.ceil((expiry - now) / (1000 * 60 * 60 * 24));

        if (expiry < now) {
            return {
                text: "Vencido",
                class: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300",
                dias: Math.abs(diff),
                pasado: true,
            };
        }

        if (diff <= 14) {
            return {
                text: "Próximo",
                class: "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300",
                dias: diff,
                pasado: false,
            };
        }

        return {
            text: "En regla",
            class: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300",
            dias: diff,
            pasado: false,
        };
    };

    return (
        <div className="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center px-4 py-8">
            <div className="bg-white rounded-3xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col border border-slate-200">
                {/* Header */}
                <div className="px-8 py-6 border-b border-slate-200 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center shadow-inner">
                            <i className="mgc_calendar_line text-2xl text-indigo-600"></i>
                        </div>

                        <div>
                            <h2 className="text-xl font-bold text-slate-800">
                                Archivos con caducidad
                            </h2>
                            <p className="text-sm text-slate-500">
                                Control rápido de vencimientos
                            </p>
                        </div>
                    </div>

                    <button
                        onClick={onClose}
                        className="h-10 w-10 rounded-2xl border border-slate-200 hover:bg-slate-100 flex items-center justify-center transition"
                    >
                        <i className="mgc_close_line text-xl text-slate-600"></i>
                    </button>
                </div>

                {/* Body */}
                <div className="flex-1 overflow-y-auto">
                    {loading && (
                        <div className="py-20 text-center text-slate-500 font-medium">
                            Cargando...
                        </div>
                    )}

                    {!loading && files.length === 0 && (
                        <div className="py-20 text-center text-slate-500">
                            No hay archivos para este rango
                        </div>
                    )}

                    {!loading && files.length > 0 && (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="sticky top-0 bg-slate-50 border-b border-slate-200">
                                    <tr className="text-left text-xs uppercase tracking-wider text-slate-500">
                                        <th className="px-8 py-4 font-semibold">
                                            Documento
                                        </th>
                                        <th className="px-8 py-4 font-semibold">
                                            Carpeta
                                        </th>
                                        <th className="px-8 py-4 font-semibold">
                                            Caducidad
                                        </th>
                                        <th className="px-8 py-4 font-semibold">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-200">
                                    {files.map((file) => {
                                        const estado = getEstado(
                                            file.fecha_caducidad,
                                        );

                                        return (
                                            <tr
                                                key={file.id}
                                                className="hover:bg-slate-50 transition"
                                            >
                                                <td className="px-8 py-5 font-semibold text-slate-800">
                                                    {file.nombre}
                                                </td>

                                                <td className="px-8 py-5 text-xs text-slate-500">
                                                    {file.folder?.nombre ||
                                                        "Raíz"}
                                                </td>

                                                <td className="px-8 py-5">
                                                    <div className="text-slate-700 font-medium">
                                                        {new Date(
                                                            file.fecha_caducidad,
                                                        ).toLocaleDateString()}
                                                    </div>
                                                    <div className="text-xs text-slate-500 mt-1">
                                                        {estado.pasado
                                                            ? `Hace ${estado.dias} días`
                                                            : `En ${estado.dias} días`}
                                                    </div>
                                                </td>

                                                <td className="px-8 py-5">
                                                    <span
                                                        className={`inline-flex px-4 py-1.5 rounded-full text-xs font-semibold ${estado.class}`}
                                                    >
                                                        {estado.text}
                                                    </span>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div className="px-8 py-5 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-slate-500">
                    <span>{files.length} resultado(s)</span>

                    <button
                        onClick={onClose}
                        className="
                    px-6 py-2.5
                    rounded-2xl
                    bg-gradient-to-r from-slate-900 to-slate-700
                    text-white
                    font-semibold
                    hover:shadow-lg
                    hover:scale-[1.02]
                    active:scale-[0.98]
                    transition-all
                "
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    );
}
