import React, { useState } from "react";
import PartidaFila from "./PartidaFila";
import PartidaFilaEdicion from "./PartidaFilaEdicion";
import ModalConfirmar from "./ModalConfirmar";
import { totalPartidas, formatEuro } from "../utils/calculos";
import { useNotification } from "../../shared/NotificationContext";

const FORM_VACIO = {
    codigo: "",
    descripcion: "",
    unidad: "",
    medicion: "",
    precio_unitario: "",
};

export default function PartidasTable({
    partidas,
    modo,
    capituloId,
    oficioId,
    onCrear,
    onEditar,
    onEliminar,
}) {
    const { showSuccess, showError } = useNotification();

    const [editandoId, setEditandoId] = useState(null);
    const [creandoNueva, setCreandoNueva] = useState(false);
    const [guardando, setGuardando] = useState(false);

    // Modal eliminar
    const [modalEliminar, setModalEliminar] = useState(false);
    const [partidaAEliminar, setPartidaAEliminar] = useState(null);

    const total = totalPartidas(partidas);

    const handleGuardarNueva = async (datos) => {
        setGuardando(true);
        try {
            await onCrear(datos);
            setCreandoNueva(false);
            showSuccess("Partida creada correctamente.");
        } catch (err) {
            showError("Error al crear la partida.");
        } finally {
            setGuardando(false);
        }
    };

    const handleGuardarEdicion = async (partidaId, datos) => {
        setGuardando(true);
        try {
            await onEditar(partidaId, datos);
            setEditandoId(null);
            showSuccess("Partida actualizada correctamente.");
        } catch (err) {
            showError("Error al actualizar la partida.");
        } finally {
            setGuardando(false);
        }
    };

    const pedirConfirmacionEliminar = (partida) => {
        setPartidaAEliminar(partida);
        setModalEliminar(true);
    };

    const handleConfirmarEliminar = async () => {
        try {
            await onEliminar(partidaAEliminar.id);
            showSuccess("Partida eliminada correctamente.");
        } catch (err) {
            showError("Error al eliminar la partida.");
        } finally {
            setModalEliminar(false);
            setPartidaAEliminar(null);
        }
    };

    return (
        <>
            <div className="space-y-4">
                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm text-slate-700">
                            <thead className="border-b border-slate-200 bg-slate-50/80 text-slate-600">
                                <tr>
                                    <th className="w-24 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">
                                        Código
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">
                                        Descripción
                                    </th>
                                    <th className="w-20 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide sm:px-5">
                                        Unidad
                                    </th>
                                    <th className="w-28 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                        Medición
                                    </th>
                                    <th className="w-32 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                        Precio unit.
                                    </th>
                                    <th className="w-32 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                        Importe
                                    </th>
                                    <th className="w-24 px-4 py-3 sm:px-5"></th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100 bg-white">
                                {partidas.length === 0 && !creandoNueva ? (
                                    <tr>
                                        <td
                                            colSpan={7}
                                            className="px-4 py-10 text-center text-xs italic text-slate-400 sm:px-5"
                                        >
                                            No hay partidas en este capítulo
                                            todavía.
                                        </td>
                                    </tr>
                                ) : (
                                    partidas.map((partida) =>
                                        editandoId === partida.id ? (
                                            <PartidaFilaEdicion
                                                key={partida.id}
                                                inicial={partida}
                                                guardando={guardando}
                                                modo={modo}
                                                onGuardar={(datos) =>
                                                    handleGuardarEdicion(
                                                        partida.id,
                                                        datos,
                                                    )
                                                }
                                                onCancelar={() =>
                                                    setEditandoId(null)
                                                }
                                            />
                                        ) : (
                                            <PartidaFila
                                                key={partida.id}
                                                partida={partida}
                                                onEditar={() =>
                                                    setEditandoId(partida.id)
                                                }
                                                onEliminar={() =>
                                                    pedirConfirmacionEliminar(
                                                        partida,
                                                    )
                                                }
                                            />
                                        ),
                                    )
                                )}

                                {creandoNueva && (
                                    <PartidaFilaEdicion
                                        key="nueva"
                                        inicial={FORM_VACIO}
                                        modo={modo}
                                        guardando={guardando}
                                        esNueva
                                        onGuardar={handleGuardarNueva}
                                        onCancelar={() =>
                                            setCreandoNueva(false)
                                        }
                                    />
                                )}
                            </tbody>

                            <tfoot className="bg-slate-50">
                                <tr className="border-t border-slate-200">
                                    <td
                                        colSpan={5}
                                        className="px-4 py-4 text-right text-sm font-semibold text-slate-700 sm:px-5"
                                    >
                                        Total capítulo
                                    </td>
                                    <td className="px-4 py-4 text-right text-base font-bold text-cyan-600 sm:px-5">
                                        {formatEuro(total)}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {!creandoNueva && editandoId === null && (
                    <div className="flex justify-end">
                        <button
                            onClick={() => setCreandoNueva(true)}
                            className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-cyan-500/20 transition-all duration-200 hover:-translate-y-0.5 hover:from-cyan-600 hover:to-blue-700"
                        >
                            <i className="mgc_add_line text-base"></i>
                            Nueva partida
                        </button>
                    </div>
                )}
            </div>

            {modalEliminar && (
                <ModalConfirmar
                    titulo="Eliminar partida"
                    mensaje={`¿Estás seguro de que deseas eliminar "${partidaAEliminar?.descripcion}"? Esta acción no se puede deshacer.`}
                    textoConfirmar="Eliminar"
                    onConfirmar={handleConfirmarEliminar}
                    onCancelar={() => {
                        setModalEliminar(false);
                        setPartidaAEliminar(null);
                    }}
                />
            )}
        </>
    );
}
