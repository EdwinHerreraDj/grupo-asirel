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
}) {
    return (
        <div className="p-6 bg-white rounded-xl shadow relative">
            {/* Header */}
            <div className="flex items-center mb-6">
                <button
                    onClick={onBack}
                    className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium shadow-sm border border-gray-200 hover:bg-gray-200 hover:text-gray-900 transition-all"
                >
                    <i className="mgc_arrow_left_line text-lg"></i>
                    Regresar
                </button>

                <button
                    onClick={onAbrirModalCrear}
                    className="ml-3 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    <i className="mgc_add_line text-lg"></i>
                    Nuevo cliente
                </button>
            </div>

            {/* Título */}
            <h2 className="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
                Clientes
            </h2>

            {/* Filtros */}
            <Filters
                search={search}
                setSearch={setSearch}
                filtroActivo={filtroActivo}
                setFiltroActivo={setFiltroActivo}
                onAplicarFiltros={onAplicarFiltros}
                onLimpiarFiltros={onLimpiarFiltros}
            />

            {/* Tabla */}
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

            {/* Modal Formulario */}
            {showModal && (
                <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
                    <div className="bg-white w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl shadow-2xl p-6 border border-gray-200 relative">
                        <button
                            onClick={() => setShowModal(false)}
                            className="absolute top-3 right-4 text-gray-600 hover:text-red-600 text-2xl"
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

            {/* Modal Eliminar */}
            {showDeleteModal && (
                <ModalEliminar
                    cliente={clienteToDelete}
                    onConfirmar={onEliminarCliente}
                    onCancelar={() => setShowDeleteModal(false)}
                />
            )}
        </div>
    );
}
