import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import useEnvio from "../useEnvio";
import ModalConfirmar from "./ModalConfirmar";
import { Aviso, BotonIcono, Campo, CampoAdjunto, Cargando, Modal, Panel, Segmentado, SeccionFormulario, Vacio, claseInput } from "./Comunes";
import { aFormData, botonPrimario, botonSecundario, fechaCorta, hoyISO, mensajeDeError } from "../utils";

const GRAVEDADES = { leve: "Leve", grave: "Grave", muy_grave: "Muy grave" };
const TIPOS = {
    amonestacion_verbal: "Amonestación verbal",
    amonestacion_escrita: "Amonestación por escrito",
    suspension: "Suspensión de empleo y sueldo",
    despido: "Despido disciplinario",
    otra: "Otra",
};
const PRESCRIPCION = { leve: 10, grave: 20, muy_grave: 60 };
const CLASES_GRAVEDAD = {
    leve: "border-amber-200 bg-amber-50 text-amber-700",
    grave: "border-orange-200 bg-orange-50 text-orange-700",
    muy_grave: "border-rose-200 bg-rose-50 text-rose-700",
};

const sumarDias = (fecha, dias) => {
    const [a, m, d] = fecha.split("-").map(Number);
    const r = new Date(a, m - 1, d + dias);
    return `${r.getFullYear()}-${String(r.getMonth() + 1).padStart(2, "0")}-${String(r.getDate()).padStart(2, "0")}`;
};

function ModalSancion({ empleado, sancion, onCerrar, onGuardado }) {
    const { errores, enviando, enviar, limpiarError } = useEnvio();
    const [d, setD] = useState({
        fecha_hechos: sancion?.fecha_hechos ?? hoyISO(),
        fecha_comunicacion: sancion?.fecha_comunicacion ?? "",
        gravedad: sancion?.gravedad ?? "leve",
        tipo: sancion?.tipo ?? "amonestacion_escrita",
        dias_suspension: sancion?.dias_suspension ?? "",
        fecha_inicio_suspension: sancion?.fecha_inicio_suspension ?? "",
        descripcion: sancion?.descripcion ?? "",
        estado: sancion?.estado ?? "vigente",
    });
    const [archivo, setArchivo] = useState(null);
    const [errorArchivo, setErrorArchivo] = useState(null);
    const poner = (c, v) => {
        setD((p) => ({ ...p, [c]: v }));
        limpiarError(c);
    };

    const guardar = async (e) => {
        e.preventDefault();
        const url = sancion ? `/rrhh/sanciones/${sancion.id}` : `/rrhh/empleados/${empleado.id}/sanciones`;
        const r = await enviar(url, aFormData(d, archivo, sancion ? "PUT" : null));
        if (r) onGuardado(r);
    };

    const limite = d.fecha_hechos ? sumarDias(d.fecha_hechos, PRESCRIPCION[d.gravedad]) : null;

    return (
        <Modal
            etiqueta={sancion ? "Editar sanción" : "Nueva sanción"}
            titulo={empleado.nombre_completo}
            onCerrar={onCerrar}
            ancho="max-w-2xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-sancion" className={botonPrimario} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-sancion" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Hechos" descripcion="Qué pasó y cuándo" icono="mgc_warning_line" color="rose" conError={!!(errores.fecha_hechos || errores.descripcion)}>
                    <div className="space-y-4">
                        <Campo etiqueta="Fecha de los hechos" obligatorio error={errores.fecha_hechos} className="sm:max-w-xs">
                            <input type="date" value={d.fecha_hechos} onChange={(e) => poner("fecha_hechos", e.target.value)} className={claseInput(errores.fecha_hechos)} />
                        </Campo>
                        <Campo etiqueta="Descripción" obligatorio error={errores.descripcion}>
                            <textarea rows={4} value={d.descripcion} onChange={(e) => poner("descripcion", e.target.value)} placeholder="Describe los hechos con fechas y detalles." className={claseInput(errores.descripcion)} />
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Sanción"
                    descripcion="Gravedad y medida"
                    icono="mgc_forbid_circle_line"
                    color="amber"
                    conError={!!(errores.gravedad || errores.tipo || errores.dias_suspension || errores.fecha_inicio_suspension)}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Gravedad" obligatorio className="sm:col-span-2">
                            <Segmentado opciones={GRAVEDADES} value={d.gravedad} onChange={(v) => poner("gravedad", v)} />
                        </Campo>
                        <Campo etiqueta="Tipo de sanción" obligatorio error={errores.tipo} className="sm:col-span-2">
                            <select value={d.tipo} onChange={(e) => poner("tipo", e.target.value)} className={claseInput(errores.tipo)}>
                                {Object.entries(TIPOS).map(([v, t]) => (
                                    <option key={v} value={v}>
                                        {t}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                        {d.tipo === "suspension" && (
                            <>
                                <Campo etiqueta="Días de suspensión" obligatorio error={errores.dias_suspension}>
                                    <input type="number" min={1} max={365} value={d.dias_suspension} onChange={(e) => poner("dias_suspension", e.target.value)} className={claseInput(errores.dias_suspension)} />
                                </Campo>
                                <Campo etiqueta="Empieza el" error={errores.fecha_inicio_suspension}>
                                    <input type="date" min={d.fecha_hechos} value={d.fecha_inicio_suspension} onChange={(e) => poner("fecha_inicio_suspension", e.target.value)} className={claseInput(errores.fecha_inicio_suspension)} />
                                </Campo>
                            </>
                        )}
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Comunicación" descripcion="Cuándo se entregó la carta" icono="mgc_paper_line" color="indigo" conError={!!errores.fecha_comunicacion}>
                    <div className="space-y-3">
                        <Campo etiqueta="Fecha de comunicación" error={errores.fecha_comunicacion} className="sm:max-w-xs">
                            <input type="date" min={d.fecha_hechos} value={d.fecha_comunicacion} onChange={(e) => poner("fecha_comunicacion", e.target.value)} className={claseInput(errores.fecha_comunicacion)} />
                        </Campo>
                        {limite && !sancion && (
                            <Aviso>
                                Orientativo: una falta {GRAVEDADES[d.gravedad].toLowerCase()} prescribe a los <strong>{PRESCRIPCION[d.gravedad]} días</strong> desde que la
                                empresa conoce los hechos (art. 60.2 del Estatuto de los Trabajadores). Si los conociste el {fechaCorta(d.fecha_hechos)}, el límite sería el{" "}
                                <strong>{fechaCorta(limite)}</strong>. Consulta con la asesoría.
                            </Aviso>
                        )}
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Carta de sanción y estado" descripcion="Opcional" icono="mgc_attachment_line" color="violet">
                    <div className="space-y-4">
                        <CampoAdjunto
                            archivo={archivo}
                            actual={sancion?.archivo}
                            texto="Adjuntar la carta firmada"
                            carpeta="Sanciones"
                            error={errores.archivo || errorArchivo}
                            onChange={(f, err) => {
                                setArchivo(f);
                                setErrorArchivo(err ?? null);
                            }}
                        />
                        <Campo etiqueta="Estado" className="sm:max-w-xs">
                            <Segmentado opciones={{ vigente: "Vigente", anulada: "Anulada" }} value={d.estado} onChange={(v) => poner("estado", v)} />
                        </Campo>
                    </div>
                </SeccionFormulario>
            </form>
        </Modal>
    );
}

/** Sanciones del empleado (pestaña de la ficha). */
export default function SancionesEmpleado({ empleado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState(null);
    const [modal, setModal] = useState(null);
    const [borrar, setBorrar] = useState(null);

    const cargar = async () => {
        try {
            const { data } = await api.get(`/rrhh/empleados/${empleado.id}/sanciones`);
            setDatos(data);
        } catch (error) {
            showError(mensajeDeError(error, "Error al cargar las sanciones."));
        }
    };

    useEffect(() => {
        cargar();
    }, [empleado.id]);

    const eliminar = async () => {
        try {
            const { data } = await api.delete(`/rrhh/sanciones/${borrar.id}`);
            showSuccess(data.message);
            setBorrar(null);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudo eliminar."));
        }
    };

    return (
        <Panel
            titulo="Sanciones"
            icono="mgc_forbid_circle_line"
            acciones={
                <button type="button" onClick={() => setModal({})} className={`${botonPrimario} flex-1 sm:flex-none`}>
                    <i className="mgc_add_line"></i> Nueva sanción
                </button>
            }
        >
            {!datos ? (
                <Cargando />
            ) : datos.sanciones.length === 0 ? (
                <Vacio icono="mgc_check_circle_line" titulo="Sin sanciones" />
            ) : (
                <ul className="space-y-2 px-5 py-5 sm:px-6">
                    {datos.sanciones.map((s) => (
                        <li key={s.id} className={`rounded-2xl border border-slate-200 px-4 py-3 ${s.estado === "anulada" ? "opacity-60" : ""}`}>
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-start">
                                <div className="min-w-0 flex-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className={`rounded-full border px-2 py-0.5 text-[11px] font-semibold ${CLASES_GRAVEDAD[s.gravedad]}`}>{GRAVEDADES[s.gravedad]}</span>
                                        <p className={`font-semibold text-slate-800 ${s.estado === "anulada" ? "line-through" : ""}`}>{TIPOS[s.tipo]}</p>
                                        {s.estado === "anulada" && <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">Anulada</span>}
                                    </div>
                                    <p className="mt-0.5 text-xs text-slate-500">
                                        Hechos: {fechaCorta(s.fecha_hechos)}
                                        {s.fecha_comunicacion && ` · Comunicada: ${fechaCorta(s.fecha_comunicacion)}`}
                                        {s.dias_suspension && ` · ${s.dias_suspension} días de suspensión`}
                                        {s.fecha_inicio_suspension && ` desde el ${fechaCorta(s.fecha_inicio_suspension)}`}
                                    </p>
                                    <p className="mt-1 whitespace-pre-line text-sm text-slate-600">{s.descripcion}</p>
                                </div>
                                <div className="flex shrink-0 items-center">
                                    {s.archivo && <BotonIcono icono="mgc_file_line" titulo="Ver carta" href={`/drive/ver/${s.archivo.id}`} target="_blank" />}
                                    <BotonIcono icono="mgc_edit_line" titulo="Editar" onClick={() => setModal({ sancion: s })} />
                                    <BotonIcono icono="mgc_delete_line" titulo="Eliminar" peligro onClick={() => setBorrar(s)} />
                                </div>
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            {modal && (
                <ModalSancion
                    empleado={empleado}
                    sancion={modal.sancion}
                    onCerrar={() => setModal(null)}
                    onGuardado={() => {
                        setModal(null);
                        cargar();
                    }}
                />
            )}
            {borrar && (
                <ModalConfirmar titulo="Eliminar sanción" textoConfirmar="Eliminar" peligro onConfirmar={eliminar} onCerrar={() => setBorrar(null)}>
                    Se eliminará la sanción del {fechaCorta(borrar.fecha_hechos)}. Si solo quieres dejarla sin efecto, edítala y márcala como <strong>anulada</strong>.
                </ModalConfirmar>
            )}
        </Panel>
    );
}
