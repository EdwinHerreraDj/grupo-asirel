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
        <div className="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            {/* Tabla */}
            <div className="overflow-x-auto">
                <table className="min-w-full text-sm text-slate-700">
                    <thead className="bg-slate-50 border-b border-slate-200">
                        <tr className="text-xs uppercase tracking-wider text-slate-500">
                            <th className="px-6 py-4 text-left font-semibold">
                                Nombre
                            </th>

                            <th className="hidden sm:table-cell px-6 py-4 text-left font-semibold">
                                CIF
                            </th>

                            <th className="hidden md:table-cell px-6 py-4 text-left font-semibold">
                                Teléfonos
                            </th>

                            <th className="hidden lg:table-cell px-6 py-4 text-left font-semibold">
                                Emails
                            </th>

                            <th className="hidden xl:table-cell px-6 py-4 text-left font-semibold">
                                Dirección
                            </th>

                            <th className="px-6 py-4 text-center font-semibold">
                                Activo
                            </th>

                            <th className="px-6 py-4 text-right font-semibold">
                                Acción
                            </th>
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {loading ? (
                            <tr>
                                <td
                                    colSpan="7"
                                    className="px-6 py-20 text-center"
                                >
                                    <div className="flex flex-col items-center justify-center gap-4">
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
                                    className="px-6 py-20 text-center"
                                >
                                    <div className="flex flex-col items-center justify-center text-slate-400">
                                        <i className="mgc_user_3_line text-5xl mb-4 text-slate-300"></i>
                                        <p className="text-base font-semibold text-slate-600">
                                            No se encontraron clientes
                                        </p>
                                        <span className="text-sm text-slate-400 mt-1">
                                            Ajusta los filtros o crea uno nuevo
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
                <div className="px-6 py-5 bg-slate-50 border-t border-slate-200">
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
