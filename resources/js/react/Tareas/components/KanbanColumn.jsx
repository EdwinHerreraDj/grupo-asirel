import React from "react";
import TareaCard from "./TareaCard";

export default function KanbanColumn({
    config,
    tareas,
    userId,
    isAdmin,
    onEditar,
    onEliminar,
    onCambiarEstado,
}) {
    return (
        <div className="rounded-3xl border border-slate-200 bg-slate-50/50 overflow-hidden">
            {/* Header columna */}
            <div className={`bg-gradient-to-r ${config.gradient} px-4 py-3 text-white`}>
                <div className="flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2">
                        <i className={`${config.icon} text-lg`}></i>
                        <span className="font-semibold text-sm uppercase tracking-wide">
                            {config.label}
                        </span>
                    </div>
                    <span className="inline-flex items-center justify-center min-w-[28px] h-6 px-2 rounded-full bg-white/20 text-xs font-bold">
                        {tareas.length}
                    </span>
                </div>
            </div>

            {/* Lista de tareas */}
            <div className="p-3 space-y-3 min-h-[200px]">
                {tareas.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-10 text-center">
                        <i className="mgc_inbox_line text-3xl text-slate-300 mb-2"></i>
                        <p className="text-xs text-slate-400">
                            Sin tareas aquí
                        </p>
                    </div>
                ) : (
                    tareas.map((tarea) => (
                        <TareaCard
                            key={tarea.id}
                            tarea={tarea}
                            userId={userId}
                            isAdmin={isAdmin}
                            onEditar={onEditar}
                            onEliminar={onEliminar}
                            onCambiarEstado={onCambiarEstado}
                        />
                    ))
                )}
            </div>
        </div>
    );
}
