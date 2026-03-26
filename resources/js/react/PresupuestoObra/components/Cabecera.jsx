import React, { useState } from "react";
import { formatEuro } from "../utils/calculos";

export default function Cabecera({
    obraNombre,
    modo,
    onToggleModo,
    totalGlobal,
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
                <div className="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div className="min-w-0 space-y-2">
                        <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.12em] text-cyan-700">
                            <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                            {modo === "venta" ? "Modo venta" : "Modo coste"}
                        </div>

                        <div>
                            <h2 className="text-2xl font-bold tracking-tight text-slate-900">
                                {modo === "venta"
                                    ? "Presupuesto de venta"
                                    : "Coste teórico"}
                            </h2>
                            <p className="mt-1 text-sm font-medium text-slate-500">
                                {obraNombre}
                            </p>
                        </div>
                    </div>

                    <div className="w-full xl:w-auto">
                        <div className="inline-flex w-full flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm sm:w-auto sm:flex-row">
                            <button
                                onClick={() => onToggleModo("venta")}
                                className={`inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200 ${
                                    modo === "venta"
                                        ? "bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-md shadow-cyan-500/20"
                                        : "text-slate-500 hover:bg-slate-50 hover:text-slate-800"
                                }`}
                            >
                                <i className="mgc_bill_line text-base"></i>
                                Presupuesto de venta
                            </button>

                            <button
                                onClick={() => onToggleModo("coste")}
                                className={`inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-all duration-200 ${
                                    modo === "coste"
                                        ? "bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-md shadow-cyan-500/20"
                                        : "text-slate-500 hover:bg-slate-50 hover:text-slate-800"
                                }`}
                            >
                                <i className="mgc_chart_line_line text-base"></i>
                                Coste teórico
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {onNuevoCapitulo && (
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="text-sm text-slate-500">
                        Organiza la obra por capítulos y mantén una estructura
                        clara.
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

            {modo === "venta" && (
                <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-1">
                            <h3 className="text-sm font-semibold text-slate-800">
                                Acciones globales
                            </h3>
                            <p className="text-sm text-slate-500">
                                Sincroniza costes o aplica incrementos a toda la
                                obra.
                            </p>
                        </div>

                        <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            {pendientesSincronizar && onSincronizar && (
                                <button
                                    onClick={onSincronizar}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-amber-600"
                                >
                                    <i className="mgc_refresh_2_line text-base"></i>
                                    Sincronizar desde coste
                                </button>
                            )}

                            {onDescargarPdf && (
                                <button
                                    onClick={onDescargarPdf}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800 shadow-sm transition"
                                >
                                    <i className="mgc_download_line"></i>
                                    Descargar PDF
                                </button>
                            )}

                            {showIncremento ? (
                                <div className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center">
                                    <span className="text-sm font-medium text-slate-600">
                                        Incremento global:
                                    </span>

                                    <div className="relative">
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
                                                e.key === "Enter" &&
                                                handleAplicar()
                                            }
                                        />
                                    </div>

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
                                        Incremento global
                                    </button>
                                )
                            )}
                        </div>
                    </div>
                </div>
            )}

            <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                            Resumen global
                        </p>
                        <p className="mt-1 text-sm text-slate-500">
                            Total{" "}
                            {modo === "venta"
                                ? "presupuestado"
                                : "coste estimado"}
                        </p>
                    </div>

                    <div className="inline-flex items-center gap-3 self-start rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 px-4 py-3 text-white shadow-lg shadow-slate-900/10">
                        <span className="text-xs font-medium uppercase tracking-[0.12em] text-slate-300">
                            Total
                        </span>
                        <span className="text-xl font-bold tracking-tight text-cyan-300">
                            {formatEuro(totalGlobal)}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
