import React, { useState } from "react";
import {
    formatEuro,
    formatPorcentaje,
    calcularMargen,
    calcularMargenPct,
} from "../utils/calculos";
import PartidasTable from "./PartidasTable";

export default function CapituloRow({
    capitulo,
    modo,
    abierto,
    onToggle,
    onCrearPartida,
    onEditarPartida,
    onEliminarPartida,
    onEditarCapitulo,
    onEliminarCapitulo,
    onIncrementar,
    onRestablecer,
}) {
    const [showIncremento, setShowIncremento] = useState(false);
    const [porcentaje, setPorcentaje] = useState("");
    const [aplicando, setAplicando] = useState(false);

    const total = parseFloat(capitulo.importe_total) || 0;
    const coste = parseFloat(capitulo.coste_total) || 0;
    const margen = calcularMargen(total, coste);
    const margenPct = calcularMargenPct(total, coste);
    const numPartidas = capitulo.partidas?.length ?? 0;

    const handleAplicarIncremento = async () => {
        if (!porcentaje || isNaN(porcentaje)) return;
        setAplicando(true);
        try {
            await onIncrementar(parseFloat(porcentaje), capitulo.oficio_id);
            setShowIncremento(false);
            setPorcentaje("");
        } finally {
            setAplicando(false);
        }
    };

    const colorMargen =
        margen > 0
            ? "text-emerald-600"
            : margen < 0
              ? "text-red-600"
              : "text-gray-500";

    return (
        <>
            {/* FILA CAPÍTULO */}
            <tr
                onClick={onToggle}
                className={`cursor-pointer border-t border-slate-200 transition-all duration-200 hover:bg-slate-50 ${
                    abierto ? "bg-cyan-50/60" : "bg-white"
                }`}
            >
                <td className="px-4 py-4 sm:px-5">
                    <div className="flex items-center gap-3">
                        <span
                            className={`inline-flex h-7 w-7 items-center justify-center rounded-full border text-[11px] transition-colors ${
                                abierto
                                    ? "border-cyan-200 bg-cyan-100 text-cyan-700"
                                    : "border-slate-200 bg-slate-100 text-slate-500"
                            }`}
                        >
                            <i
                                className={`mgc_${abierto ? "down" : "right"}_line`}
                            ></i>
                        </span>

                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold text-slate-800">
                                {capitulo.oficio_nombre}
                            </p>
                        </div>
                    </div>
                </td>

                {/* COMPARATIVA — solo en modo venta */}
                {modo === "venta" ? (
                    <>
                        <td className="px-4 py-4 text-right text-xs font-medium text-slate-500 sm:px-5">
                            {formatEuro(coste)}
                        </td>
                        <td className="px-4 py-4 text-right text-sm font-semibold text-slate-800 sm:px-5">
                            {formatEuro(total)}
                        </td>
                        <td
                            className={`px-4 py-4 text-right text-xs font-semibold sm:px-5 ${colorMargen}`}
                        >
                            <span className="block text-sm font-semibold">
                                {margenPct !== null
                                    ? formatPorcentaje(margenPct)
                                    : "—"}
                            </span>
                            <span
                                className={`mt-0.5 block text-xs font-medium ${colorMargen}`}
                            >
                                {formatEuro(margen)}
                            </span>
                        </td>
                    </>
                ) : (
                    <>
                        <td className="px-4 py-4 text-right text-sm font-semibold text-slate-800 sm:px-5">
                            {formatEuro(total)}
                        </td>
                        <td className="px-4 py-4 sm:px-5"></td>
                        <td className="px-4 py-4 sm:px-5"></td>
                    </>
                )}

                <td className="px-4 py-4 text-center sm:px-5">
                    <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-500">
                        {numPartidas} partida{numPartidas !== 1 ? "s" : ""}
                    </span>
                </td>

                <td
                    className="px-4 py-4 text-right sm:px-5"
                    onClick={(e) => e.stopPropagation()}
                >
                    {modo === "coste" ? (
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => onEditarCapitulo(capitulo)}
                                className="inline-flex items-center rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100"
                            >
                                Editar
                            </button>
                            <button
                                onClick={() => onEliminarCapitulo(capitulo)}
                                className="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                            >
                                Eliminar
                            </button>
                        </div>
                    ) : (
                        <span
                            className={`inline-flex h-8 w-8 items-center justify-center rounded-full border transition-colors ${
                                abierto
                                    ? "border-cyan-200 bg-cyan-100 text-cyan-700"
                                    : "border-slate-200 bg-white text-slate-400"
                            }`}
                        >
                            <i
                                className={`mgc_${abierto ? "up" : "down"}_line`}
                            ></i>
                        </span>
                    )}
                </td>
            </tr>

            {/* FILA PARTIDAS */}
            {abierto && (
                <tr>
                    <td
                        colSpan={6}
                        className="border-t border-cyan-100 bg-gradient-to-b from-slate-50 to-white px-4 py-5 sm:px-5"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* BARRA DE INCREMENTO — solo en modo venta */}
                        {modo === "venta" && (
                            <div className="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                    <div className="space-y-1">
                                        <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                                            Resumen del capítulo
                                        </p>
                                        <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-600">
                                            <span>
                                                Coste:{" "}
                                                <span className="font-semibold text-slate-800">
                                                    {formatEuro(coste)}
                                                </span>
                                            </span>
                                            <span className="text-slate-300">
                                                →
                                            </span>
                                            <span>
                                                Venta:{" "}
                                                <span className="font-semibold text-slate-800">
                                                    {formatEuro(total)}
                                                </span>
                                            </span>
                                            {margenPct !== null && (
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${
                                                        colorMargen
                                                    } ${
                                                        margenPct >= 0
                                                            ? "bg-emerald-50"
                                                            : "bg-red-50"
                                                    }`}
                                                >
                                                    {margenPct > 0 ? "+" : ""}
                                                    {formatPorcentaje(margenPct)}
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:justify-end">
                                        <button
                                            onClick={() => {
                                                onRestablecer(
                                                    capitulo.oficio_id,
                                                );
                                            }}
                                            className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-800"
                                        >
                                            <i className="mgc_refresh_line"></i>
                                            Restablecer
                                        </button>

                                        {showIncremento ? (
                                            <div className="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center">
                                                <input
                                                    type="number"
                                                    value={porcentaje}
                                                    onChange={(e) =>
                                                        setPorcentaje(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="% (ej: 10)"
                                                    className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-right text-slate-700 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100 sm:w-32"
                                                    autoFocus
                                                />
                                                <button
                                                    onClick={
                                                        handleAplicarIncremento
                                                    }
                                                    disabled={aplicando}
                                                    className="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:from-cyan-600 hover:to-blue-700 disabled:opacity-50"
                                                >
                                                    {aplicando
                                                        ? "..."
                                                        : "Aplicar"}
                                                </button>
                                                <button
                                                    onClick={() => {
                                                        setShowIncremento(
                                                            false,
                                                        );
                                                        setPorcentaje("");
                                                    }}
                                                    className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                                                >
                                                    Cancelar
                                                </button>
                                            </div>
                                        ) : (
                                            <button
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    setShowIncremento(true);
                                                }}
                                                className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-800"
                                            >
                                                <i className="mgc_arrow_up_line"></i>
                                                Aplicar incremento
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <PartidasTable
                                partidas={capitulo.partidas ?? []}
                                modo={modo}
                                oficioId={capitulo.oficio_id}
                                onCrear={onCrearPartida}
                                onEditar={onEditarPartida}
                                onEliminar={onEliminarPartida}
                            />
                        </div>
                    </td>
                </tr>
            )}
        </>
    );
}
