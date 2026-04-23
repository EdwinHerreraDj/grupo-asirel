import React, { useState, useEffect } from "react";
import api from "../../shared/api";
import { formatEuro, formatNumero } from "../utils/formato";

export default function ComparativaMensual({ obraId }) {
    const [periodo, setPeriodo] = useState(
        new Date().toISOString().slice(0, 7),
    );
    const [tmpPeriodo, setTmp] = useState(periodo);
    const [filas, setFilas] = useState([]);
    const [loading, setLoading] = useState(true);
    const [descargando, setDesc] = useState(false);

    const cargar = async (p) => {
        setLoading(true);
        try {
            const { data } = await api.get(
                `/obras/${obraId}/comparativa-mensual`,
                {
                    params: { periodo: p },
                },
            );
            setFilas(data.filas ?? []);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        cargar(periodo);
    }, [periodo]);

    const handleDescargar = async () => {
        setDesc(true);
        try {
            const response = await api.get(
                `/obras/${obraId}/comparativa-mensual/pdf`,
                { params: { periodo }, responseType: "blob" },
            );
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", `comparativa-${periodo}.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
        } finally {
            setDesc(false);
        }
    };

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-violet-50/40 px-4 py-4 sm:px-5">
                <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-violet-100 bg-violet-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-violet-700">
                            <span className="h-2 w-2 rounded-full bg-violet-500"></span>
                            Comparativa
                        </div>
                        <h3 className="mt-3 text-base font-semibold text-slate-900 sm:text-lg">
                            Comparativa mensual
                        </h3>
                        <p className="mt-1 text-sm text-slate-500">
                            Revisa el avance por oficio y exporta el detalle del
                            periodo seleccionado.
                        </p>
                    </div>

                    <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        <input
                            type="month"
                            value={tmpPeriodo}
                            onChange={(e) => setTmp(e.target.value)}
                            className="h-11 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20"
                        />

                        <button
                            onClick={() => setPeriodo(tmpPeriodo)}
                            className="inline-flex h-11 items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-4 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(99,102,241,0.22)] transition hover:from-violet-500 hover:to-indigo-500"
                        >
                            Aplicar
                        </button>

                        <button
                            onClick={handleDescargar}
                            disabled={descargando}
                            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <i className="mgc_download_line text-base"></i>
                            {descargando ? "..." : "PDF"}
                        </button>
                    </div>
                </div>
            </div>

            {loading ? (
                <div className="px-4 py-14 sm:px-5">
                    <div className="flex flex-col items-center justify-center gap-3 text-center">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                            <i className="mgc_loading_line animate-spin text-xl"></i>
                        </div>
                        <div>
                            <p className="font-medium text-slate-700">
                                Cargando comparativa
                            </p>
                            <p className="mt-1 text-sm text-slate-500">
                                Estamos preparando los datos del periodo
                                seleccionado.
                            </p>
                        </div>
                    </div>
                </div>
            ) : (
                <div className="overflow-x-auto">
                    <table className="min-w-full text-xs sm:text-sm">
                        <thead className="bg-slate-50/80 text-slate-600">
                            <tr className="border-b border-slate-200">
                                <th className="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Oficio
                                </th>
                                <th className="px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Ud.
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Contrato €
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Origen ant. €
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Mes €
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    A origen €
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Pendiente €
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Imp. mes €
                                </th>
                                <th className="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-4">
                                    Imp. origen €
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-200">
                            {filas.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={9}
                                        className="px-4 py-14 text-center text-sm text-slate-500"
                                    >
                                        <div className="flex flex-col items-center justify-center gap-3">
                                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                <i className="mgc_chart_line text-xl"></i>
                                            </div>
                                            <div>
                                                <p className="font-medium text-slate-700">
                                                    Sin datos disponibles
                                                </p>
                                                <p className="mt-1 text-sm text-slate-500">
                                                    No hay datos para este
                                                    periodo.
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            ) : (
                                filas.map((fila, i) => (
                                    <tr
                                        key={i}
                                        className="transition-colors hover:bg-slate-50/70"
                                    >
                                        <td className="px-3 py-3 sm:px-4">
                                            <div className="max-w-[220px] truncate font-medium text-slate-800">
                                                {fila.oficio}
                                            </div>
                                        </td>

                                        <td className="px-3 py-3 text-center text-slate-500 sm:px-4">
                                            {fila.unidad || "—"}
                                        </td>

                                        <td className="px-3 py-3 text-right text-slate-700 sm:px-4">
                                            {formatNumero(fila.contrato)}
                                        </td>

                                        <td className="px-3 py-3 text-right text-slate-700 sm:px-4">
                                            {formatNumero(fila.origen_anterior)}
                                        </td>

                                        <td className="px-3 py-3 text-right sm:px-4">
                                            <span className="font-semibold text-slate-900">
                                                {formatNumero(fila.mes)}
                                            </span>
                                        </td>

                                        <td className="px-3 py-3 text-right text-slate-700 sm:px-4">
                                            {formatNumero(fila.a_origen)}
                                        </td>

                                        <td
                                            className={`px-3 py-3 text-right font-medium sm:px-4 ${
                                                fila.pendiente < 0
                                                    ? "text-red-600"
                                                    : "text-slate-700"
                                            }`}
                                        >
                                            {formatNumero(fila.pendiente)}
                                        </td>

                                        <td className="px-3 py-3 text-right text-slate-700 sm:px-4">
                                            {formatEuro(fila.importe_mes)}
                                        </td>

                                        <td className="px-3 py-3 text-right sm:px-4">
                                            <span className="font-semibold text-slate-900">
                                                {formatEuro(fila.importe_origen)}
                                            </span>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}
