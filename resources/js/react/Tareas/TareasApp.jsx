import React, { useEffect, useState, useCallback } from "react";
import api from "../shared/api";
import {
    NotificationProvider,
    useNotification,
} from "../shared/NotificationContext";
import TareasLayout from "./TareasLayout";

function TareasAppContent() {
    const [tareas, setTareas] = useState([]);
    const [stats, setStats] = useState(null);
    const [usuarios, setUsuarios] = useState([]);
    const [obras, setObras] = useState([]);
    const [meta, setMeta] = useState({ user_id: null, is_admin: false });
    const [loading, setLoading] = useState(true);

    // Filtros
    const [vista, setVista] = useState("mis_tareas"); // mis_tareas | creadas_por_mi | todas
    const [search, setSearch] = useState("");
    const [filtroPrioridad, setFiltroPrioridad] = useState("");
    const [filtroAsignado, setFiltroAsignado] = useState("");
    const [filtroObra, setFiltroObra] = useState("");
    const [fechaDesde, setFechaDesde] = useState("");
    const [fechaHasta, setFechaHasta] = useState("");

    // Modales
    const [showFormulario, setShowFormulario] = useState(false);
    const [tareaEditar, setTareaEditar] = useState(null);
    const [tareaEliminar, setTareaEliminar] = useState(null);

    const { showSuccess, showError } = useNotification();

    const cargar = useCallback(async () => {
        setLoading(true);
        try {
            const params = { vista };
            if (search) params.search = search;
            if (filtroPrioridad) params.prioridad = filtroPrioridad;
            if (filtroAsignado) params.asignado_a = filtroAsignado;
            if (filtroObra) params.obra_id = filtroObra;
            if (fechaDesde) params.fecha_desde = fechaDesde;
            if (fechaHasta) params.fecha_hasta = fechaHasta;

            const { data } = await api.get("/tareas", { params });
            setTareas(data.tareas || []);
            setStats(data.stats || null);
            setUsuarios(data.usuarios || []);
            setObras(data.obras || []);
            setMeta(data.meta || { user_id: null, is_admin: false });
        } catch (err) {
            console.error("Error cargando tareas:", err);
            showError("Error al cargar las tareas.");
        } finally {
            setLoading(false);
        }
    }, [
        vista,
        search,
        filtroPrioridad,
        filtroAsignado,
        filtroObra,
        fechaDesde,
        fechaHasta,
        showError,
    ]);

    useEffect(() => {
        cargar();
    }, [cargar]);

    const handleAbrirNueva = () => {
        setTareaEditar(null);
        setShowFormulario(true);
    };

    const handleAbrirEditar = (tarea) => {
        setTareaEditar(tarea);
        setShowFormulario(true);
    };

    const handleGuardarTarea = async (data) => {
        try {
            if (tareaEditar) {
                await api.put(`/tareas/${tareaEditar.id}`, data);
                showSuccess("Tarea actualizada correctamente.");
            } else {
                await api.post("/tareas", data);
                showSuccess("Tarea creada correctamente.");
            }
            setShowFormulario(false);
            setTareaEditar(null);
            await cargar();
        } catch (err) {
            console.error("Error guardando tarea:", err);
            showError(
                err.response?.data?.message ||
                    "Error al guardar la tarea.",
            );
            throw err;
        }
    };

    const handleCambiarEstado = async (tareaId, nuevoEstado) => {
        try {
            await api.patch(`/tareas/${tareaId}/estado`, {
                estado: nuevoEstado,
            });
            await cargar();
        } catch (err) {
            console.error("Error cambiando estado:", err);
            showError("Error al cambiar el estado.");
        }
    };

    const handleEliminar = async () => {
        if (!tareaEliminar) return;
        try {
            await api.delete(`/tareas/${tareaEliminar.id}`);
            showSuccess("Tarea eliminada correctamente.");
            setTareaEliminar(null);
            await cargar();
        } catch (err) {
            console.error("Error eliminando tarea:", err);
            showError(
                err.response?.data?.message ||
                    "Error al eliminar la tarea.",
            );
        }
    };

    const handleLimpiarFiltros = () => {
        setSearch("");
        setFiltroPrioridad("");
        setFiltroAsignado("");
        setFiltroObra("");
        setFechaDesde("");
        setFechaHasta("");
    };

    return (
        <TareasLayout
            tareas={tareas}
            stats={stats}
            usuarios={usuarios}
            obras={obras}
            meta={meta}
            loading={loading}
            // filtros
            vista={vista}
            setVista={setVista}
            search={search}
            setSearch={setSearch}
            filtroPrioridad={filtroPrioridad}
            setFiltroPrioridad={setFiltroPrioridad}
            filtroAsignado={filtroAsignado}
            setFiltroAsignado={setFiltroAsignado}
            filtroObra={filtroObra}
            setFiltroObra={setFiltroObra}
            fechaDesde={fechaDesde}
            setFechaDesde={setFechaDesde}
            fechaHasta={fechaHasta}
            setFechaHasta={setFechaHasta}
            onLimpiarFiltros={handleLimpiarFiltros}
            // acciones
            onAbrirNueva={handleAbrirNueva}
            onAbrirEditar={handleAbrirEditar}
            onCambiarEstado={handleCambiarEstado}
            onEliminar={(t) => setTareaEliminar(t)}
            // modales
            showFormulario={showFormulario}
            setShowFormulario={setShowFormulario}
            tareaEditar={tareaEditar}
            setTareaEditar={setTareaEditar}
            onGuardarTarea={handleGuardarTarea}
            tareaEliminar={tareaEliminar}
            setTareaEliminar={setTareaEliminar}
            onConfirmarEliminar={handleEliminar}
        />
    );
}

export default function TareasApp() {
    return (
        <NotificationProvider>
            <TareasAppContent />
        </NotificationProvider>
    );
}
