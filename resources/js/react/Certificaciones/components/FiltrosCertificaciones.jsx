import React from "react";

export default function FiltrosCertificaciones({
    pending,
    setPending,
    oficios,
    clientes,
    onAplicar,
    onLimpiar,
}) {
    const set = (campo, valor) =>
        setPending((prev) => ({ ...prev, [campo]: valor }));

    return (
        <div className="mb-6 overflow-hidden rounded-3xl border border-slate-200/80  shadow-[0_8px_30px_rgba(15,23,42,0.05)]">
            <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-4 py-4 sm:px-5">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                            <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                            Filtros
                        </div>
                        <h3 className="mt-3 text-base font-semibold text-slate-900 sm:text-lg">
                            Buscar certificaciones
                        </h3>
                        <p className="mt-1 text-sm text-slate-500">
                            Refina el listado por número, oficio, cliente,
                            estado o rango de fechas.
                        </p>
                    </div>
                </div>
            </div>

            <div className="px-4 py-4 sm:px-5 sm:py-5">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {/* BUSCADOR */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Nº certificación
                        </label>
                        <div className="relative">
                            <span className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i className="mgc_search_2_line text-base"></i>
                            </span>
                            <input
                                type="text"
                                value={pending.search}
                                onChange={(e) => set("search", e.target.value)}
                                placeholder="Buscar por número..."
                                className="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                                onKeyDown={(e) =>
                                    e.key === "Enter" && onAplicar()
                                }
                            />
                        </div>
                    </div>

                    {/* OFICIO */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Oficio
                        </label>
                        <select
                            value={pending.oficio_id}
                            onChange={(e) => set("oficio_id", e.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                        >
                            <option value="">Todos los oficios</option>
                            {oficios.map((o) => (
                                <option key={o.id} value={o.id}>
                                    {o.nombre}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* CLIENTE */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Cliente
                        </label>
                        <select
                            value={pending.cliente_id}
                            onChange={(e) => set("cliente_id", e.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                        >
                            <option value="">Todos los clientes</option>
                            {clientes.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.nombre}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* ESTADO CERTIFICACIÓN */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Estado
                        </label>
                        <select
                            value={pending.estado_certificacion}
                            onChange={(e) =>
                                set("estado_certificacion", e.target.value)
                            }
                            className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                        >
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aceptada">Aceptada</option>
                        </select>
                    </div>

                    {/* FECHA DESDE */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Fecha desde
                        </label>
                        <input
                            type="date"
                            value={pending.fecha_desde}
                            onChange={(e) => set("fecha_desde", e.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                        />
                    </div>

                    {/* FECHA HASTA */}
                    <div className="space-y-1.5">
                        <label className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                            Fecha hasta
                        </label>
                        <input
                            type="date"
                            value={pending.fecha_hasta}
                            onChange={(e) => set("fecha_hasta", e.target.value)}
                            className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"
                        />
                    </div>
                </div>

                <div className="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        onClick={onLimpiar}
                        className="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        Limpiar
                    </button>
                    <button
                        onClick={onAplicar}
                        className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500"
                    >
                        <i className="mgc_search_2_line text-base"></i>
                        Aplicar filtros
                    </button>
                </div>
            </div>
        </div>
    );
}
