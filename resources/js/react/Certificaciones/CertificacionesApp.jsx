import React, { useState } from "react";
import useCertificaciones from "./hooks/useCertificaciones";
import FiltrosCertificaciones from "./components/FiltrosCertificaciones";
import TablaCertificaciones from "./components/TablaCertificaciones";
import ModalNuevaCertificacion from "./components/ModalNuevaCertificacion";
import ModalNuevoCapitulo from "./components/ModalNuevoCapitulo";
import ModalFacturar from "./components/ModalFacturar";
import ModalInforme from "./components/ModalInforme";
import ModalConfirmar from "../PresupuestoObra/components/ModalConfirmar";
import ComparativaMensual from "./components/ComparativaMensual";
import {
    NotificationProvider,
    useNotification,
} from "../shared/NotificationContext";

const el = document.getElementById("react-certificaciones-listado");
const obraId = el?.dataset?.obraId;

function CertificacionesInner() {
    const { showSuccess, showError } = useNotification();

    const {
        certificaciones,
        oficios,
        clientes,
        loading,
        error,
        page,
        setPage,
        lastPage,
        total,
        pending,
        setPending,
        aplicarFiltros,
        limpiarFiltros,
        crearCertificacion,
        crearCapitulo,
        eliminarCertificacion,
        recargar,
    } = useCertificaciones(obraId);

    // Modales
    const [modalNueva, setModalNueva] = useState(false);
    const [modalCapitulo, setModalCapitulo] = useState(false);
    const [certParaCapitulo, setCertParaCapitulo] = useState(null);
    const [modalFacturar, setModalFacturar] = useState(false);
    const [numeroFacturar, setNumeroFacturar] = useState(null);
    const [modalInforme, setModalInforme] = useState(false);
    const [numeroInforme, setNumeroInforme] = useState(null);
    const [modalEliminar, setModalEliminar] = useState(false);
    const [idEliminar, setIdEliminar] = useState(null);
    const [mostrarComparativa, setMostrarComparativa] = useState(false);

    const handleVerDetalle = (id) => {
        window.location.href = `/empresa/certificaciones/${id}`;
    };

    const handleNuevoCapitulo = (cert) => {
        setCertParaCapitulo(cert);
        setModalCapitulo(true);
    };

    const handleFacturar = (numero) => {
        setNumeroFacturar(numero);
        setModalFacturar(true);
    };

    const handleInforme = (numero) => {
        setNumeroInforme(numero);
        setModalInforme(true);
    };

    const handleEliminar = (id) => {
        setIdEliminar(id);
        setModalEliminar(true);
    };

    const handleConfirmarEliminar = async () => {
        try {
            await eliminarCertificacion(idEliminar);
            showSuccess("Certificación eliminada correctamente.");
        } catch {
            showError("Error al eliminar la certificación.");
        } finally {
            setModalEliminar(false);
            setIdEliminar(null);
        }
    };

    if (error) {
        return (
            <div className="rounded-2xl border border-red-200 bg-gradient-to-br from-red-50 to-white px-4 py-4 text-sm font-medium text-red-700 shadow-sm sm:px-5">
                <div className="flex items-start gap-3">
                    <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <i className="mgc_warning_line text-base"></i>
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-red-800">
                            Se ha producido un error
                        </p>
                        <p className="mt-1 text-sm text-red-600">{error}</p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div>
            <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                {/* CABECERA */}
                <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6 lg:px-8">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="min-w-0">
                            <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                Gestión económica
                            </div>
                            <h2 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                                Certificaciones
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                Gestión de certificaciones de la obra
                            </p>
                        </div>

                        <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                            <button
                                onClick={() => setMostrarComparativa((v) => !v)}
                                className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900"
                            >
                                <i className="mgc_calendar_month_line text-base"></i>
                                Comparativa mensual
                            </button>

                            <button
                                onClick={() => setModalNueva(true)}
                                className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500"
                            >
                                <i className="mgc_add_line text-base"></i>
                                Nueva certificación
                            </button>
                        </div>
                    </div>
                </div>

                <div className="space-y-6 px-5 py-5 sm:px-6 lg:px-8 lg:py-6">
                    {/* COMPARATIVA */}
                    {mostrarComparativa && (
                        <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-3 sm:p-4">
                            <ComparativaMensual obraId={obraId} />
                        </div>
                    )}

                    {/* FILTROS */}
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <FiltrosCertificaciones
                            pending={pending}
                            setPending={setPending}
                            oficios={oficios}
                            clientes={clientes}
                            onAplicar={aplicarFiltros}
                            onLimpiar={limpiarFiltros}
                        />
                    </div>

                    {/* TABLA */}
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <TablaCertificaciones
                            certificaciones={certificaciones}
                            loading={loading}
                            page={page}
                            lastPage={lastPage}
                            total={total}
                            onPageChange={setPage}
                            onVerDetalle={handleVerDetalle}
                            onNuevoCapitulo={handleNuevoCapitulo}
                            onEliminar={handleEliminar}
                            onFacturar={handleFacturar}
                            onInforme={handleInforme}
                        />
                    </div>
                </div>
            </div>

            {/* MODALES */}
            {modalNueva && (
                <ModalNuevaCertificacion
                    obraId={obraId}
                    oficios={oficios}
                    clientes={clientes}
                    onGuardar={async (datos) => {
                        try {
                            await crearCertificacion(datos);
                            showSuccess("Certificación creada correctamente.");
                            setModalNueva(false);
                        } catch (err) {
                            showError(
                                err.response?.data?.message ??
                                    "Error al crear la certificación.",
                            );
                        }
                    }}
                    onCancelar={() => setModalNueva(false)}
                />
            )}

            {modalCapitulo && certParaCapitulo && (
                <ModalNuevoCapitulo
                    obraId={obraId}
                    cert={certParaCapitulo}
                    oficios={oficios}
                    onGuardar={async (datos) => {
                        try {
                            await crearCapitulo(datos);
                            showSuccess("Capítulo creado correctamente.");
                            setModalCapitulo(false);
                            setCertParaCapitulo(null);
                        } catch (err) {
                            showError(
                                err.response?.data?.message ??
                                    "Error al crear el capítulo.",
                            );
                        }
                    }}
                    onCancelar={() => {
                        setModalCapitulo(false);
                        setCertParaCapitulo(null);
                    }}
                />
            )}

            {modalFacturar && (
                <ModalFacturar
                    obraId={obraId}
                    numeroCertificacion={numeroFacturar}
                    onFacturado={(redirectUrl) => {
                        window.location.href = redirectUrl;
                    }}
                    onCancelar={() => {
                        setModalFacturar(false);
                        setNumeroFacturar(null);
                    }}
                />
            )}

            {modalInforme && (
                <ModalInforme
                    obraId={obraId}
                    numeroCertificacion={numeroInforme}
                    onCancelar={() => {
                        setModalInforme(false);
                        setNumeroInforme(null);
                    }}
                />
            )}

            {modalEliminar && (
                <ModalConfirmar
                    titulo="Eliminar certificación"
                    mensaje="¿Estás seguro de que deseas eliminar esta certificación? Esta acción no se puede deshacer."
                    textoConfirmar="Eliminar"
                    onConfirmar={handleConfirmarEliminar}
                    onCancelar={() => {
                        setModalEliminar(false);
                        setIdEliminar(null);
                    }}
                />
            )}
        </div>
    );
}

export default function CertificacionesApp() {
    return (
        <NotificationProvider>
            <CertificacionesInner />
        </NotificationProvider>
    );
}
