// resources/js/react/Proveedores/components/ProveedoresTable.jsx
import React from "react";
import ProveedorRow from "./ProveedorRow";
import Pagination from "../../shared/Pagination";

export default function ProveedoresTable({
    proveedores,
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
                            <th className="px-6 py-4 text-left font-semibold">
                                CIF
                            </th>
                            <th className="px-6 py-4 text-left font-semibold">
                                Teléfonos
                            </th>
                            <th className="px-6 py-4 text-left font-semibold">
                                Emails
                            </th>
                            <th className="px-6 py-4 text-left font-semibold hidden xl:table-cell">
                                Tipo
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
                                        <div className="animate-spin rounded-full h-12 w-12 border-4 border-slate-200 border-t-cyan-500"></div>
                                        <p className="text-slate-500 font-medium">
                                            Cargando proveedores...
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        ) : proveedores.length === 0 ? (
                            <tr>
                                <td
                                    colSpan="7"
                                    className="px-6 py-20 text-center"
                                >
                                    <div className="flex flex-col items-center justify-center">
                                        <div className="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                            <i className="mgc_building_2_line text-4xl text-slate-400"></i>
                                        </div>
                                        <p className="text-base font-semibold text-slate-600">
                                            No se encontraron proveedores
                                        </p>
                                        <span className="text-sm text-slate-400 mt-1">
                                            Ajusta los filtros o crea uno nuevo
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        ) : (
                            proveedores.map((proveedor) => (
                                <ProveedorRow
                                    key={proveedor.id}
                                    proveedor={proveedor}
                                    onEditar={onEditar}
                                    onEliminar={onEliminar}
                                />
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {/* Paginación */}
            {!loading && proveedores.length > 0 && (
                <div className="px-6 py-5 bg-slate-50/70 border-t border-slate-200">
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
