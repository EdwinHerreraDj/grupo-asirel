import React from "react";
import ClientesTable from "./components/ClientesTable";
import FormularioCliente from "./components/FormularioCliente";
import ModalEliminar from "./components/ModalEliminar";
import Filters from "./components/Filters";

export default function ClientesLayout({
    clientes,
    loading,
    search,
    setSearch,
    filtroActivo,
    setFiltroActivo,
    onAplicarFiltros,
    onLimpiarFiltros,
    onAbrirModalCrear,
    onAbrirModalEditar,
    onAbrirModalEliminar,
    showModal,
    setShowModal,
    clienteToEdit,
    onGuardarCliente,
    showDeleteModal,
    setShowDeleteModal,
    clienteToDelete,
    onEliminarCliente,
    currentPage,
    lastPage,
    total,
    onPageChange,
    onBack,
    deleteError,
    setDeleteError,
    stats,
}) {
    return (
        <div>
            <div className="space-y-4">
                {/* CABECERA */}
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                    <div className="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                        <div className="flex items-center gap-3 mb-4">
                            <button
                                onClick={onBack}
                                className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                            >
                                <i className="mgc_arrow_left_line text-lg"></i>
                                <span className="hidden sm:inline">
                                    Regresar
                                </span>
                            </button>
                        </div>

                        <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div className="min-w-0">
                                <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                    <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                    Directorio
                                </div>
                                <h2 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                                    Clientes
                                </h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    Gestiona y administra los clientes
                                    registrados en la empresa.
                                </p>
                            </div>

                            <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                <button
                                    onClick={onAbrirModalCrear}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] transition hover:from-cyan-500 hover:to-blue-500"
                                >
                                    <i className="mgc_add_line"></i> Nuevo
                                    cliente
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* STATS */}
                    {stats && (
                        <div className="grid grid-cols-3 gap-3 px-5 py-5 sm:px-6">
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Total
                                </p>
                                <p className="mt-1 text-xl font-bold text-slate-900">
                                    {stats.total ?? 0}
                                </p>
                            </div>
                            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-emerald-700">
                                    Activos
                                </p>
                                <p className="mt-1 text-xl font-bold text-emerald-800">
                                    {stats.activos ?? 0}
                                </p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Inactivos
                                </p>
                                <p className="mt-1 text-xl font-bold text-slate-700">
                                    {stats.inactivos ?? 0}
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {/* FILTROS */}
                <Filters
                    search={search}
                    setSearch={setSearch}
                    filtroActivo={filtroActivo}
                    setFiltroActivo={setFiltroActivo}
                    onAplicarFiltros={onAplicarFiltros}
                    onLimpiarFiltros={onLimpiarFiltros}
                />

                {/* TABLA */}
                <ClientesTable
                    clientes={clientes}
                    loading={loading}
                    onEditar={onAbrirModalEditar}
                    onEliminar={onAbrirModalEliminar}
                    onNuevo={onAbrirModalCrear}
                    currentPage={currentPage}
                    lastPage={lastPage}
                    total={total}
                    onPageChange={onPageChange}
                />
            </div>

            {/* MODAL FORMULARIO */}
            {showModal && (
                <div
                    className="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
                    role="dialog"
                    aria-modal="true"
                >
                    <div className="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col">
                        <div className="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5 shrink-0">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                        <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                        {clienteToEdit ? "Editar" : "Nuevo"}
                                    </div>
                                    <h3 className="mt-2 text-lg font-semibold text-slate-900">
                                        {clienteToEdit
                                            ? "Editar cliente"
                                            : "Nuevo cliente"}
                                    </h3>
                                    <p className="text-sm text-slate-500 mt-0.5">
                                        Los campos con * son obligatorios.
                                    </p>
                                </div>
                                <button
                                    onClick={() => setShowModal(false)}
                                    aria-label="Cerrar"
                                    className="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                >
                                    <i className="mgc_close_line text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div className="overflow-y-auto px-6 py-5">
                            <FormularioCliente
                                cliente={clienteToEdit}
                                onGuardar={onGuardarCliente}
                                onCancelar={() => setShowModal(false)}
                            />
                        </div>
                    </div>
                </div>
            )}

            {/* MODAL ELIMINAR */}
            {showDeleteModal && (
                <ModalEliminar
                    cliente={clienteToDelete}
                    errorMessage={deleteError}
                    onConfirmar={onEliminarCliente}
                    onCancelar={() => {
                        setShowDeleteModal(false);
                        setDeleteError(null);
                    }}
                />
            )}
        </div>
    );
}
