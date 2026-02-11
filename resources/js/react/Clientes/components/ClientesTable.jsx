// resources/js/react/Clientes/components/ClientesTable.jsx
import React from "react";
import ClienteRow from "./ClienteRow";
import Pagination from "../../shared/Pagination";

export default function ClientesTable({
    clientes = [],
    loading,
    onEditar,
    onEliminar,
    currentPage,
    lastPage,
    total,
    onPageChange,
}) {
    return (
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            {/* Tabla */}
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead className="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th className="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                Nombre
                            </th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                CIF
                            </th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                Teléfonos
                            </th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                Emails
                            </th>
                            <th className="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300 hidden lg:table-cell">
                                Dirección
                            </th>
                            <th className="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">
                                Activo
                            </th>
                            <th className="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">
                                Acción
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                        {loading ? (
                            <tr>
                                <td
                                    colSpan="7"
                                    className="px-4 py-12 text-center"
                                >
                                    <div className="flex justify-center">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                                    </div>
                                    <p className="text-gray-500 dark:text-gray-400 mt-2">
                                        Cargando clientes...
                                    </p>
                                </td>
                            </tr>
                        ) : clientes.length === 0 ? (
                            <tr>
                                <td
                                    colSpan="7"
                                    className="px-4 py-12 text-center text-gray-500 dark:text-gray-400"
                                >
                                    <i className="mgc_user_3_line text-5xl mb-2 block text-gray-300 dark:text-gray-600"></i>
                                    No se encontraron clientes.
                                </td>
                            </tr>
                        ) : (
                            clientes.map((cliente) => (
                                <ClienteRow
                                    key={cliente.id}
                                    cliente={cliente}
                                    onEditar={onEditar}
                                    onEliminar={onEliminar}
                                />
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {/* Paginación */}
            {!loading && clientes.length > 0 && (
                <div className="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <Pagination
                        currentPage={currentPage}
                        lastPage={lastPage}
                        total={total}
                        onPageChange={onPageChange}
                    />
                </div>
            )}
        </div>
    );
}
