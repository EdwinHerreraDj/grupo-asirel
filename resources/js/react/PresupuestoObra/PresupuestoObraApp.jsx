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
import ModalPdf from "./components/ModalPdf";

const el = document.getElementById("react-presupuesto-obra");
const obraId = el?.dataset?.obraId;
const obraNombre = el?.dataset?.obraNombre;
const urlRegresar = el?.dataset?.urlRegresar ?? "/";

function PresupuestoObraInner() {
    const { showSuccess, showError } = useNotification();

    const {
        modo,
        setModo,
        capitulos,
        capituloAbierto,
        toggleCapitulo,
        loading,
        error,
        totalGlobal,
        crearPartida,
        editarPartida,
        eliminarPartida,
        crearCapitulo,
        editarCapitulo,
        eliminarCapitulo,
        pendientesSincronizar,
        sincronizar,
        incrementar,
        restablecer,
    } = usePresupuesto(obraId);

    const [modalCapitulo, setModalCapitulo] = useState(false);
    const [capituloEditando, setCapituloEditando] = useState(null);
    const [guardandoCapitulo, setGuardandoCapitulo] = useState(false);
    const [modalEliminarCapitulo, setModalEliminarCapitulo] = useState(false);
    const [capituloAEliminar, setCapituloAEliminar] = useState(null);
    const [modalPdf, setModalPdf] = useState(false);

    const handleGuardarCapitulo = async (datos) => {
        setGuardandoCapitulo(true);
        try {
            if (capituloEditando) {
                await editarCapitulo(capituloEditando.oficio_id, datos);
                showSuccess("Capítulo actualizado correctamente.");
            } else {
                await crearCapitulo(datos);
                showSuccess("Capítulo creado correctamente.");
            }
            setModalCapitulo(false);
            setCapituloEditando(null);
        } catch (err) {
            showError("Error al guardar el capítulo.");
        } finally {
            setGuardandoCapitulo(false);
        }
    };

    const handleEliminarCapitulo = async () => {
        try {
            await eliminarCapitulo(capituloAEliminar.oficio_id);
            showSuccess("Capítulo eliminado correctamente.");
        } catch (err) {
            const msg =
                err.response?.data?.message ?? "Error al eliminar el capítulo.";
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
                Cargando presupuesto...
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
                        modo={modo}
                        onToggleModo={setModo}
                        totalGlobal={totalGlobal}
                        urlRegresar={urlRegresar}
                        pendientesSincronizar={pendientesSincronizar}
                        onSincronizar={async () => {
                            try {
                                await sincronizar();
                                showSuccess(
                                    "Partidas sincronizadas correctamente.",
                                );
                            } catch {
                                showError("Error al sincronizar.");
                            }
                        }}
                        onIncrementarGlobal={async (pct) => {
                            try {
                                await incrementar(pct);
                                showSuccess(
                                    `Incremento del ${pct}% aplicado a toda la obra.`,
                                );
                            } catch {
                                showError("Error al aplicar el incremento.");
                            }
                        }}
                        onRestablecerGlobal={async () => {
                            try {
                                await restablecer();
                                showSuccess(
                                    "Todos los precios restablecidos al coste original.",
                                );
                            } catch {
                                showError("Error al restablecer los precios.");
                            }
                        }}
                        onNuevoCapitulo={
                            modo === "coste"
                                ? () => {
                                      setCapituloEditando(null);
                                      setModalCapitulo(true);
                                  }
                                : null
                        }
                        onDescargarPdf={
                            modo === "venta" ? () => setModalPdf(true) : null
                        }
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
                                        {modo === "venta" ? (
                                            <>
                                                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-5">
                                                    Coste
                                                </th>
                                                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                                    Venta
                                                </th>
                                                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                                    Margen
                                                </th>
                                            </>
                                        ) : (
                                            <>
                                                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">
                                                    Total
                                                </th>
                                                <th className="px-4 py-3 sm:px-5"></th>
                                                <th className="px-4 py-3 sm:px-5"></th>
                                            </>
                                        )}
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
                                                {modo === "coste"
                                                    ? "No hay capítulos. Crea el primero."
                                                    : "No hay capítulos configurados para esta obra."}
                                            </td>
                                        </tr>
                                    ) : (
                                        capitulos.map((capitulo) => (
                                            <CapituloRow
                                                key={capitulo.oficio_id}
                                                capitulo={capitulo}
                                                modo={modo}
                                                abierto={
                                                    capituloAbierto ===
                                                    capitulo.oficio_id
                                                }
                                                onToggle={() =>
                                                    toggleCapitulo(
                                                        capitulo.oficio_id,
                                                    )
                                                }
                                                onCrearPartida={(datos) =>
                                                    crearPartida(
                                                        capitulo.oficio_id,
                                                        datos,
                                                    )
                                                }
                                                onEditarPartida={editarPartida}
                                                onEliminarPartida={(
                                                    partidaId,
                                                ) =>
                                                    eliminarPartida(
                                                        partidaId,
                                                        capitulo.oficio_id,
                                                    )
                                                }
                                                onEditarCapitulo={(cap) => {
                                                    setCapituloEditando(cap);
                                                    setModalCapitulo(true);
                                                }}
                                                onEliminarCapitulo={(cap) => {
                                                    setCapituloAEliminar(cap);
                                                    setModalEliminarCapitulo(
                                                        true,
                                                    );
                                                }}
                                                onIncrementar={(
                                                    pct,
                                                    oficioId,
                                                ) => incrementar(pct, oficioId)}
                                                onRestablecer={async (
                                                    oficioId,
                                                ) => {
                                                    try {
                                                        await restablecer(
                                                            oficioId,
                                                        );
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
                                        <td className="px-4 py-4 text-right text-base font-bold text-cyan-600 sm:px-5">
                                            {formatEuro(totalGlobal)}
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
                    titulo="Eliminar capítulo"
                    mensaje={`¿Estás seguro de que deseas eliminar "${capituloAEliminar?.oficio_nombre}"? No se puede deshacer.`}
                    textoConfirmar="Eliminar"
                    onConfirmar={handleEliminarCapitulo}
                    onCancelar={() => {
                        setModalEliminarCapitulo(false);
                        setCapituloAEliminar(null);
                    }}
                />
            )}

            {modalPdf && (
                <ModalPdf
                    obraId={obraId}
                    onCancelar={() => setModalPdf(false)}
                />
            )}
        </div>
    );
}

export default function PresupuestoObraApp() {
    return (
        <NotificationProvider>
            <PresupuestoObraInner />
        </NotificationProvider>
    );
}
