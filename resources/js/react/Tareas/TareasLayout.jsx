import React from "react";
import Filters from "./components/Filters";
import KanbanColumn from "./components/KanbanColumn";
import FormularioTarea from "./components/FormularioTarea";
import ModalEliminar from "./components/ModalEliminar";

const ESTADOS = [
    {
        key: "pendiente",
        label: "Pendiente",
        accent: "slate",
        gradient: "from-slate-500 to-slate-600",
        icon: "mgc_time_line",
    },
    {
        key: "en_curso",
        label: "En curso",
        accent: "blue",
        gradient: "from-blue-500 to-cyan-500",
        icon: "mgc_loading_3_line",
    },
    {
        key: "completada",
        label: "Completada",
        accent: "emerald",
        gradient: "from-emerald-500 to-green-500",
        icon: "mgc_check_line",
    },
];

export default function TareasLayout({
    tareas,
    stats,
    usuarios,
    obras,
    meta,
    loading,
    vista,
    setVista,
    search,
    setSearch,
    filtroPrioridad,
    setFiltroPrioridad,
    filtroAsignado,
    setFiltroAsignado,
    filtroObra,
    setFiltroObra,
    fechaDesde,
    setFechaDesde,
    fechaHasta,
    setFechaHasta,
    onLimpiarFiltros,
    onAbrirNueva,
    onAbrirEditar,
    onCambiarEstado,
    onEliminar,
    showFormulario,
    setShowFormulario,
    tareaEditar,
    setTareaEditar,
    onGuardarTarea,
    tareaEliminar,
    setTareaEliminar,
    onConfirmarEliminar,
}) {
    const tareasPorEstado = (estado) =>
        tareas.filter((t) => t.estado === estado);

    return (
        <div>
            <div className="space-y-4">
                {/* CABECERA */}
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                    <div className="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div className="min-w-0">
                                <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                    <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                    Personal
                                </div>
                                <h2 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                                    Tareas
                                </h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    Organiza tu trabajo en estilo kanban: tareas
                                    pendientes, en curso y completadas.
                                </p>
                            </div>

                            <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                <button
                                    onClick={onAbrirNueva}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] transition hover:from-cyan-500 hover:to-blue-500"
                                >
                                    <i className="mgc_add_line"></i> Nueva tarea
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* STATS */}
                    {stats && (
                        <div className="grid grid-cols-2 gap-3 px-5 py-5 sm:grid-cols-4 sm:px-6">
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Pendientes
                                </p>
                                <p className="mt-1 text-xl font-bold text-slate-900">
                                    {stats.pendiente}
                                </p>
                            </div>
                            <div className="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-blue-700">
                                    En curso
                                </p>
                                <p className="mt-1 text-xl font-bold text-blue-800">
                                    {stats.en_curso}
                                </p>
                            </div>
                            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-emerald-700">
                                    Completadas
                                </p>
                                <p className="mt-1 text-xl font-bold text-emerald-800">
                                    {stats.completada}
                                </p>
                            </div>
                            <div className={`rounded-2xl border px-4 py-3 ${
                                stats.vencidas > 0
                                    ? "border-red-200 bg-red-50"
                                    : "border-slate-200 bg-slate-50/70"
                            }`}>
                                <p className={`text-[11px] font-semibold uppercase tracking-[0.12em] ${
                                    stats.vencidas > 0
                                        ? "text-red-700"
                                        : "text-slate-500"
                                }`}>
                                    Vencidas
                                </p>
                                <p className={`mt-1 text-xl font-bold ${
                                    stats.vencidas > 0
                                        ? "text-red-800"
                                        : "text-slate-700"
                                }`}>
                                    {stats.vencidas}
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {/* FILTROS */}
                <Filters
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
                    usuarios={usuarios}
                    obras={obras}
                    isAdmin={meta.is_admin}
                    onLimpiarFiltros={onLimpiarFiltros}
                />

                {/* KANBAN */}
                {loading ? (
                    <div className="rounded-3xl border border-slate-200 bg-white p-16 text-center">
                        <div className="mx-auto mb-3 h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-cyan-500"></div>
                        <p className="text-sm text-slate-500 font-medium">
                            Cargando tareas…
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {ESTADOS.map((est) => (
                            <KanbanColumn
                                key={est.key}
                                config={est}
                                tareas={tareasPorEstado(est.key)}
                                userId={meta.user_id}
                                isAdmin={meta.is_admin}
                                onEditar={onAbrirEditar}
                                onEliminar={onEliminar}
                                onCambiarEstado={onCambiarEstado}
                            />
                        ))}
                    </div>
                )}
            </div>

            {/* MODAL FORMULARIO */}
            {showFormulario && (
                <FormularioTarea
                    tarea={tareaEditar}
                    usuarios={usuarios}
                    obras={obras}
                    onGuardar={onGuardarTarea}
                    onCancelar={() => {
                        setShowFormulario(false);
                        setTareaEditar(null);
                    }}
                />
            )}

            {/* MODAL ELIMINAR */}
            {tareaEliminar && (
                <ModalEliminar
                    tarea={tareaEliminar}
                    onConfirmar={onConfirmarEliminar}
                    onCancelar={() => setTareaEliminar(null)}
                />
            )}
        </div>
    );
}
