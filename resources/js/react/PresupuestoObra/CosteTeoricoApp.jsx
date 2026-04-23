import React, { useState } from "react";
import usePresupuesto from "./hooks/usePresupuesto";
import Cabecera from "./components/Cabecera";
import CapituloRow from "./components/CapituloRow";
import ModalCapitulo from "./components/ModalCapitulo";
import ModalConfirmar from "./components/ModalConfirmar";
import { formatEuro } from "./utils/calculos";
import {
    NotificationProvider,
    useNotification,
} from "../shared/NotificationContext";

const MODO = "coste";

export function CosteTeoricoInner({ obraId, obraNombre, urlRegresar }) {
    const { showSuccess, showError } = useNotification();

    const {
        capitulos,
        capituloAbierto,
        toggleCapitulo,
        loading,
        error,
        totalCoste,
        indicador,
        crearPartida,
        editarPartida,
        eliminarPartida,
        crearCapitulo,
        editarCapitulo,
        eliminarCapitulo,
    } = usePresupuesto(obraId, MODO);

    const [modalCapitulo, setModalCapitulo] = useState(false);
    const [capituloEditando, setCapituloEditando] = useState(null);
    const [guardandoCapitulo, setGuardandoCapitulo] = useState(false);
    const [modalEliminarCapitulo, setModalEliminarCapitulo] = useState(false);
    const [capituloAEliminar, setCapituloAEliminar] = useState(null);

    const handleGuardarCapitulo = async (datos) => {
        setGuardandoCapitulo(true);
        try {
            if (capituloEditando) {
                await editarCapitulo(capituloEditando.oficio_id, datos);
                showSuccess("Oficio actualizado correctamente.");
            } else {
                await crearCapitulo(datos);
                showSuccess("Oficio creado correctamente.");
            }
            setModalCapitulo(false);
            setCapituloEditando(null);
        } catch {
            showError("Error al guardar el oficio.");
        } finally {
            setGuardandoCapitulo(false);
        }
    };

    const handleEliminarCapitulo = async () => {
        try {
            await eliminarCapitulo(capituloAEliminar.oficio_id);
            showSuccess("Oficio eliminado correctamente.");
        } catch (err) {
            const msg = err.response?.data?.message ?? "Error al eliminar el oficio.";
            showError(msg);
        } finally {
            setModalEliminarCapitulo(false);
            setCapituloAEliminar(null);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20 text-gray-500 text-sm">
                <i className="mgc_loading_line animate-spin mr-2"></i>
                Cargando coste teórico...
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
                        totalGlobal={totalCoste}
                        urlRegresar={urlRegresar}
                        indicador={indicador}
                        onNuevoCapitulo={() => {
                            setCapituloEditando(null);
                            setModalCapitulo(true);
                        }}
                    />
                </div>

                <div className="px-4 pb-4 sm:px-6 sm:pb-6 lg:px-8 lg:pb-8">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm text-slate-700">
                                <thead className="bg-slate-50/80 text-slate-600">
                                    <tr className="border-b border-slate-200">
                                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">
                                            Oficio
                                        </th>
                                        <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                            Total coste
                                        </th>
                                        <th className="px-4 py-3 sm:px-5"></th>
                                        <th className="px-4 py-3 sm:px-5"></th>
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
                                                No hay oficios. Crea el primero.
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
                                                onEditarCapitulo={(cap) => {
                                                    setCapituloEditando(cap);
                                                    setModalCapitulo(true);
                                                }}
                                                onEliminarCapitulo={(cap) => {
                                                    setCapituloAEliminar(cap);
                                                    setModalEliminarCapitulo(true);
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
                                        <td className="px-4 py-4 text-right text-base font-bold text-cyan-600 sm:px-5">
                                            {formatEuro(totalCoste)}
                                        </td>
                                        <td colSpan={4}></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {modalCapitulo && (
                <ModalCapitulo
                    capitulo={capituloEditando}
                    guardando={guardandoCapitulo}
                    onGuardar={handleGuardarCapitulo}
                    onCancelar={() => {
                        setModalCapitulo(false);
                        setCapituloEditando(null);
                    }}
                />
            )}

            {modalEliminarCapitulo && (
                <ModalConfirmar
                    titulo="Eliminar oficio"
                    mensaje={`¿Eliminar "${capituloAEliminar?.oficio_nombre}"? No se puede deshacer.`}
                    textoConfirmar="Eliminar"
                    onConfirmar={handleEliminarCapitulo}
                    onCancelar={() => {
                        setModalEliminarCapitulo(false);
                        setCapituloAEliminar(null);
                    }}
                />
            )}
        </div>
    );
}

export default function CosteTeoricoApp() {
    const el = document.getElementById("react-coste-teorico");
    const obraId = el?.dataset?.obraId;
    const obraNombre = el?.dataset?.obraNombre;
    const urlRegresar = el?.dataset?.urlRegresar ?? "/";

    return (
        <NotificationProvider>
            <CosteTeoricoInner
                obraId={obraId}
                obraNombre={obraNombre}
                urlRegresar={urlRegresar}
            />
        </NotificationProvider>
    );
}
