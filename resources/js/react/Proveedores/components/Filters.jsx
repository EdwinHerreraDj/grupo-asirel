import React, { useEffect, useRef } from "react";

export default function Filters({
    search,
    setSearch,
    filtroActivo,
    setFiltroActivo,
    filtroTipo,
    setFiltroTipo,
    onAplicarFiltros,
    onLimpiarFiltros,
}) {
    // Debounce: aplica filtros al dejar de escribir 400ms
    const debounceRef = useRef(null);
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            onAplicarFiltros();
        }, 400);
        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search, filtroActivo, filtroTipo]);

    return (
        <div className="rounded-3xl border border-slate-200 bg-white shadow-sm p-4 sm:p-5">
            <div className="flex items-center gap-2 mb-4">
                <i className="mgc_filter_line text-slate-500"></i>
                <h3 className="text-sm font-semibold text-slate-700">
                    Filtros
                </h3>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                {/* Buscar */}
                <div className="sm:col-span-2">
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Buscar
                    </label>
                    <div className="relative">
                        <i className="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Nombre, CIF, email o teléfono…"
                            className="w-full rounded-xl border-slate-300 pl-9 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                        />
                    </div>
                </div>

                {/* Tipo */}
                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Tipo
                    </label>
                    <select
                        value={filtroTipo}
                        onChange={(e) => setFiltroTipo(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    >
                        <option value="">Todos</option>
                        <option value="material">Material</option>
                        <option value="mano_obra">Mano de obra</option>
                        <option value="servicio">Servicio</option>
                        <option value="mixto">Mixto</option>
                    </select>
                </div>

                {/* Estado */}
                <div>
                    <label className="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                        Estado
                    </label>
                    <select
                        value={filtroActivo}
                        onChange={(e) => setFiltroActivo(e.target.value)}
                        className="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    >
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                {/* Acciones */}
                <div className="sm:col-span-2 lg:col-span-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 border-t border-slate-100">
                    <button
                        onClick={onLimpiarFiltros}
                        type="button"
                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white text-slate-600 text-sm px-4 py-2.5 hover:bg-slate-50"
                    >
                        <i className="mgc_broom_line"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    );
}
