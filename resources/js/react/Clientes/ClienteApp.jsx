import React, { useState, useEffect } from "react";
import ClientesLayout from "./ClientesLayout";
import {
    NotificationProvider,
    useNotification,
} from "../shared/NotificationContext";

import api from "../shared/api";

function ClientesAppContent() {
    const [clientes, setClientes] = useState([]);
    const [loading, setLoading] = useState(false);
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [clienteToEdit, setClienteToEdit] = useState(null);
    const [clienteToDelete, setClienteToDelete] = useState(null);

    // Filtros
    const [search, setSearch] = useState("");
    const [filtroActivo, setFiltroActivo] = useState("");

    // Paginación
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);

    const { showSuccess, showError } = useNotification();

    const loadClientes = async (page = 1) => {
        setLoading(true);
        try {
            const params = { page };

            if (search) params.search = search;
            if (filtroActivo !== "") params.filtroActivo = filtroActivo;

            const response = await api.get("/clientes", { params });

            setClientes(response.data.data || []);
            setCurrentPage(response.data.current_page || 1);
            setLastPage(response.data.last_page || 1);
            setTotal(response.data.total || 0);
        } catch (error) {
            console.error("Error loading clientes:", error);
            showError("Error al cargar los clientes");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadClientes();
    }, []);

    const handleAplicarFiltros = () => {
        loadClientes(1);
    };

    const handleLimpiarFiltros = () => {
        setSearch("");
        setFiltroActivo("");
        setTimeout(() => loadClientes(1), 0);
    };

    const handleAbrirModalCrear = () => {
        setClienteToEdit(null);
        setShowModal(true);
    };

    const handleAbrirModalEditar = (cliente) => {
        setClienteToEdit(cliente);
        setShowModal(true);
    };

    const handleAbrirModalEliminar = (cliente) => {
        setClienteToDelete(cliente);
        setShowDeleteModal(true);
    };

    const handleGuardarCliente = async (data) => {
        try {
            if (clienteToEdit) {
                await api.put(`/clientes/${clienteToEdit.id}`, data);
                showSuccess("Cliente actualizado exitosamente");
            } else {
                await api.post("/clientes", data);
                showSuccess("Cliente creado exitosamente");
            }

            setShowModal(false);
            loadClientes(currentPage);
        } catch (error) {
            console.error("Error saving cliente:", error);
            showError(
                error.response?.data?.message || "Error al guardar el cliente",
            );
            throw error;
        }
    };

    const handleEliminarCliente = async () => {
        try {
            await api.delete(`/clientes/${clienteToDelete.id}`);
            showSuccess("Cliente eliminado exitosamente");
            setShowDeleteModal(false);
            loadClientes(currentPage);
        } catch (error) {
            console.error("Error deleting cliente:", error);
            showError(
                error.response?.data?.message || "Error al eliminar el cliente",
            );
        }
    };

    const handleBack = () => {
        window.location.href = "/empresa";
    };

    return (
        <ClientesLayout
            clientes={clientes}
            loading={loading}
            search={search}
            setSearch={setSearch}
            filtroActivo={filtroActivo}
            setFiltroActivo={setFiltroActivo}
            onAplicarFiltros={handleAplicarFiltros}
            onLimpiarFiltros={handleLimpiarFiltros}
            onAbrirModalCrear={handleAbrirModalCrear}
            onAbrirModalEditar={handleAbrirModalEditar}
            onAbrirModalEliminar={handleAbrirModalEliminar}
            showModal={showModal}
            setShowModal={setShowModal}
            clienteToEdit={clienteToEdit}
            onGuardarCliente={handleGuardarCliente}
            showDeleteModal={showDeleteModal}
            setShowDeleteModal={setShowDeleteModal}
            clienteToDelete={clienteToDelete}
            onEliminarCliente={handleEliminarCliente}
            currentPage={currentPage}
            lastPage={lastPage}
            total={total}
            onPageChange={loadClientes}
            onBack={handleBack}
        />
    );
}

export default function ClientesApp() {
    return (
        <NotificationProvider>
            <ClientesAppContent />
        </NotificationProvider>
    );
}
