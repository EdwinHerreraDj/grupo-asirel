// resources/js/react/Clientes/ClientesLayout.jsx
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
}) {
    return (
        <div className="relative w-full min-w-0">
            <div className="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6 lg:p-8">
                {/* HEADER */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                    <button
                        onClick={onBack}
                        className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200 text-slate-600 font-medium shadow-sm hover:bg-slate-100 hover:text-slate-900 transition-all duration-200 w-full md:w-auto"
                    >
                        <i className="mgc_arrow_left_line text-lg"></i>
                        Regresar
                    </button>

                    <button
                        onClick={onAbrirModalCrear}
                        className="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white rounded-xl shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 font-semibold w-full md:w-auto"
                    >
                        <i className="mgc_add_line text-lg"></i>
                        Nuevo cliente
                    </button>
                </div>

                {/* TÍTULO */}
                <div className="mb-8">
                    <h2 className="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-800 tracking-tight">
                        Clientes
                    </h2>

                    <p className="text-sm sm:text-base text-slate-500 mt-2 max-w-2xl">
                        Gestión y administración de clientes registrados
                    </p>

                    <div className="mt-4 h-1 w-20 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full"></div>
                </div>

                {/* FILTROS */}
                <div className="mb-8">
                    <Filters
                        search={search}
                        setSearch={setSearch}
                        filtroActivo={filtroActivo}
                        setFiltroActivo={setFiltroActivo}
                        onAplicarFiltros={onAplicarFiltros}
                        onLimpiarFiltros={onLimpiarFiltros}
                    />
                </div>

                {/* TABLA */}
                <ClientesTable
                    clientes={clientes}
                    loading={loading}
                    onEditar={onAbrirModalEditar}
                    onEliminar={onAbrirModalEliminar}
                    currentPage={currentPage}
                    lastPage={lastPage}
                    total={total}
                    onPageChange={onPageChange}
                />
            </div>

            {/* MODAL FORMULARIO */}
            {showModal && (
                <div
                    className="fixed inset-0 z-[1000] bg-slate-900/60 backdrop-blur-sm flex items-end sm:items-center justify-center"
                    role="dialog"
                    aria-modal="true"
                >
                    <div className="relative w-full sm:max-w-2xl bg-white shadow-2xl border border-slate-200 rounded-t-3xl sm:rounded-2xl max-h-[100vh] sm:max-h-[90vh] overflow-y-auto p-5 sm:p-8 animate-in fade-in zoom-in-95 duration-200">
                        <button
                            onClick={() => setShowModal(false)}
                            className="absolute top-4 right-4 w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-600 transition-colors text-xl"
                        >
                            ×
                        </button>

                        <FormularioCliente
                            cliente={clienteToEdit}
                            onGuardar={onGuardarCliente}
                            onCancelar={() => setShowModal(false)}
                        />
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
