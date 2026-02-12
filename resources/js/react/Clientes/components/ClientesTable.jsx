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
        <div className="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            {/* Tabla */}
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm text-slate-700">
                    <thead className="bg-gradient-to-r from-slate-50 to-slate-100 border-b border-slate-200">
                        <tr>
                            <th className="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-600">
                                Nombre
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-600">
                                CIF
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-600">
                                Teléfonos
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-600">
                                Emails
                            </th>
                            <th className="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-600 hidden lg:table-cell">
                                Dirección
                            </th>
                            <th className="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-600">
                                Activo
                            </th>
                            <th className="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-600">
                                Acción
                            </th>
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-200 bg-white">
                        {loading ? (
                            <tr>
                                <td
                                    colSpan="7"
                                    className="px-6 py-16 text-center"
                                >
                                    <div className="flex flex-col items-center justify-center gap-3">
                                        <div className="animate-spin rounded-full h-10 w-10 border-4 border-slate-200 border-t-blue-600"></div>
                                        <p className="text-slate-500 font-medium">
                                            Cargando clientes...
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        ) : clientes.length === 0 ? (
                            <tr>
                                <td
                                    colSpan="7"
                                    className="px-6 py-16 text-center"
                                >
                                    <div className="flex flex-col items-center justify-center text-slate-400">
                                        <i className="mgc_user_3_line text-6xl mb-4 text-slate-300"></i>
                                        <p className="text-base font-medium text-slate-500">
                                            No se encontraron clientes
                                        </p>
                                        <span className="text-sm text-slate-400 mt-1">
                                            Intenta ajustar los filtros o crear
                                            uno nuevo
                                        </span>
                                    </div>
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
                <div className="px-6 py-4 bg-slate-50 border-t border-slate-200">
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
