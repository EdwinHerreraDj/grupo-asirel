import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import useEnvio from "../useEnvio";
import ModalConfirmar from "./ModalConfirmar";
import { BotonIcono, Campo, CampoAdjunto, Cargando, Cifra, Modal, Panel, SeccionFormulario, Vacio, claseInput } from "./Comunes";
import { aFormData, botonPrimario, botonSecundario, fechaCorta, hoyISO, mensajeDeError, numeroDias, textoDias } from "../utils";

export const CATEGORIAS_CURSO = {
    prl: "Prevención de riesgos (PRL)",
    oficio: "Oficio / especialidad",
    carnet: "Carnet o habilitación",
    otro: "Otro",
};

export const ESTADOS_CURSO = {
    vigente: { texto: "Vigente", clases: "border-emerald-200 bg-emerald-50 text-emerald-700" },
    sin_caducidad: { texto: "Sin caducidad", clases: "border-slate-200 bg-slate-50 text-slate-600" },
    proximo: { texto: "Caduca pronto", clases: "border-amber-200 bg-amber-50 text-amber-700" },
    vencido: { texto: "Caducado", clases: "border-rose-200 bg-rose-50 text-rose-700" },
    renovado: { texto: "Renovado", clases: "border-slate-200 bg-slate-100 text-slate-500" },
};

/** Suma años a una fecha ISO. */
const masAnios = (fecha, anios) => {
    const [a, m, d] = fecha.split("-").map(Number);
    const r = new Date(a + anios, m - 1, d);
    const pad = (n) => String(n).padStart(2, "0");
    return `${r.getFullYear()}-${pad(r.getMonth() + 1)}-${pad(r.getDate())}`;
};

function ModalCurso({ empleado, curso, onCerrar, onGuardado }) {
    const { errores, enviando, enviar, limpiarError } = useEnvio();
    const [d, setD] = useState({
        nombre: curso?.nombre ?? "",
        categoria: curso?.categoria ?? "prl",
        entidad: curso?.entidad ?? "",
        horas: curso?.horas ?? "",
        fecha: curso?.fecha ?? hoyISO(),
        fecha_caducidad: curso?.fecha_caducidad ?? "",
        observaciones: curso?.observaciones ?? "",
    });
    const [archivo, setArchivo] = useState(null);
    const [errorArchivo, setErrorArchivo] = useState(null);
    const poner = (c, v) => {
        setD((p) => ({ ...p, [c]: v }));
        limpiarError(c);
    };

    const guardar = async (e) => {
        e.preventDefault();
        const url = curso ? `/rrhh/cursos/${curso.id}` : `/rrhh/empleados/${empleado.id}/cursos`;
        const r = await enviar(url, aFormData(d, archivo, curso ? "PUT" : null));
        if (r) onGuardado(r);
    };

    return (
        <Modal
            etiqueta={curso ? "Editar curso" : "Nuevo curso"}
            titulo={empleado.nombre_completo}
            onCerrar={onCerrar}
            ancho="max-w-2xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-curso" className={botonPrimario} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-curso" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Curso" descripcion="Qué formación es y quién la imparte" icono="mgc_star_line" color="cyan" conError={!!(errores.nombre || errores.categoria || errores.horas)}>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Nombre" obligatorio error={errores.nombre} className="sm:col-span-2" ayuda="Usa el mismo nombre al renovarlo para que el anterior deje de avisar.">
                            <input value={d.nombre} onChange={(e) => poner("nombre", e.target.value)} placeholder="Ej.: PRL 20 h Albañilería" className={claseInput(errores.nombre)} autoFocus />
                        </Campo>
                        <Campo etiqueta="Categoría" obligatorio error={errores.categoria}>
                            <select value={d.categoria} onChange={(e) => poner("categoria", e.target.value)} className={claseInput(errores.categoria)}>
                                {Object.entries(CATEGORIAS_CURSO).map(([v, t]) => (
                                    <option key={v} value={v}>
                                        {t}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                        <Campo etiqueta="Horas" error={errores.horas}>
                            <input type="number" min={0} step="0.5" value={d.horas} onChange={(e) => poner("horas", e.target.value)} className={claseInput(errores.horas)} />
                        </Campo>
                        <Campo etiqueta="Entidad o centro" error={errores.entidad} className="sm:col-span-2">
                            <input value={d.entidad} onChange={(e) => poner("entidad", e.target.value)} placeholder="Ej.: Fundación Laboral de la Construcción" className={claseInput(errores.entidad)} />
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Fechas" descripcion="Realización y caducidad" icono="mgc_calendar_line" color="amber" conError={!!(errores.fecha || errores.fecha_caducidad)}>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Fecha del curso" obligatorio error={errores.fecha}>
                            <input type="date" value={d.fecha} onChange={(e) => poner("fecha", e.target.value)} className={claseInput(errores.fecha)} />
                        </Campo>
                        <Campo etiqueta="Caduca el" error={errores.fecha_caducidad} ayuda="Vacío si no caduca.">
                            <input type="date" min={d.fecha} value={d.fecha_caducidad} onChange={(e) => poner("fecha_caducidad", e.target.value)} className={claseInput(errores.fecha_caducidad)} />
                            {d.fecha && (
                                <div className="mt-2 flex flex-wrap gap-1">
                                    {[1, 3, 5].map((n) => (
                                        <button
                                            key={n}
                                            type="button"
                                            onClick={() => poner("fecha_caducidad", masAnios(d.fecha, n))}
                                            className="rounded-lg border border-slate-200 px-2 py-0.5 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                        >
                                            +{n} {n === 1 ? "año" : "años"}
                                        </button>
                                    ))}
                                    {d.fecha_caducidad && (
                                        <button type="button" onClick={() => poner("fecha_caducidad", "")} className="rounded-lg px-2 py-0.5 text-xs font-medium text-slate-500 hover:bg-slate-50">
                                            No caduca
                                        </button>
                                    )}
                                </div>
                            )}
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Certificado" descripcion="Opcional" icono="mgc_attachment_line" color="violet">
                    <CampoAdjunto
                        archivo={archivo}
                        actual={curso?.archivo}
                        texto="Adjuntar el certificado o diploma"
                        carpeta="Cursos y formación"
                        error={errores.archivo || errorArchivo}
                        onChange={(f, err) => {
                            setArchivo(f);
                            setErrorArchivo(err ?? null);
                        }}
                    />
                </SeccionFormulario>

                <SeccionFormulario titulo="Observaciones" descripcion="Opcional" icono="mgc_edit_line" color="slate">
                    <textarea rows={2} value={d.observaciones} onChange={(e) => poner("observaciones", e.target.value)} className={claseInput(errores.observaciones)} />
                </SeccionFormulario>
            </form>
        </Modal>
    );
}

/** Cursos y formación del empleado (pestaña de la ficha). */
export default function FormacionEmpleado({ empleado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState(null);
    const [modal, setModal] = useState(null);
    const [borrar, setBorrar] = useState(null);

    const cargar = async () => {
        try {
            const { data } = await api.get(`/rrhh/empleados/${empleado.id}/cursos`);
            setDatos(data);
        } catch (error) {
            showError(mensajeDeError(error, "Error al cargar la formación."));
        }
    };

    useEffect(() => {
        cargar();
    }, [empleado.id]);

    const eliminar = async () => {
        try {
            const { data } = await api.delete(`/rrhh/cursos/${borrar.id}`);
            showSuccess(data.message);
            setBorrar(null);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudo eliminar."));
        }
    };

    const avisos = (datos?.cursos ?? []).filter((c) => c.estado === "vencido" || c.estado === "proximo").length;

    return (
        <Panel
            titulo="Cursos y formación"
            icono="mgc_star_line"
            acciones={
                <button type="button" onClick={() => setModal({})} className={`${botonPrimario} flex-1 sm:flex-none`}>
                    <i className="mgc_add_line"></i> Nuevo curso
                </button>
            }
        >
            {!datos ? (
                <Cargando />
            ) : (
                <div className="space-y-5 px-5 py-5 sm:px-6">
                    <div className="grid grid-cols-3 gap-3 sm:max-w-xl">
                        <Cifra etiqueta="Cursos" valor={datos.cursos.length} />
                        <Cifra etiqueta="Horas" valor={numeroDias(datos.horas)} />
                        <Cifra etiqueta="Por renovar" valor={avisos} tono={avisos ? "amber" : "slate"} />
                    </div>

                    {datos.cursos.length === 0 ? (
                        <Vacio icono="mgc_star_line" titulo="Sin formación registrada" texto="Registra cursos de PRL, carnets y formación de oficio; te avisaremos antes de que caduquen." />
                    ) : (
                        <ul className="space-y-2">
                            {datos.cursos.map((c) => {
                                const est = ESTADOS_CURSO[c.estado] ?? ESTADOS_CURSO.sin_caducidad;
                                return (
                                    <li key={c.id} className={`flex flex-col gap-2 rounded-2xl border border-slate-200 px-4 py-3 sm:flex-row sm:items-center ${c.estado === "renovado" ? "opacity-60" : ""}`}>
                                        <div className="min-w-0 flex-1">
                                            <p className="break-words font-semibold text-slate-800">{c.nombre}</p>
                                            <p className="text-xs text-slate-500">
                                                {CATEGORIAS_CURSO[c.categoria]}
                                                {c.entidad && ` · ${c.entidad}`}
                                                {c.horas ? ` · ${numeroDias(c.horas)} h` : ""}
                                                {` · ${fechaCorta(c.fecha)}`}
                                                {c.fecha_caducidad && ` · caduca el ${fechaCorta(c.fecha_caducidad)}`}
                                                {c.fecha_caducidad && c.estado !== "renovado" && ` (${textoDias(c.dias)})`}
                                            </p>
                                        </div>
                                        <div className="flex items-center justify-between gap-2 sm:justify-end">
                                            <span className={`whitespace-nowrap rounded-full border px-2 py-0.5 text-[11px] font-semibold ${est.clases}`}>{est.texto}</span>
                                            <div className="flex items-center">
                                                {c.archivo && <BotonIcono icono="mgc_file_line" titulo="Ver certificado" href={`/drive/ver/${c.archivo.id}`} target="_blank" />}
                                                <BotonIcono icono="mgc_edit_line" titulo="Editar" onClick={() => setModal({ curso: c })} />
                                                <BotonIcono icono="mgc_delete_line" titulo="Eliminar" peligro onClick={() => setBorrar(c)} />
                                            </div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
            )}

            {modal && (
                <ModalCurso
                    empleado={empleado}
                    curso={modal.curso}
                    onCerrar={() => setModal(null)}
                    onGuardado={() => {
                        setModal(null);
                        cargar();
                    }}
                />
            )}
            {borrar && (
                <ModalConfirmar titulo="Eliminar curso" textoConfirmar="Eliminar" peligro onConfirmar={eliminar} onCerrar={() => setBorrar(null)}>
                    Se eliminará <strong>{borrar.nombre}</strong>. {borrar.archivo && "El certificado se conserva en su carpeta del Drive."}
                </ModalConfirmar>
            )}
        </Panel>
    );
}
