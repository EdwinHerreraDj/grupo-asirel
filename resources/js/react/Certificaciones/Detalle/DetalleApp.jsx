import React, { useState } from "react";
import useDetalle from "./hooks/useDetalle";
import CabeceraDetalle from "./components/CabeceraDetalle";
import ResumenFiscal from "./components/ResumenFiscal";
import TablaLineas from "./components/TablaLineas";
import ModalSeleccionarPartida from "./components/ModalSeleccionarPartida";
import ModalImpuestos from "./components/ModalImpuestos";
import ModalAceptar from "./components/ModalAceptar";
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
        loading,
        error,
        crearLinea,
        eliminarLinea,
        actualizarImpuestos,
        aceptar,
    } = useDetalle(certificacionId);

    const [modalPartida, setModalPartida] = useState(false);
    const [modalImpuestos, setModalImpuestos] = useState(false);
    const [modalAceptar, setModalAceptar] = useState(false);
    const [guardando, setGuardando] = useState(false);
    const [aceptando, setAceptando] = useState(false);

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
            await crearLinea(datos);
            showSuccess("Línea añadida correctamente.");
            setModalPartida(false);
        } catch (err) {
            showError(
                err.response?.data?.message ?? "Error al añadir la línea.",
            );
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
                        />
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <TablaLineas
                            lineas={lineas}
                            editable={editable}
                            onNuevaLinea={() => setModalPartida(true)}
                            onEliminar={handleEliminarLinea}
                        />
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm sm:p-5">
                        <ResumenFiscal
                            certificacion={certificacion}
                            presupuesto={presupuesto}
                        />
                    </div>
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
