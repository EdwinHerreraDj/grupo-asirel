import React from "react";

const PRIORIDAD_META = {
    baja: {
        label: "Baja",
        classes: "border-slate-200 bg-slate-50 text-slate-600",
    },
    media: {
        label: "Media",
        classes: "border-amber-200 bg-amber-50 text-amber-700",
    },
    alta: {
        label: "Alta",
        classes: "border-red-200 bg-red-50 text-red-700",
    },
};

function iniciales(nombre) {
    if (!nombre) return "?";
    const partes = nombre.trim().split(/\s+/);
    if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
    return (partes[0][0] + partes[1][0]).toUpperCase();
}

function formatFecha(fechaISO) {
    if (!fechaISO) return null;
    const d = new Date(fechaISO);
    return `${String(d.getDate()).padStart(2, "0")}/${String(d.getMonth() + 1).padStart(2, "0")}/${d.getFullYear()}`;
}

function diasRestantes(fechaISO) {
    if (!fechaISO) return null;
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fecha = new Date(fechaISO);
    fecha.setHours(0, 0, 0, 0);
    const diff = Math.round((fecha - hoy) / (1000 * 60 * 60 * 24));
    return diff;
}

export default function TareaCard({
    tarea,
    userId,
    isAdmin,
    onEditar,
    onEliminar,
    onCambiarEstado,
}) {
    const prioridad = PRIORIDAD_META[tarea.prioridad] || PRIORIDAD_META.media;
    const dias = diasRestantes(tarea.fecha_limite);
    const completada = tarea.estado === "completada";
    const vencida = !completada && dias !== null && dias < 0;
    const proxima = !completada && dias !== null && dias >= 0 && dias <= 3;

    const puedeEliminar = isAdmin || tarea.creado_por === userId;

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm hover:shadow-md transition-shadow">
            {/* Cabecera con badge prioridad + acciones */}
            <div className="flex items-start justify-between gap-2 mb-2">
                <span
                    className={`inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ${prioridad.classes}`}
                >
                    {prioridad.label}
                </span>
                <div className="flex items-center gap-1">
                    <button
                        onClick={() => onEditar(tarea)}
                        title="Editar"
                        className="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-cyan-700"
                    >
                        <i className="mgc_edit_2_line text-sm"></i>
                    </button>
                    {puedeEliminar && (
                        <button
                            onClick={() => onEliminar(tarea)}
                            title="Eliminar"
                            className="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"
                        >
                            <i className="mgc_delete_line text-sm"></i>
                        </button>
                    )}
                </div>
            </div>

            {/* Título */}
            <h4 className={`text-sm font-semibold leading-snug mb-1 ${
                completada ? "text-slate-500 line-through" : "text-slate-900"
            }`}>
                {tarea.titulo}
            </h4>

            {/* Descripción truncada */}
            {tarea.descripcion && (
                <p className="text-xs text-slate-500 line-clamp-2 mb-2">
                    {tarea.descripcion}
                </p>
            )}

            {/* Obra (si aplica) */}
            {tarea.obra && (
                <div className="inline-flex items-center gap-1 text-[11px] text-slate-600 bg-slate-50 border border-slate-200 rounded-md px-1.5 py-0.5 mb-2">
                    <i className="mgc_building_2_line text-slate-400"></i>
                    <span className="truncate max-w-[160px]">{tarea.obra.nombre}</span>
                </div>
            )}

            {/* Fecha límite */}
            {tarea.fecha_limite && (
                <div className={`flex items-center gap-1.5 text-[11px] mb-2 ${
                    vencida
                        ? "text-red-700 font-medium"
                        : proxima
                          ? "text-amber-700 font-medium"
                          : "text-slate-500"
                }`}>
                    <i className={vencida ? "mgc_warning_line" : "mgc_calendar_line"}></i>
                    <span>
                        {formatFecha(tarea.fecha_limite)}
                        {!completada && dias !== null && (
                            <span className="ml-1">
                                {vencida
                                    ? `· vencida hace ${Math.abs(dias)}d`
                                    : dias === 0
                                      ? "· hoy"
                                      : `· en ${dias}d`}
                            </span>
                        )}
                    </span>
                </div>
            )}

            {/* Footer: asignado a + select de estado */}
            <div className="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                <div className="flex items-center gap-1.5 min-w-0">
                    <span
                        className="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-cyan-700 text-[10px] font-bold"
                        title={`Asignada a ${tarea.asignadoA?.name ?? "—"}`}
                    >
                        {iniciales(tarea.asignadoA?.name)}
                    </span>
                    <span className="text-[11px] text-slate-600 truncate">
                        {tarea.asignadoA?.name ?? "—"}
                    </span>
                </div>

                <select
                    value={tarea.estado}
                    onChange={(e) => onCambiarEstado(tarea.id, e.target.value)}
                    className="text-[11px] rounded-md border-slate-300 py-0.5 pl-2 pr-7 focus:border-cyan-500 focus:ring-cyan-500"
                >
                    <option value="pendiente">Pendiente</option>
                    <option value="en_curso">En curso</option>
                    <option value="completada">Completada</option>
                </select>
            </div>

            {/* Creado por (info adicional) */}
            {tarea.creado_por !== userId && tarea.creadoPor && (
                <p className="text-[10px] text-slate-400 mt-1.5">
                    Creada por {tarea.creadoPor.name}
                </p>
            )}
        </div>
    );
}
