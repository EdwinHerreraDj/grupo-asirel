import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { formatEuro, formatNumero } from "../../shared/formato";
import FormularioEmpleado from "./FormularioEmpleado";
import { ModalBaja, ModalReingreso } from "./ModalesAltaBaja";
import SubirDocumento from "./SubirDocumento";
import AusenciasEmpleado from "./AusenciasEmpleado";
import { Cargando, EstadoDocumento, EstadoEmpleado, ObrasChips, Vacio } from "./Comunes";
import {
    botonPeligro,
    botonPrimario,
    botonSecundario,
    fechaCorta,
    ibanLegible,
    ibanOculto,
    mensajeDeError,
    textoDias,
} from "../utils";

function Tarjeta({ titulo, icono, accion, children, className = "" }) {
    return (
        <section className={`overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] ${className}`}>
            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-4 sm:px-6">
                <h3 className="flex items-center gap-2 font-semibold text-slate-900">
                    <i className={`${icono} text-lg text-cyan-600`}></i>
                    {titulo}
                </h3>
                {accion}
            </div>
            {children}
        </section>
    );
}

function Dato({ etiqueta, children, className = "" }) {
    const vacio = children === null || children === undefined || children === "";
    return (
        <div className={className}>
            <dt className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">{etiqueta}</dt>
            <dd className={`mt-0.5 break-words text-sm ${vacio ? "text-slate-400" : "text-slate-800"}`}>{vacio ? "—" : children}</dd>
        </div>
    );
}

function Iban({ iban }) {
    const [visible, setVisible] = useState(false);
    if (!iban) return null;
    return (
        <span className="inline-flex flex-wrap items-center gap-2">
            <span className="font-mono">{visible ? ibanLegible(iban) : ibanOculto(iban)}</span>
            <button
                type="button"
                onClick={() => setVisible((v) => !v)}
                className="text-xs font-semibold text-cyan-700 hover:text-cyan-800"
            >
                {visible ? "Ocultar" : "Mostrar"}
            </button>
        </span>
    );
}

export default function FichaEmpleado({ id, onVolver }) {
    const { showError } = useNotification();

    const [ficha, setFicha] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [modal, setModal] = useState(null); // editar | baja | reingreso | {subir: apartado}
    const [abiertos, setAbiertos] = useState({});

    const cargar = async () => {
        try {
            const { data } = await api.get(`/rrhh/empleados/${id}`);
            setFicha(data);
        } catch (error) {
            if (error.response?.status === 404) {
                showError("El empleado no existe.");
                onVolver();
                return;
            }
            showError(mensajeDeError(error, "Error al cargar la ficha."));
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        setCargando(true);
        cargar();
    }, [id]);

    const actualizar = (data) => {
        setModal(null);
        if (data?.empleado) setFicha(data);
        else cargar();
    };

    if (cargando) {
        return (
            <div className="rounded-3xl border border-slate-200 bg-white">
                <Cargando texto="Cargando ficha…" />
            </div>
        );
    }

    if (!ficha) {
        return (
            <div className="rounded-3xl border border-slate-200 bg-white">
                <Vacio icono="mgc_warning_line" titulo="No se pudo cargar la ficha">
                    <button type="button" onClick={onVolver} className={botonSecundario}>
                        Volver al listado
                    </button>
                </Vacio>
            </div>
        );
    }

    const { empleado: e, documentacion, resumen_documentacion: resumen, carpeta, opciones } = ficha;
    const activo = e.estado === "activo";
    const periodoAbierto = e.periodos?.find((p) => !p.fecha_baja);
    const texto = (lista, valor) => (valor ? lista?.[valor] ?? valor : null);

    return (
        <div>
            <div className="space-y-4">
                {/* CABECERA */}
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                    <div className="bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                        <button
                            type="button"
                            onClick={onVolver}
                            className="mb-4 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                        >
                            <i className="mgc_arrow_left_line text-lg"></i>
                            Empleados
                        </button>

                        <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                            <div className="flex min-w-0 items-center gap-4">
                                <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-lg font-bold text-white">
                                    {(e.nombre?.[0] || "") + (e.apellidos?.[0] || "")}
                                </div>
                                <div className="min-w-0">
                                    <h2 className="break-words text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">
                                        {e.nombre} {e.apellidos}
                                    </h2>
                                    <div className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                                        <span className="font-mono">{e.dni}</span>
                                        {e.puesto && <span>{e.puesto}</span>}
                                        <EstadoEmpleado estado={e.estado} />
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-2 sm:flex sm:flex-wrap sm:items-center">
                                {carpeta && (
                                    <a href={`/empresa/drive-app?carpeta=${carpeta.id}`} className={botonSecundario} title={carpeta.ruta}>
                                        <i className="mgc_folder_open_line"></i> Abrir en el Drive
                                    </a>
                                )}
                                <button type="button" onClick={() => setModal("editar")} className={botonSecundario}>
                                    <i className="mgc_edit_line"></i> Editar ficha
                                </button>
                                {activo ? (
                                    <button type="button" onClick={() => setModal("baja")} className={botonPeligro}>
                                        <i className="mgc_user_remove_line"></i> Dar de baja
                                    </button>
                                ) : (
                                    <button type="button" onClick={() => setModal("reingreso")} className={botonPrimario}>
                                        <i className="mgc_user_follow_line"></i> Reincorporar
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 xl:grid-cols-5">
                    {/* DOCUMENTACIÓN */}
                    <Tarjeta
                        titulo="Documentación"
                        icono="mgc_file_check_line"
                        className="xl:col-span-3"
                        accion={
                            activo && resumen?.total > 0 ? (
                                <span className="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                    {resumen.total} {resumen.total === 1 ? "pendiente" : "pendientes"}
                                </span>
                            ) : activo ? (
                                <span className="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    Al día
                                </span>
                            ) : null
                        }
                    >
                        {documentacion.length === 0 ? (
                            <Vacio
                                icono="mgc_file_line"
                                titulo="No hay tipos de documento activos"
                                texto="Configúralos en la pestaña «Tipos de documento»."
                            />
                        ) : (
                            <ul className="divide-y divide-slate-100">
                                {documentacion.map((a) => {
                                    const abierto = !!abiertos[a.tipo_id];
                                    return (
                                        <li key={a.tipo_id} className="px-4 py-3 sm:px-6">
                                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <button
                                                    type="button"
                                                    onClick={() => setAbiertos((p) => ({ ...p, [a.tipo_id]: !abierto }))}
                                                    className="flex min-w-0 items-center gap-2 text-left"
                                                    disabled={!a.archivos_count}
                                                >
                                                    <i className={`mgc_right_line text-slate-400 transition ${abierto ? "rotate-90" : ""} ${a.archivos_count ? "" : "invisible"}`}></i>
                                                    <span className="min-w-0">
                                                        <span className="block break-words font-medium text-slate-800">
                                                            {a.tipo}
                                                            {!a.obligatorio && <span className="ml-1 text-xs font-normal text-slate-400">(opcional)</span>}
                                                        </span>
                                                        <span className="block text-xs text-slate-500">
                                                            {a.archivos_count
                                                                ? `${a.archivos_count} ${a.archivos_count === 1 ? "archivo" : "archivos"}`
                                                                : "Sin archivos"}
                                                            {a.caduca && ` · caduca el ${fechaCorta(a.caduca)} (${textoDias(a.dias)})`}
                                                        </span>
                                                    </span>
                                                </button>
                                                <div className="flex items-center gap-2 pl-6 sm:pl-0">
                                                    <EstadoDocumento estado={a.estado} />
                                                    {a.folder_id && (
                                                        <button
                                                            type="button"
                                                            onClick={() => setModal({ subir: a })}
                                                            className="inline-flex h-8 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-cyan-700 transition hover:bg-cyan-50"
                                                        >
                                                            <i className="mgc_upload_2_line"></i> Subir
                                                        </button>
                                                    )}
                                                </div>
                                            </div>

                                            {abierto && a.archivos?.length > 0 && (
                                                <ul className="mt-2 space-y-1 pl-6">
                                                    {a.archivos.map((f) => (
                                                        <li key={f.id} className="flex flex-col gap-1 rounded-xl bg-slate-50 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                                            <div className="min-w-0">
                                                                <p className="break-all text-sm text-slate-700">{f.nombre}</p>
                                                                <p className="text-xs text-slate-500">
                                                                    Subido el {fechaCorta(f.created_at)}
                                                                    {f.usuario?.name && ` por ${f.usuario.name}`}
                                                                    {f.fecha_caducidad && ` · caduca el ${fechaCorta(f.fecha_caducidad)}`}
                                                                </p>
                                                            </div>
                                                            <div className="flex shrink-0 gap-1">
                                                                <a
                                                                    href={`/drive/ver/${f.id}`}
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                    title="Ver"
                                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-cyan-700"
                                                                >
                                                                    <i className="mgc_eye_line"></i>
                                                                </a>
                                                                <a
                                                                    href={`/api/files/${f.id}/download`}
                                                                    title="Descargar"
                                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-cyan-700"
                                                                >
                                                                    <i className="mgc_download_line"></i>
                                                                </a>
                                                            </div>
                                                        </li>
                                                    ))}
                                                </ul>
                                            )}
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                        {carpeta && (
                            <p className="border-t border-slate-100 px-4 py-3 text-xs text-slate-500 sm:px-6">
                                <i className="mgc_folder_open_line mr-1"></i>
                                {carpeta.ruta}. Para borrar o renombrar archivos, usa el Drive.
                            </p>
                        )}
                    </Tarjeta>

                    {/* HISTORIAL */}
                    <Tarjeta titulo="Historial de altas y bajas" icono="mgc_history_line" className="xl:col-span-2">
                        <ol className="space-y-3 px-5 py-4 sm:px-6">
                            {(e.periodos || []).map((p) => (
                                <li key={p.id} className="relative rounded-2xl border border-slate-200 px-4 py-3">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <p className="text-sm font-semibold text-slate-800">
                                            {fechaCorta(p.fecha_alta)} → {p.fecha_baja ? fechaCorta(p.fecha_baja) : "actualidad"}
                                        </p>
                                        {!p.fecha_baja && (
                                            <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">En curso</span>
                                        )}
                                    </div>
                                    {p.tipo_contrato && (
                                        <p className="mt-0.5 text-xs text-slate-500">Contrato: {texto(opciones?.tipos_contrato, p.tipo_contrato)}</p>
                                    )}
                                    {p.motivo_baja && (
                                        <p className="mt-0.5 text-xs text-slate-500">Motivo de baja: {texto(opciones?.motivos_baja, p.motivo_baja)}</p>
                                    )}
                                    {p.observaciones_baja && <p className="mt-1 whitespace-pre-line text-xs text-slate-600">{p.observaciones_baja}</p>}
                                </li>
                            ))}
                            {!e.periodos?.length && <p className="text-sm text-slate-400">Sin periodos registrados.</p>}
                        </ol>
                    </Tarjeta>
                </div>

                {/* AUSENCIAS */}
                <AusenciasEmpleado key={`${e.id}-${e.estado}`} empleado={e} />

                {/* DATOS */}
                <Tarjeta titulo="Datos del empleado" icono="mgc_user_3_line">
                    <div className="grid grid-cols-1 gap-6 px-5 py-5 sm:px-6 lg:grid-cols-2">
                        <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Dato etiqueta="Nº Seguridad Social"><span className="font-mono">{e.nss}</span></Dato>
                            <Dato etiqueta="Fecha de nacimiento">{e.fecha_nacimiento && fechaCorta(e.fecha_nacimiento)}</Dato>
                            <Dato etiqueta="Teléfono">{e.telefono && <a href={`tel:${e.telefono}`} className="text-cyan-700 hover:underline">{e.telefono}</a>}</Dato>
                            <Dato etiqueta="Email">{e.email && <a href={`mailto:${e.email}`} className="break-all text-cyan-700 hover:underline">{e.email}</a>}</Dato>
                            <Dato etiqueta="Dirección" className="sm:col-span-2">
                                {[e.direccion, [e.codigo_postal, e.poblacion].filter(Boolean).join(" "), e.provincia].filter(Boolean).join(", ")}
                            </Dato>
                            <Dato etiqueta="Contacto de emergencia" className="sm:col-span-2">
                                {e.contacto_emergencia_nombre &&
                                    [e.contacto_emergencia_nombre, e.contacto_emergencia_relacion && `(${e.contacto_emergencia_relacion})`, e.contacto_emergencia_telefono]
                                        .filter(Boolean)
                                        .join(" ")}
                            </Dato>
                        </dl>
                        <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Dato etiqueta="Puesto">{e.puesto}</Dato>
                            <Dato etiqueta="Categoría (convenio)">{e.categoria_convenio}</Dato>
                            <Dato etiqueta="Tipo de contrato">{texto(opciones?.tipos_contrato, e.tipo_contrato)}</Dato>
                            <Dato etiqueta="Jornada">
                                {[texto(opciones?.jornadas, e.jornada), e.horas_semanales && `${formatNumero(e.horas_semanales)} h/semana`].filter(Boolean).join(" · ")}
                            </Dato>
                            <Dato etiqueta="Obras" className="sm:col-span-2">
                                {e.obras?.length ? <ObrasChips obras={e.obras} /> : null}
                            </Dato>
                            <Dato etiqueta="Alta actual">{periodoAbierto && fechaCorta(periodoAbierto.fecha_alta)}</Dato>
                            <Dato etiqueta="Salario bruto anual">{e.salario_bruto_anual !== null && e.salario_bruto_anual !== undefined && formatEuro(e.salario_bruto_anual)}</Dato>
                            <Dato etiqueta="IBAN"><Iban iban={e.iban} /></Dato>
                            <Dato etiqueta="Vacaciones al año">
                                {e.dias_vacaciones_anuales !== null && e.dias_vacaciones_anuales !== undefined
                                    ? `${formatNumero(e.dias_vacaciones_anuales)} días (propios del empleado)`
                                    : opciones?.vacaciones
                                      ? `${formatNumero(opciones.vacaciones.dias_anuales)} días ${opciones.vacaciones.computo} (general)`
                                      : null}
                            </Dato>
                        </dl>
                        {e.observaciones && (
                            <Dato etiqueta="Observaciones" className="lg:col-span-2">
                                <span className="whitespace-pre-line">{e.observaciones}</span>
                            </Dato>
                        )}
                    </div>
                </Tarjeta>
            </div>

            {modal === "editar" && (
                <FormularioEmpleado
                    empleado={e}
                    opciones={opciones}
                    onCerrar={() => setModal(null)}
                    onGuardado={actualizar}
                />
            )}
            {modal === "baja" && (
                <ModalBaja empleado={e} opciones={opciones} onCerrar={() => setModal(null)} onHecho={actualizar} />
            )}
            {modal === "reingreso" && (
                <ModalReingreso empleado={e} opciones={opciones} onCerrar={() => setModal(null)} onHecho={actualizar} />
            )}
            {modal?.subir && (
                <SubirDocumento
                    apartado={modal.subir}
                    empleado={e}
                    onCerrar={() => setModal(null)}
                    onSubido={() => {
                        setModal(null);
                        setAbiertos((p) => ({ ...p, [modal.subir.tipo_id]: true }));
                        cargar();
                    }}
                />
            )}
        </div>
    );
}
