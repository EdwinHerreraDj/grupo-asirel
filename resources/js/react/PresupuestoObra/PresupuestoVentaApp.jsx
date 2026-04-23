import React, { useState } from "react";
import usePresupuesto from "./hooks/usePresupuesto";
import Cabecera from "./components/Cabecera";
import CapituloRow from "./components/CapituloRow";
import ModalConfirmar from "./components/ModalConfirmar";
import ModalPdf from "./components/ModalPdf";
import { formatEuro } from "./utils/calculos";
import {
    NotificationProvider,
    useNotification,
} from "../shared/NotificationContext";

const MODO = "venta";

export function PresupuestoVentaInner({ obraId, obraNombre, urlRegresar }) {
    const { showSuccess, showError } = useNotification();

    const {
        capitulos,
        capituloAbierto,
        toggleCapitulo,
        loading,
        error,
        totalVenta,
        totalCoste,
        margenImporte,
        margenPorcentaje,
        indicador,
        crearPartida,
        editarPartida,
        eliminarPartida,
        pendientesSincronizar,
        sincronizar,
        incrementar,
        restablecer,
    } = usePresupuesto(obraId, MODO);

    const [modalPdf, setModalPdf] = useState(false);

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20 text-gray-500 text-sm">
                <i className="mgc_loading_line animate-spin mr-2"></i>
                Cargando presupuesto de venta...
            </div>
        );
    }

    if (error) {
        return (
            <div className="p-6 text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg">
                {error}
            </div>
        );
    }

    return (
        <div>
            <div className="card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="p-4 sm:p-6 lg:p-8">
                    <Cabecera
                        obraNombre={obraNombre}
                        modo={MODO}
                        totalGlobal={totalVenta}
                        totalCoste={totalCoste}
                        totalVenta={totalVenta}
                        margenImporte={margenImporte}
                        margenPorcentaje={margenPorcentaje}
                        indicador={indicador}
                        urlRegresar={urlRegresar}
                        pendientesSincronizar={pendientesSincronizar}
                        onSincronizar={async () => {
                            try {
                                await sincronizar();
                                showSuccess("Partidas importadas desde coste.");
                            } catch {
                                showError("Error al importar desde coste.");
                            }
                        }}
                        onIncrementarGlobal={async (pct) => {
                            try {
                                await incrementar(pct);
                                showSuccess(
                                    `Margen del ${pct}% aplicado a toda la obra.`,
                                );
                            } catch {
                                showError("Error al aplicar el margen.");
                            }
                        }}
                        onDescargarPdf={() => setModalPdf(true)}
                    />
                </div>

                <div className="px-4 pb-4 sm:px-6 sm:pb-6 lg:px-8 lg:pb-8">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm text-slate-700">
                                <thead className="bg-slate-50/80 text-slate-600">
                                    <tr className="border-b border-slate-200">
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">
                                            Capítulo / Oficio
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">
                                            Coste
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                            Venta
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                            Margen
                                        </th>
                                        <th className="w-28 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide sm:px-5">
                                            Partidas
                                        </th>
                                        <th className="w-32 px-4 py-3 sm:px-5"></th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {capitulos.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan={6}
                                                className="px-4 py-12 text-center text-sm italic text-slate-400 sm:px-5"
                                            >
                                                No hay capítulos configurados para esta obra. Define los oficios desde "Coste teórico".
                                            </td>
                                        </tr>
                                    ) : (
                                        capitulos.map((capitulo) => (
                                            <CapituloRow
                                                key={capitulo.oficio_id}
                                                capitulo={capitulo}
                                                modo={MODO}
                                                abierto={
                                                    capituloAbierto ===
                                                    capitulo.oficio_id
                                                }
                                                onToggle={() =>
                                                    toggleCapitulo(capitulo.oficio_id)
                                                }
                                                onCrearPartida={(datos) =>
                                                    crearPartida(capitulo.oficio_id, datos)
                                                }
                                                onEditarPartida={editarPartida}
                                                onEliminarPartida={(partidaId) =>
                                                    eliminarPartida(partidaId, capitulo.oficio_id)
                                                }
                                                onIncrementar={(pct, oficioId) =>
                                                    incrementar(pct, oficioId)
                                                }
                                                onRestablecer={async (oficioId) => {
                                                    try {
                                                        await restablecer(oficioId);
                                                        showSuccess(
                                                            "Precios restablecidos al coste original.",
                                                        );
                                                    } catch {
                                                        showError(
                                                            "Error al restablecer los precios.",
                                                        );
                                                    }
                                                }}
                                            />
                                        ))
                                    )}
                                </tbody>

                                <tfoot className="bg-slate-50">
                                    <tr className="border-t border-slate-200">
                                        <td className="px-4 py-4 text-sm font-semibold text-slate-700 sm:px-5">
                                            Total
                                        </td>
                                        <td className="px-4 py-4 text-right text-sm font-semibold text-slate-600 sm:px-5">
                                            {formatEuro(totalCoste)}
                                        </td>
                                        <td className="px-4 py-4 text-right text-base font-bold text-cyan-600 sm:px-5">
                                            {formatEuro(totalVenta)}
                                        </td>
                                        <td className="px-4 py-4 text-right text-sm font-semibold sm:px-5">
                                            <span
                                                className={
                                                    margenImporte > 0
                                                        ? "text-emerald-600"
                                                        : margenImporte < 0
                                                          ? "text-red-600"
                                                          : "text-slate-500"
                                                }
                                            >
                                                {formatEuro(margenImporte)}
                                            </span>
                                        </td>
                                        <td colSpan={2}></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {modalPdf && (
                <ModalPdf
                    obraId={obraId}
                    onCancelar={() => setModalPdf(false)}
                />
            )}
        </div>
    );
}

export default function PresupuestoVentaApp() {
    const el = document.getElementById("react-presupuesto-venta");
    const obraId = el?.dataset?.obraId;
    const obraNombre = el?.dataset?.obraNombre;
    const urlRegresar = el?.dataset?.urlRegresar ?? "/";

    return (
        <NotificationProvider>
            <PresupuestoVentaInner
                obraId={obraId}
                obraNombre={obraNombre}
                urlRegresar={urlRegresar}
            />
        </NotificationProvider>
    );
}
