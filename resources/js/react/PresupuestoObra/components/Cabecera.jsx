import React, { useState } from "react";
import { formatEuro, formatPorcentaje } from "../utils/calculos";

export default function Cabecera({
    obraNombre,
    modo,
    totalGlobal,
    totalCoste = 0,
    totalVenta = 0,
    margenImporte = 0,
    margenPorcentaje = null,
    indicador = null,
    urlRegresar,
    onNuevoCapitulo = null,
    pendientesSincronizar = false,
    onSincronizar = null,
    onIncrementarGlobal = null,
    onDescargarPdf = null,
}) {
    const [showIncremento, setShowIncremento] = useState(false);
    const [porcentaje, setPorcentaje] = useState("");

    const handleAplicar = () => {
        if (!porcentaje || isNaN(porcentaje)) return;
        onIncrementarGlobal(parseFloat(porcentaje));
        setShowIncremento(false);
        setPorcentaje("");
    };

    const esVenta = modo === "venta";
    const titulo = esVenta ? "Presupuesto de venta" : "Coste teórico";
    const subtituloModo = esVenta
        ? "Precio al cliente"
        : "Coste estimado de ejecución";
    const colorMargen =
        margenImporte > 0
            ? "text-emerald-600"
            : margenImporte < 0
              ? "text-red-600"
              : "text-slate-500";

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <a
                    href={urlRegresar}
                    className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800"
                >
                    <i className="mgc_arrow_left_line text-lg"></i>
                    Regresar
                </a>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-cyan-50/40 p-5 shadow-sm sm:p-6">
                <div className="flex flex-col gap-2">
                    <div className="inline-flex w-fit items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-cyan-700">
                        <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                        {subtituloModo}
                    </div>
                    <h2 className="text-2xl font-bold tracking-tight text-slate-900">
                        {titulo}
                    </h2>
                    <p className="text-sm font-medium text-slate-500">
                        {obraNombre}
                    </p>

                    {indicador && !esVenta && indicador.total_coste > 0 && (
                        <div className="mt-3 inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
                            <i className="mgc_arrow_right_line"></i>
                            <span>
                                <strong className="font-semibold text-slate-800">
                                    {indicador.importadas_a_venta}
                                </strong>{" "}
                                de{" "}
                                <strong className="font-semibold text-slate-800">
                                    {indicador.total_coste}
                                </strong>{" "}
                                partidas de coste importadas a venta
                            </span>
                        </div>
                    )}

                    {indicador && esVenta && indicador.total_venta > 0 && (
                        <div className="mt-3 inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600">
                            <i className="mgc_link_2_line"></i>
                            <span>
                                <strong className="font-semibold text-slate-800">
                                    {indicador.vinculadas_a_coste}
                                </strong>{" "}
                                de{" "}
                                <strong className="font-semibold text-slate-800">
                                    {indicador.total_venta}
                                </strong>{" "}
                                partidas con vínculo a coste
                            </span>
                        </div>
                    )}
                </div>
            </div>

            {onNuevoCapitulo && (
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="text-sm text-slate-500">
                        Organiza la obra por capítulos y mantén una estructura clara.
                    </div>

                    <button
                        onClick={onNuevoCapitulo}
                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:from-cyan-600 hover:to-blue-700"
                    >
                        <i className="mgc_add_line text-base"></i>
                        Nuevo capítulo
                    </button>
                </div>
            )}

            {esVenta && (onSincronizar || onIncrementarGlobal || onDescargarPdf) && (
                <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-1">
                            <h3 className="text-sm font-semibold text-slate-800">
                                Acciones globales
                            </h3>
                            <p className="text-sm text-slate-500">
                                Sincroniza costes o aplica incrementos a toda la obra.
                            </p>
                        </div>

                        <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            {pendientesSincronizar && onSincronizar && (
                                <button
                                    onClick={onSincronizar}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-amber-600"
                                >
                                    <i className="mgc_refresh_2_line text-base"></i>
                                    Importar desde coste
                                </button>
                            )}

                            {onDescargarPdf && (
                                <button
                                    onClick={onDescargarPdf}
                                    className="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                                >
                                    <i className="mgc_download_line"></i>
                                    Descargar PDF
                                </button>
                            )}

                            {showIncremento ? (
                                <div className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center">
                                    <span className="text-sm font-medium text-slate-600">
                                        Aplicar %:
                                    </span>
                                    <input
                                        type="number"
                                        value={porcentaje}
                                        onChange={(e) =>
                                            setPorcentaje(e.target.value)
                                        }
                                        placeholder="% (ej: 10)"
                                        className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-right text-slate-700 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100 sm:w-32"
                                        autoFocus
                                        onKeyDown={(e) =>
                                            e.key === "Enter" && handleAplicar()
                                        }
                                    />
                                    <div className="flex items-center gap-2">
                                        <button
                                            onClick={handleAplicar}
                                            className="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:from-cyan-600 hover:to-blue-700"
                                        >
                                            Aplicar
                                        </button>
                                        <button
                                            onClick={() => {
                                                setShowIncremento(false);
                                                setPorcentaje("");
                                            }}
                                            className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-800"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            ) : (
                                onIncrementarGlobal && (
                                    <button
                                        onClick={() => setShowIncremento(true)}
                                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800"
                                    >
                                        <i className="mgc_arrow_up_line text-base"></i>
                                        Aplicar % de margen
                                    </button>
                                )
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* RESUMEN */}
            {esVenta ? (
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Coste total
                        </p>
                        <p className="mt-1 text-xl font-bold text-slate-700">
                            {formatEuro(totalCoste)}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Venta total
                        </p>
                        <p className="mt-1 text-xl font-bold text-cyan-600">
                            {formatEuro(totalVenta)}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Margen
                        </p>
                        <p className={`mt-1 text-xl font-bold ${colorMargen}`}>
                            {formatEuro(margenImporte)}
                            {margenPorcentaje !== null && (
                                <span className="ml-2 text-sm font-semibold">
                                    ({formatPorcentaje(margenPorcentaje)})
                                </span>
                            )}
                        </p>
                    </div>
                </div>
            ) : (
                <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                Resumen
                            </p>
                            <p className="mt-1 text-sm text-slate-500">
                                Coste total estimado de la obra
                            </p>
                        </div>
                        <div className="inline-flex items-center gap-3 self-start rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 px-4 py-3 text-white shadow-lg shadow-slate-900/10">
                            <span className="text-xs font-medium uppercase tracking-[0.12em] text-slate-300">
                                Total coste
                            </span>
                            <span className="text-xl font-bold tracking-tight text-cyan-300">
                                {formatEuro(totalGlobal)}
                            </span>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
