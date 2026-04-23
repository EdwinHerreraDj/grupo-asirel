import React from "react";
import ClienteRow, { ClienteCard } from "./ClienteRow";
import Pagination from "../../shared/Pagination";

export default function ClientesTable({
    clientes = [],
    loading,
    onEditar,
    onEliminar,
    onNuevo,
    currentPage,
    lastPage,
    total,
    onPageChange,
}) {
    const EmptyState = () => (
        <div className="flex flex-col items-center justify-center py-16 px-4">
            <div className="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                <i className="mgc_user_3_line text-3xl text-slate-400"></i>
            </div>
            <p className="text-sm font-semibold text-slate-600">
                No se encontraron clientes
            </p>
            <p className="text-xs text-slate-400 mt-1">
                Ajusta los filtros o crea uno nuevo.
            </p>
            {onNuevo && (
                <button
                    type="button"
                    onClick={onNuevo}
                    className="mt-4 inline-flex items-center gap-2 rounded-xl bg-slate-900 text-white text-xs font-semibold px-3 py-2 hover:bg-slate-800"
                >
                    <i className="mgc_add_line"></i> Nuevo cliente
                </button>
            )}
        </div>
    );

    const LoadingState = () => (
        <div className="flex flex-col items-center justify-center py-16 gap-3">
            <div className="animate-spin rounded-full h-10 w-10 border-4 border-slate-200 border-t-cyan-500"></div>
            <p className="text-xs text-slate-500 font-medium">
                Cargando clientes…
            </p>
        </div>
    );

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            {/* Desktop */}
            <div className="hidden md:block overflow-x-auto">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50/80 text-slate-600">
                        <tr className="border-b border-slate-200">
                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">
                                Cliente
                            </th>
                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">
                                Contacto
                            </th>
                            <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide hidden xl:table-cell">
                                Dirección
                            </th>
                            <th className="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">
                                Estado
                            </th>
                            <th className="w-24 px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 bg-white">
                        {loading ? (
                            <tr>
                                <td colSpan={5}>
                                    <LoadingState />
                                </td>
                            </tr>
                        ) : clientes.length === 0 ? (
                            <tr>
                                <td colSpan={5}>
                                    <EmptyState />
                                </td>
                            </tr>
                        ) : (
                            clientes.map((c) => (
                                <ClienteRow
                                    key={c.id}
                                    cliente={c}
                                    onEditar={onEditar}
                                    onEliminar={onEliminar}
                                />
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {/* Mobile */}
            <div className="md:hidden divide-y divide-slate-100">
                {loading ? (
                    <LoadingState />
                ) : clientes.length === 0 ? (
                    <EmptyState />
                ) : (
                    clientes.map((c) => (
                        <ClienteCard
                            key={c.id}
                            cliente={c}
                            onEditar={onEditar}
                            onEliminar={onEliminar}
                        />
                    ))
                )}
            </div>

            {/* Paginación */}
            {!loading && clientes.length > 0 && lastPage > 1 && (
                <div className="px-4 py-3 sm:px-5 bg-slate-50/40 border-t border-slate-200">
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
