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
        <div className="mb-6 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {/* Izquierda: Grupo de filtros */}
                <div className="flex flex-col md:flex-row gap-4 flex-1">
                    {/* Buscar */}
                    <div className="flex-1">
                        <div className="relative">
                            <i className="mgc_search_3_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={handleKeyPress}
                                className="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:ring-primary focus:border-primary shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Buscar cliente..."
                            />
                        </div>
                    </div>

                    {/* Estado */}
                    <div>
                        <select
                            value={filtroActivo}
                            onChange={(e) => setFiltroActivo(e.target.value)}
                            className="w-full md:w-48 py-2.5 rounded-xl border-gray-300 focus:ring-primary focus:border-primary shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">Estado</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>

                {/* Derecha: Botones */}
                <div className="flex gap-2 md:gap-3">
                    <button
                        onClick={onLimpiarFiltros}
                        className="px-4 py-2.5 rounded-xl bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition shadow-sm"
                    >
                        Limpiar
                    </button>

                    <button
                        onClick={onAplicarFiltros}
                        className="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-md hover:bg-primary/90 transition"
                    >
                        Filtrar
                    </button>
                </div>
            </div>
        </div>
    );
}
