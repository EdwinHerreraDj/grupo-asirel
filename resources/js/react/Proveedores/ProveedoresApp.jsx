// resources/js/react/Proveedores/ProveedoresApp.jsx
import React, { useState, useEffect } from "react";
import ProveedoresLayout from "./ProveedoresLayout";
import {
    NotificationProvider,
    useNotification,
} from "../shared/NotificationContext";
import api from "../shared/api";

function ProveedoresAppContent() {
    const [proveedores, setProveedores] = useState([]);
    const [loading, setLoading] = useState(false);
    const [showModal, setShowModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [proveedorToEdit, setProveedorToEdit] = useState(null);
    const [proveedorToDelete, setProveedorToDelete] = useState(null);

    // Filtros
    const [search, setSearch] = useState("");
    const [filtroActivo, setFiltroActivo] = useState("");
    const [filtroTipo, setFiltroTipo] = useState("");

    // Paginación
    const [currentPage, setCurrentPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);

    const { showSuccess, showError } = useNotification();

    const loadProveedores = async (page = 1) => {
        setLoading(true);

        try {
            const params = { page };

            if (search) params.search = search;
            if (filtroActivo !== "") params.filtroActivo = filtroActivo;
            if (filtroTipo !== "") params.filtroTipo = filtroTipo;

            const response = await api.get("/proveedores", { params });

            setProveedores(response.data.data || []);
            setCurrentPage(response.data.current_page || 1);
            setLastPage(response.data.last_page || 1);
            setTotal(response.data.total || 0);
        } catch (error) {
            console.error("Error loading proveedores:", error);
            showError("Error al cargar los proveedores");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadProveedores();
    }, []);

    const handleAplicarFiltros = () => {
        loadProveedores(1);
    };

    const handleLimpiarFiltros = () => {
        setSearch("");
        setFiltroActivo("");
        setFiltroTipo("");
        setTimeout(() => loadProveedores(1), 0);
    };

    const handleAbrirModalCrear = () => {
        setProveedorToEdit(null);
        setShowModal(true);
    };

    const handleAbrirModalEditar = (proveedor) => {
        setProveedorToEdit(proveedor);
        setShowModal(true);
    };

    const handleAbrirModalEliminar = (proveedor) => {
        setProveedorToDelete(proveedor);
        setShowDeleteModal(true);
    };

    const handleGuardarProveedor = async (data) => {
        try {
            if (proveedorToEdit) {
                await api.put(`/proveedores/${proveedorToEdit.id}`, data);
                showSuccess("Proveedor actualizado exitosamente");
            } else {
                await api.post("/proveedores", data);
                showSuccess("Proveedor creado exitosamente");
            }

            setShowModal(false);
            loadProveedores(currentPage);
        } catch (error) {
            console.error("Error saving proveedor:", error);
            showError(
                error.response?.data?.message ||
                    "Error al guardar el proveedor",
            );
            throw error;
        }
    };

    const handleEliminarProveedor = async () => {
        try {
            await api.delete(`/proveedores/${proveedorToDelete.id}`);
            showSuccess("Proveedor eliminado exitosamente");
            setShowDeleteModal(false);
            loadProveedores(currentPage);
        } catch (error) {
            console.error("Error deleting proveedor:", error);
            showError(
                error.response?.data?.message ||
                    "Error al eliminar el proveedor",
            );
        }
    };

    const handleBack = () => {
        window.location.href = "/empresa";
    };

    return (
        <ProveedoresLayout
            proveedores={proveedores}
            loading={loading}
            search={search}
            setSearch={setSearch}
            filtroActivo={filtroActivo}
            setFiltroActivo={setFiltroActivo}
            filtroTipo={filtroTipo}
            setFiltroTipo={setFiltroTipo}
            onAplicarFiltros={handleAplicarFiltros}
            onLimpiarFiltros={handleLimpiarFiltros}
            onAbrirModalCrear={handleAbrirModalCrear}
            onAbrirModalEditar={handleAbrirModalEditar}
            onAbrirModalEliminar={handleAbrirModalEliminar}
            showModal={showModal}
            setShowModal={setShowModal}
            proveedorToEdit={proveedorToEdit}
            onGuardarProveedor={handleGuardarProveedor}
            showDeleteModal={showDeleteModal}
            setShowDeleteModal={setShowDeleteModal}
            proveedorToDelete={proveedorToDelete}
            onEliminarProveedor={handleEliminarProveedor}
            currentPage={currentPage}
            lastPage={lastPage}
            total={total}
            onPageChange={loadProveedores}
            onBack={handleBack}
        />
    );
}

export default function ProveedoresApp() {
    return (
        <NotificationProvider>
            <ProveedoresAppContent />
        </NotificationProvider>
    );
}
