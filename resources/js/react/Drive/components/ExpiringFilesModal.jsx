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
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-8">
            <div className="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col border border-gray-200 dark:border-gray-800">
                {/* Header */}
                <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <div className="h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                            <i className="mgc_calendar_line text-xl"></i>
                        </div>

                        <div>
                            <h2 className="text-lg font-semibold">
                                Archivos con caducidad
                            </h2>
                            <p className="text-xs text-gray-500">
                                Control rápido de vencimientos
                            </p>
                        </div>
                    </div>

                    <button
                        onClick={onClose}
                        className="h-9 w-9 rounded-xl border hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center"
                    >
                        <i className="mgc_close_line text-xl"></i>
                    </button>
                </div>

                {/* Body */}
                <div className="flex-1 overflow-y-auto">
                    {loading && (
                        <div className="py-16 text-center">Cargando...</div>
                    )}

                    {!loading && files.length === 0 && (
                        <div className="py-16 text-center text-gray-500">
                            No hay archivos para este rango
                        </div>
                    )}

                    {!loading && files.length > 0 && (
                        <table className="w-full text-sm">
                            <thead className="sticky top-0 bg-gray-50 dark:bg-gray-800 border-b">
                                <tr className="text-left text-xs uppercase text-gray-500">
                                    <th className="px-6 py-3">Documento</th>
                                    <th className="px-6 py-3">Carpeta</th>
                                    <th className="px-6 py-3">Caducidad</th>
                                    <th className="px-6 py-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {files.map((file) => {
                                    const estado = getEstado(
                                        file.fecha_caducidad,
                                    );

                                    return (
                                        <tr
                                            key={file.id}
                                            className="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition"
                                        >
                                            <td className="px-6 py-4 font-medium">
                                                {file.nombre}
                                            </td>

                                            <td className="px-6 py-4 text-xs text-gray-500">
                                                {file.folder?.nombre || "Raíz"}
                                            </td>

                                            <td className="px-6 py-4">
                                                <div>
                                                    {new Date(
                                                        file.fecha_caducidad,
                                                    ).toLocaleDateString()}
                                                    <div className="text-xs text-gray-500">
                                                        {estado.pasado
                                                            ? `Hace ${estado.dias} días`
                                                            : `En ${estado.dias} días`}
                                                    </div>
                                                </div>
                                            </td>

                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-flex px-3 py-1 rounded-full text-xs font-bold ${estado.class}`}
                                                >
                                                    {estado.text}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    )}
                </div>

                {/* Footer */}
                <div className="px-6 py-4 border-t flex justify-between text-xs text-gray-500">
                    <span>{files.length} resultado(s)</span>
                    <button
                        onClick={onClose}
                        className="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    );
}
