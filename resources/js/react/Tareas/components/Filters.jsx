import React, { useEffect, useRef } from "react";

export default function Filters({
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
    usuarios,
    obras,
    isAdmin,
    onLimpiarFiltros,
}) {
    // Pequeña debounce para search
    const timerRef = useRef(null);
    const [localSearch, setLocalSearch] = React.useState(search);
    useEffect(() => setLocalSearch(search), [search]);
    useEffect(() => {
        if (localSearch === search) return;
        if (timerRef.current) clearTimeout(timerRef.current);
        timerRef.current = setTimeout(() => setSearch(localSearch), 400);
        return () => clearTimeout(timerRef.current);
        // eslint-disable-next-line
    }, [localSearch]);

    const VISTAS = [
        { key: "mis_tareas", label: "Mis tareas", icon: "mgc_user_3_line" },
        { key: "creadas_por_mi", label: "Creadas por mí", icon: "mgc_pencil_line" },
        { key: "todas", label: "Todas", icon: "mgc_global_line" },
    ];

    return (
        <div className="rounded-3xl border border-slate-200 bg-white shadow-sm p-4 sm:p-5 space-y-4">
            {/* Tabs vista */}
            <div className="flex flex-wrap gap-2">
                {VISTAS.map((v) => {
                    const active = vista === v.key;
                    return (
                        <button
                            key={v.key}
                            type="button"
                            onClick={() => setVista(v.key)}
                            className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition ${
                                active
                                    ? "border-cyan-300 bg-cyan-50 text-cyan-700"
                                    : "border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50"
                            }`}
                        >
                            <i className={v.icon}></i>
                            {v.label}
                            {v.key === "todas" && !isAdmin && (
                                <span
                                    title="Como no eres admin, en 'Todas' verás solo tus tareas o las creadas por ti."
                                    className="ml-0.5 text-amber-500"
                                >
                                    <i className="mgc_information_line text-xs"></i>
                                </span>
                            )}
                        </button>
                    );
                })}
            </div>

            {/* Filtros */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div className="sm:col-span-2">
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Buscar
                    </label>
                    <div className="relative">
                        <i className="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input
                            type="text"
                            value={localSearch}
                            onChange={(e) => setLocalSearch(e.target.value)}
                            placeholder="Título o descripción…"
                            className="w-full rounded-xl border-slate-300 pl-9 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                        />
                    </div>
                </div>

                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Prioridad
                    </label>
                    <select
                        value={filtroPrioridad}
                        onChange={(e) => setFiltroPrioridad(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    >
                        <option value="">Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>

                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Asignado a
                    </label>
                    <select
                        value={filtroAsignado}
                        onChange={(e) => setFiltroAsignado(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    >
                        <option value="">Cualquiera</option>
                        {usuarios.map((u) => (
                            <option key={u.id} value={u.id}>
                                {u.name}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Obra
                    </label>
                    <select
                        value={filtroObra}
                        onChange={(e) => setFiltroObra(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    >
                        <option value="">Cualquiera</option>
                        {obras.map((o) => (
                            <option key={o.id} value={o.id}>
                                {o.nombre}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Vence desde
                    </label>
                    <input
                        type="date"
                        value={fechaDesde}
                        onChange={(e) => setFechaDesde(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    />
                </div>

                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Vence hasta
                    </label>
                    <input
                        type="date"
                        value={fechaHasta}
                        onChange={(e) => setFechaHasta(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    />
                </div>

                <div className="sm:col-span-2 lg:col-span-4 flex justify-end pt-1 border-t border-slate-100">
                    <button
                        type="button"
                        onClick={onLimpiarFiltros}
                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white text-slate-600 text-sm px-4 py-2 hover:bg-slate-50"
                    >
                        <i className="mgc_broom_line"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>
    );
}
