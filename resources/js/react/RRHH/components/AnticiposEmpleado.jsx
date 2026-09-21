import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { formatEuro } from "../../shared/formato";
import useEnvio from "../useEnvio";
import ModalConfirmar from "./ModalConfirmar";
import { BotonIcono, Campo, CampoAdjunto, Cargando, Cifra, InputEuro, Modal, Panel, Segmentado, SeccionFormulario, Vacio, claseInput } from "./Comunes";
import { MESES, aFormData, botonPrimario, botonSecundario, fechaCorta, hoyISO, mensajeDeError } from "../utils";

const TIPOS = { anticipo: "Anticipo", vale: "Vale" };
const FORMAS_PAGO = { efectivo: "Efectivo", transferencia: "Transferencia", otro: "Otro" };

function ModalAnticipo({ empleado, anticipo, onCerrar, onGuardado }) {
    const { errores, enviando, enviar, limpiarError } = useEnvio();
    const [d, setD] = useState({
        tipo: anticipo?.tipo ?? "anticipo",
        fecha: anticipo?.fecha ?? hoyISO(),
        importe: anticipo?.importe ?? "",
        forma_pago: anticipo?.forma_pago ?? "efectivo",
        concepto: anticipo?.concepto ?? "",
        observaciones: anticipo?.observaciones ?? "",
    });
    const [archivo, setArchivo] = useState(null);
    const [errorArchivo, setErrorArchivo] = useState(null);
    const poner = (c, v) => {
        setD((p) => ({ ...p, [c]: v }));
        limpiarError(c);
    };

    const guardar = async (e) => {
        e.preventDefault();
        const url = anticipo ? `/rrhh/anticipos/${anticipo.id}` : `/rrhh/empleados/${empleado.id}/anticipos`;
        const r = await enviar(url, aFormData(d, archivo, anticipo ? "PUT" : null));
        if (r) onGuardado(r);
    };

    return (
        <Modal
            etiqueta={anticipo ? "Editar" : "Nueva entrega"}
            titulo={empleado.nombre_completo}
            subtitulo="Queda pendiente hasta que se descuente en una nómina."
            onCerrar={onCerrar}
            ancho="max-w-xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-anticipo" className={botonPrimario} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-anticipo" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Entrega" descripcion="Qué, cuándo y cuánto" icono="mgc_bank_card_line" color="amber" conError={!!(errores.tipo || errores.fecha || errores.importe)}>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Tipo" className="sm:col-span-2">
                            <Segmentado opciones={TIPOS} value={d.tipo} onChange={(v) => poner("tipo", v)} />
                        </Campo>
                        <Campo etiqueta="Fecha" obligatorio error={errores.fecha}>
                            <input type="date" value={d.fecha} onChange={(e) => poner("fecha", e.target.value)} className={claseInput(errores.fecha)} />
                        </Campo>
                        <Campo etiqueta="Importe" obligatorio error={errores.importe}>
                            <InputEuro value={d.importe} onChange={(v) => poner("importe", v)} error={errores.importe} autoFocus />
                        </Campo>
                        <Campo etiqueta="Forma de pago" className="sm:col-span-2">
                            <Segmentado opciones={FORMAS_PAGO} value={d.forma_pago} onChange={(v) => poner("forma_pago", v)} />
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Concepto" descripcion="Opcional" icono="mgc_edit_line" color="slate">
                    <div className="space-y-3">
                        <input
                            value={d.concepto}
                            onChange={(e) => poner("concepto", e.target.value)}
                            placeholder="Ej.: vale de gasoil, anticipo de nómina…"
                            className={claseInput(errores.concepto)}
                        />
                        <textarea rows={2} value={d.observaciones} onChange={(e) => poner("observaciones", e.target.value)} placeholder="Observaciones" className={claseInput(errores.observaciones)} />
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Recibo firmado" descripcion="Opcional" icono="mgc_attachment_line" color="violet">
                    <CampoAdjunto
                        archivo={archivo}
                        actual={anticipo?.archivo}
                        texto="Adjuntar el recibo"
                        carpeta="Anticipos y vales"
                        error={errores.archivo || errorArchivo}
                        onChange={(f, err) => {
                            setArchivo(f);
                            setErrorArchivo(err ?? null);
                        }}
                    />
                </SeccionFormulario>
            </form>
        </Modal>
    );
}

/** Anticipos y vales del empleado (pestaña de la ficha). */
export default function AnticiposEmpleado({ empleado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState(null);
    const [modal, setModal] = useState(null);
    const [borrar, setBorrar] = useState(null);

    const cargar = async () => {
        try {
            const { data } = await api.get(`/rrhh/empleados/${empleado.id}/anticipos`);
            setDatos(data);
        } catch (error) {
            showError(mensajeDeError(error, "Error al cargar los anticipos."));
        }
    };

    useEffect(() => {
        cargar();
    }, [empleado.id]);

    const eliminar = async () => {
        try {
            const { data } = await api.delete(`/rrhh/anticipos/${borrar.id}`);
            showSuccess(data.message);
            setBorrar(null);
            cargar();
        } catch (error) {
            showError(error.response?.data?.errors?.anticipo?.[0] ?? mensajeDeError(error, "No se pudo eliminar."));
            setBorrar(null);
        }
    };

    return (
        <Panel
            titulo="Anticipos y vales"
            icono="mgc_bank_card_line"
            acciones={
                <button type="button" onClick={() => setModal({})} className={`${botonPrimario} flex-1 sm:flex-none`}>
                    <i className="mgc_add_line"></i> Nueva entrega
                </button>
            }
        >
            {!datos ? (
                <Cargando />
            ) : (
                <div className="space-y-5 px-5 py-5 sm:px-6">
                    <div className="grid grid-cols-2 gap-3 sm:max-w-md">
                        <Cifra etiqueta="Pendiente de descontar" valor={formatEuro(datos.pendiente)} tono={datos.pendiente > 0 ? "amber" : "slate"} />
                        <Cifra etiqueta="Entregas pendientes" valor={datos.n_pendientes} />
                    </div>

                    {datos.anticipos.length === 0 ? (
                        <Vacio icono="mgc_bank_card_line" titulo="Sin anticipos ni vales" texto="Registra aquí lo que se le entrega a cuenta; luego se descuenta en su nómina." />
                    ) : (
                        <ul className="space-y-2">
                            {datos.anticipos.map((a) => (
                                <li key={a.id} className="flex flex-col gap-2 rounded-2xl border border-slate-200 px-4 py-3 sm:flex-row sm:items-center">
                                    <div className="min-w-0 flex-1">
                                        <p className="font-semibold text-slate-800">
                                            {TIPOS[a.tipo]} · {formatEuro(a.importe)}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {fechaCorta(a.fecha)}
                                            {a.forma_pago && ` · ${FORMAS_PAGO[a.forma_pago]}`}
                                            {a.concepto && ` · ${a.concepto}`}
                                        </p>
                                    </div>
                                    <div className="flex items-center justify-between gap-2 sm:justify-end">
                                        {a.nomina ? (
                                            <span className="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                Descontado en {MESES[a.nomina.mes - 1].toLowerCase()} {a.nomina.anio}
                                            </span>
                                        ) : (
                                            <span className="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Pendiente</span>
                                        )}
                                        <div className="flex items-center">
                                            {a.archivo && <BotonIcono icono="mgc_file_line" titulo="Ver recibo" href={`/drive/ver/${a.archivo.id}`} target="_blank" />}
                                            {!a.nomina_id && (
                                                <>
                                                    <BotonIcono icono="mgc_edit_line" titulo="Editar" onClick={() => setModal({ anticipo: a })} />
                                                    <BotonIcono icono="mgc_delete_line" titulo="Eliminar" peligro onClick={() => setBorrar(a)} />
                                                </>
                                            )}
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}

            {modal && (
                <ModalAnticipo
                    empleado={empleado}
                    anticipo={modal.anticipo}
                    onCerrar={() => setModal(null)}
                    onGuardado={() => {
                        setModal(null);
                        cargar();
                    }}
                />
            )}
            {borrar && (
                <ModalConfirmar titulo="Eliminar entrega" textoConfirmar="Eliminar" peligro onConfirmar={eliminar} onCerrar={() => setBorrar(null)}>
                    Se eliminará el {TIPOS[borrar.tipo].toLowerCase()} de <strong>{formatEuro(borrar.importe)}</strong> del {fechaCorta(borrar.fecha)}.
                </ModalConfirmar>
            )}
        </Panel>
    );
}
