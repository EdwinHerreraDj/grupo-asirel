import React from "react";
import { formatEuro } from "../../utils/formato";
import ModalConfirmar from "../../../PresupuestoObra/components/ModalConfirmar";
import { useState } from "react";

export default function TablaLineas({
    lineas,
    editable,
    onNuevaLinea,
    onEliminar,
}) {
    const [modalEliminar, setModalEliminar] = useState(false);
    const [lineaAEliminar, setLineaAEliminar] = useState(null);

    const total = lineas.reduce((acc, l) => acc + l.importe_linea, 0);

    return (
        <>
            <div className="space-y-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-5">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-blue-700">
                            <span className="h-2 w-2 rounded-full bg-blue-500"></span>
                            Líneas
                        </div>
                        <h4 className="mt-3 text-base font-semibold text-slate-900">
                            Líneas de certificación
                        </h4>
                        <p className="mt-1 text-sm text-slate-500">
                            Detalle económico de conceptos incluidos en la
                            certificación.
                        </p>
                    </div>

                    {editable && (
                        <button
                            onClick={onNuevaLinea}
                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500"
                        >
                            <i className="mgc_add_line text-base"></i>
                            Añadir línea
                        </button>
                    )}
                </div>

                <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-slate-50/80 text-slate-600">
                                <tr className="border-b border-slate-200">
                                    <th className="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-5">
                                        Concepto
                                    </th>
                                    <th className="w-20 px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-5">
                                        Unidad
                                    </th>
                                    <th className="w-28 px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-5">
                                        Cantidad
                                    </th>
                                    <th className="w-32 px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-5">
                                        Precio unit.
                                    </th>
                                    <th className="w-32 px-4 py-3.5 text-right text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-5">
                                        Importe
                                    </th>
                                    {editable && (
                                        <th className="w-16 px-4 py-3.5 sm:px-5"></th>
                                    )}
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-200">
                                {lineas.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={editable ? 6 : 5}
                                            className="px-4 py-14 text-center text-sm text-slate-500 sm:px-5"
                                        >
                                            <div className="flex flex-col items-center justify-center gap-3">
                                                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                    <i className="mgc_inbox_line text-xl"></i>
                                                </div>
                                                <div>
                                                    <p className="font-medium text-slate-700">
                                                        No hay líneas
                                                        registradas
                                                    </p>
                                                    <p className="mt-1 text-sm text-slate-500">
                                                        {editable
                                                            ? "Añade la primera línea para comenzar."
                                                            : "No hay líneas disponibles en esta certificación."}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    lineas.map((linea) => (
                                        <tr
                                            key={linea.id}
                                            className="transition-colors hover:bg-slate-50/70"
                                        >
                                            <td className="px-4 py-3.5 sm:px-5">
                                                <div className="max-w-[360px] text-sm font-medium text-slate-800">
                                                    {linea.concepto}
                                                </div>
                                            </td>

                                            <td className="px-4 py-3.5 text-center text-slate-600 sm:px-5">
                                                {linea.unidad || "—"}
                                            </td>

                                            <td className="px-4 py-3.5 text-right text-slate-700 sm:px-5">
                                                {new Intl.NumberFormat(
                                                    "es-ES",
                                                    {
                                                        minimumFractionDigits: 4,
                                                    },
                                                ).format(linea.cantidad)}
                                            </td>

                                            <td className="px-4 py-3.5 text-right text-slate-700 sm:px-5">
                                                {formatEuro(
                                                    linea.precio_unitario,
                                                )}
                                            </td>

                                            <td className="px-4 py-3.5 text-right sm:px-5">
                                                <span className="font-semibold text-slate-900">
                                                    {formatEuro(
                                                        linea.importe_linea,
                                                    )}
                                                </span>
                                            </td>

                                            {editable && (
                                                <td className="px-4 py-3.5 text-right sm:px-5">
                                                    <button
                                                        onClick={() => {
                                                            setLineaAEliminar(
                                                                linea,
                                                            );
                                                            setModalEliminar(
                                                                true,
                                                            );
                                                        }}
                                                        className="inline-flex items-center rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </td>
                                            )}
                                        </tr>
                                    ))
                                )}
                            </tbody>

                            <tfoot className="border-t-2 border-slate-200 bg-slate-50/60">
                                <tr>
                                    <td
                                        colSpan={editable ? 4 : 3}
                                        className="px-4 py-4 text-right text-sm font-semibold text-slate-700 sm:px-5"
                                    >
                                        Total líneas
                                    </td>
                                    <td className="px-4 py-4 text-right sm:px-5">
                                        <span className="text-base font-bold text-cyan-700">
                                            {formatEuro(total)}
                                        </span>
                                    </td>
                                    {editable && <td></td>}
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {modalEliminar && (
                <ModalConfirmar
                    titulo="Eliminar línea"
                    mensaje={`¿Eliminar la línea "${lineaAEliminar?.concepto}"?`}
                    textoConfirmar="Eliminar"
                    onConfirmar={async () => {
                        await onEliminar(lineaAEliminar.id);
                        setModalEliminar(false);
                        setLineaAEliminar(null);
                    }}
                    onCancelar={() => {
                        setModalEliminar(false);
                        setLineaAEliminar(null);
                    }}
                />
            )}
        </>
    );
}
