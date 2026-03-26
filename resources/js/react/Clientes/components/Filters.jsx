import React from "react";

export default function Filters({
    search,
    setSearch,
    filtroActivo,
    setFiltroActivo,
    onAplicarFiltros,
    onLimpiarFiltros,
}) {
    const handleKeyPress = (e) => {
        if (e.key === "Enter") {
            onAplicarFiltros();
        }
    };

    return (
        <div className="mb-8 bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <div className="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                {/* IZQUIERDA */}
                <div className="flex flex-col sm:flex-row flex-wrap gap-4 flex-1">
                    {/* Buscar */}
                    <div className="flex-1 min-w-[220px]">
                        <label className="block text-sm font-semibold text-slate-700 mb-2">
                            Buscar cliente
                        </label>

                        <div className="relative">
                            <i className="mgc_search_3_line absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>

                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={handleKeyPress}
                                className="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-slate-700 placeholder-slate-400"
                                placeholder="Nombre, correo o documento..."
                            />
                        </div>
                    </div>

                    {/* Estado */}
                    <div className="w-full sm:w-56">
                        <label className="block text-sm font-semibold text-slate-700 mb-2">
                            Estado
                        </label>

                        <select
                            value={filtroActivo}
                            onChange={(e) => setFiltroActivo(e.target.value)}
                            className="w-full py-3 px-4 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-slate-700"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>

                {/* DERECHA */}
                <div className="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <button
                        onClick={onLimpiarFiltros}
                        className="w-full sm:w-auto px-5 py-3 rounded-xl bg-white border border-slate-300 text-slate-600 font-medium hover:bg-slate-100 hover:text-slate-900 transition-all shadow-sm"
                    >
                        Limpiar
                    </button>

                    <button
                        onClick={onAplicarFiltros}
                        className="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                    >
                        Filtrar
                    </button>
                </div>
            </div>
        </div>
    );
}
