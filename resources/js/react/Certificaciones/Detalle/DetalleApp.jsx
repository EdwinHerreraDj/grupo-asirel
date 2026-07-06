import React, { useState } from "react";
import useDetalle from "./hooks/useDetalle";
import CabeceraDetalle from "./components/CabeceraDetalle";
import ResumenFiscal from "./components/ResumenFiscal";
import TablaLineas from "./components/TablaLineas";
import TimelineEventos from "./components/TimelineEventos";
import ModalSeleccionarPartida from "./components/ModalSeleccionarPartida";
import ModalEditarLinea from "./components/ModalEditarLinea";
import ModalImpuestos from "./components/ModalImpuestos";
import ModalAceptar from "./components/ModalAceptar";
import ModalAnular from "./components/ModalAnular";
import {
    NotificationProvider,
    useNotification,
} from "../../shared/NotificationContext";

const el = document.getElementById("react-certificaciones-detalle");
const certificacionId = el?.dataset?.certificacionId;
const urlVolver = el?.dataset?.urlVolver ?? "/";

function DetalleInner() {
    const { showSuccess, showError } = useNotification();

    const {
        certificacion,
        lineas,
        presupuesto,
        partidasVenta,
        eventos,
        loading,
        error,
        crearLinea,
        editarLinea,
        eliminarLinea,
        actualizarImpuestos,
        aceptar,
        anular,
        cambiarEstadoCobro,
        descargarPdf,
    } = useDetalle(certificacionId);

    const [modalPartida, setModalPartida] = useState(false);
    const [modalImpuestos, setModalImpuestos] = useState(false);
    const [modalAceptar, setModalAceptar] = useState(false);
    const [modalAnular, setModalAnular] = useState(false);
    const [lineaEnEdicion, setLineaEnEdicion] = useState(null);
    const [guardando, setGuardando] = useState(false);
    const [aceptando, setAceptando] = useState(false);
    const [anulando, setAnulando] = useState(false);
    const [descargando, setDescargando] = useState(false);

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20 text-gray-500 text-sm">
                <i className="mgc_loading_line animate-spin mr-2"></i>
                Cargando certificación...
            </div>
        );
    }

    if (error || !certificacion) {
        return (
            <div className="p-6 text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg">
                {error ?? "Certificación no encontrada."}
            </div>
        );
    }

    const editable = certificacion.estado_certificacion === "pendiente";

    const handleCrearLinea = async (datos) => {
        setGuardando(true);
        try {
            const { forzar = false, ...resto } = datos;
            await crearLinea(resto, { forzar });
            showSuccess(
                forzar
                    ? "Línea añadida (se superó el pendiente)."
                    : "Línea añadida correctamente.",
            );
            setModalPartida(false);
        } catch (err) {
            if (err.exceso) {
                // Backend devolvi\u00f3 409: la UI debe confirmar expl\u00edcitamente.
                // Normalmente esto no llega aqu\u00ed porque el modal ya confirma antes,
                // pero cubrimos el caso de defensa en profundidad.
                showError(
                    `${err.message}. Pendiente: ${err.pendiente}. Marca "forzar" para continuar.`,
                );
            } else {
                showError(
                    err.response?.data?.message ??
                        "Error al añadir la línea.",
                );
            }
        } finally {
            setGuardando(false);
        }
    };

    const handleEditarLinea = async (lineaId, datos, { forzar = false } = {}) => {
        setGuardando(true);
        try {
            await editarLinea(lineaId, datos, { forzar });
            showSuccess(
                forzar
                    ? "Línea actualizada (se superó el pendiente)."
                    : "Línea actualizada correctamente.",
            );
            setLineaEnEdicion(null);
        } catch (err) {
            if (err.exceso) {
                showError(
                    `${err.message}. Pendiente: ${err.pendiente}. Marca "forzar" para continuar.`,
                );
            } else {
                showError(
                    err.response?.data?.message ??
                        "Error al actualizar la línea.",
                );
            }
        } finally {
            setGuardando(false);
        }
    };

    const handleEliminarLinea = async (lineaId) => {
        try {
            await eliminarLinea(lineaId);
            showSuccess("Línea eliminada correctamente.");
        } catch {
            showError("Error al eliminar la línea.");
        }
    };

    const handleImpuestos = async (datos) => {
        setGuardando(true);
        try {
            await actualizarImpuestos(datos);
            showSuccess("Impuestos actualizados en todos los capítulos.");
            setModalImpuestos(false);
        } catch {
            showError("Error al actualizar los impuestos.");
        } finally {
            setGuardando(false);
        }
    };

    const handleAceptar = async () => {
        setAceptando(true);
        try {
            await aceptar();
            showSuccess("Certificación aceptada. Ya no se puede modificar.");
            setModalAceptar(false);
        } catch (err) {
            showError(
                err.response?.data?.message ??
                    "Error al aceptar la certificación.",
            );
        } finally {
            setAceptando(false);
        }
    };

    const handleAnular = async (motivo) => {
        setAnulando(true);
        try {
            await anular(motivo);
            showSuccess("Aceptación anulada. La certificación vuelve a pendiente.");
            setModalAnular(false);
        } catch (err) {
            showError(
                err.response?.data?.message ??
                    "Error al anular la certificación.",
            );
        } finally {
            setAnulando(false);
        }
    };

    const handleCambiarEstadoCobro = async (estadoCobro) => {
        try {
            await cambiarEstadoCobro(estadoCobro);
            showSuccess("Estado de cobro actualizado.");
        } catch (err) {
            showError(
                err.response?.data?.message ??
                    "Error al actualizar el estado de cobro.",
            );
        }
    };

    const handleDescargarPdf = async () => {
        setDescargando(true);
        try {
            await descargarPdf();
            showSuccess("PDF generado correctamente.");
        } catch {
            showError("Error al generar el PDF.");
        } finally {
            setDescargando(false);
        }
    };

    return (
        <div>
            <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                <div className="space-y-6 px-5 py-5 sm:px-6 lg:px-8 lg:py-6">
                    <div className="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/30 p-4 sm:p-5">
                        <CabeceraDetalle
                            certificacion={certificacion}
                            urlVolver={urlVolver}
                            onAceptar={() => setModalAceptar(true)}
                            onImpuestos={() => setModalImpuestos(true)}
                            onAnular={() => setModalAnular(true)}
                            onCambiarEstadoCobro={handleCambiarEstadoCobro}
                            onDescargarPdf={
                                lineas.length > 0
                                    ? handleDescargarPdf
                                    : null
                            }
                            descargando={descargando}
                        />
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <TablaLineas
                            lineas={lineas}
                            editable={editable}
                            onNuevaLinea={() => setModalPartida(true)}
                            onEditar={(linea) => setLineaEnEdicion(linea)}
                            onEliminar={handleEliminarLinea}
                        />
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm sm:p-5">
                        <ResumenFiscal
                            certificacion={certificacion}
                            presupuesto={presupuesto}
                        />
                    </div>

                    <TimelineEventos eventos={eventos} />
                </div>
            </div>

            {modalPartida && (
                <ModalSeleccionarPartida
                    partidasVenta={partidasVenta}
                    guardando={guardando}
                    onGuardar={handleCrearLinea}
                    onCancelar={() => setModalPartida(false)}
                />
            )}

            {lineaEnEdicion && (
                <ModalEditarLinea
                    linea={lineaEnEdicion}
                    partidasVenta={partidasVenta}
                    guardando={guardando}
                    onGuardar={handleEditarLinea}
                    onCancelar={() => setLineaEnEdicion(null)}
                />
            )}

            {modalImpuestos && (
                <ModalImpuestos
                    certificacion={certificacion}
                    guardando={guardando}
                    onGuardar={handleImpuestos}
                    onCancelar={() => setModalImpuestos(false)}
                />
            )}

            {modalAceptar && (
                <ModalAceptar
                    aceptando={aceptando}
                    onConfirmar={handleAceptar}
                    onCancelar={() => setModalAceptar(false)}
                />
            )}

            {modalAnular && (
                <ModalAnular
                    anulando={anulando}
                    onConfirmar={handleAnular}
                    onCancelar={() => setModalAnular(false)}
                />
            )}
        </div>
    );
}

export default function DetalleApp() {
    return (
        <NotificationProvider>
            <DetalleInner />
        </NotificationProvider>
    );
}
