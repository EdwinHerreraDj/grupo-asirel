// resources/js/react/Proveedores/ProveedoresLayout.jsx
import React from "react";
import ProveedoresTable from "./components/ProveedoresTable";
import FormularioProveedor from "./components/FormularioProveedor";
import ModalEliminar from "./components/ModalEliminar";
import Filters from "./components/Filters";

export default function ProveedoresLayout({
    proveedores,
    loading,
    search,
    setSearch,
    filtroActivo,
    setFiltroActivo,
    filtroTipo,
    setFiltroTipo,
    onAplicarFiltros,
    onLimpiarFiltros,
    onAbrirModalCrear,
    onAbrirModalEditar,
    onAbrirModalEliminar,
    showModal,
    setShowModal,
    proveedorToEdit,
    onGuardarProveedor,
    showDeleteModal,
    setShowDeleteModal,
    proveedorToDelete,
    onEliminarProveedor,
    currentPage,
    lastPage,
    total,
    onPageChange,
    onBack,
}) {
    return (
        <div className="relative bg-gradient-to-br from-slate-50 to-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6 lg:p-8">
            {/* Header */}
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div className="flex items-center gap-3">
                    <button
                        onClick={onBack}
                        className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200 text-slate-600 font-medium shadow-sm hover:bg-slate-100 hover:text-slate-900 transition-all duration-200"
                    >
                        <i className="mgc_arrow_left_line text-lg"></i>
                        Regresar
                    </button>
                </div>

                <button
                    onClick={onAbrirModalCrear}
                    className="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white rounded-xl shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 font-semibold"
                >
                    <i className="mgc_add_line text-lg"></i>
                    Nuevo proveedor
                </button>
            </div>

            {/* Título */}
            <div className="mb-6">
                <h2 className="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                    Proveedores
                </h2>
                <p className="text-sm text-slate-500 mt-1">
                    Gestión y administración de proveedores registrados
                </p>
                <div className="mt-3 h-1 w-16 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full"></div>
            </div>

            {/* Filtros */}

            <Filters
                search={search}
                setSearch={setSearch}
                filtroActivo={filtroActivo}
                setFiltroActivo={setFiltroActivo}
                filtroTipo={filtroTipo}
                setFiltroTipo={setFiltroTipo}
                onAplicarFiltros={onAplicarFiltros}
                onLimpiarFiltros={onLimpiarFiltros}
            />

            {/* Tabla */}
            <ProveedoresTable
                proveedores={proveedores}
                loading={loading}
                onEditar={onAbrirModalEditar}
                onEliminar={onAbrirModalEliminar}
                currentPage={currentPage}
                lastPage={lastPage}
                total={total}
                onPageChange={onPageChange}
            />

            {/* Modal Formulario */}
            {showModal && (
                <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
                    <div className="relative bg-white w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl border border-slate-200 p-6 sm:p-8 animate-in fade-in zoom-in-95 duration-200">
                        <button
                            onClick={() => setShowModal(false)}
                            className="absolute top-4 right-4 w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors text-xl"
                        >
                            ×
                        </button>

                        <FormularioProveedor
                            proveedor={proveedorToEdit}
                            onGuardar={onGuardarProveedor}
                            onCancelar={() => setShowModal(false)}
                        />
                    </div>
                </div>
            )}

            {/* Modal Eliminar */}
            {showDeleteModal && (
                <ModalEliminar
                    proveedor={proveedorToDelete}
                    onConfirmar={onEliminarProveedor}
                    onCancelar={() => setShowDeleteModal(false)}
                />
            )}
        </div>
    );
}
