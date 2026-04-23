import React from "react";
import { formatEuro, formatNumero } from "../utils/calculos";

export default function PartidaFila({ partida, onEditar, onEliminar }) {
    return (
        <tr className="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
            <td className="px-4 py-3 text-xs font-medium text-slate-500 sm:px-5">
                {partida.codigo || "—"}
            </td>

            <td className="px-4 py-3 sm:px-5">
                <div className="text-sm font-medium text-slate-800">
                    {partida.descripcion}
                </div>
            </td>

            <td className="px-4 py-3 text-center text-sm text-slate-600 sm:px-5">
                {partida.unidad || "—"}
            </td>

            <td className="px-4 py-3 text-right text-sm text-slate-700 sm:px-5">
                {formatNumero(partida.medicion)}
            </td>

            <td className="px-4 py-3 text-right text-sm text-slate-700 sm:px-5">
                {formatEuro(partida.precio_unitario)}
            </td>

            <td className="px-4 py-3 text-right text-sm font-semibold text-slate-900 sm:px-5">
                {formatEuro(partida.importe)}
            </td>

            <td className="px-4 py-3 sm:px-5">
                <div className="flex justify-end gap-2">
                    <button
                        onClick={onEditar}
                        className="inline-flex items-center rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-100"
                    >
                        Editar
                    </button>
                    <button
                        onClick={onEliminar}
                        className="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                    >
                        Eliminar
                    </button>
                </div>
            </td>
        </tr>
    );
}
