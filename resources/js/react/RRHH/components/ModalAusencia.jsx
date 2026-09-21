import React, { useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Aviso, Campo, Modal, SeccionFormulario, claseInput } from "./Comunes";
import {
    botonPrimario,
    botonSecundario,
    colorAusencia,
    diasNaturales,
    erroresDeValidacion,
    fechaCorta,
    hoyISO,
    mensajeDeError,
} from "../utils";

const MAX_MB = 50;

/**
 * Alta o edición de una ausencia. `darAlta` abre una baja médica abierta con
 * la fecha de fin (alta médica) ya puesta en hoy.
 */
export default function ModalAusencia({ empleado, ausencia = null, tipos, fechaInicial = null, darAlta = false, onCerrar, onGuardado }) {
    const { showSuccess, showError, showWarning } = useNotification();

    const disponibles = tipos.filter((t) => t.activo || t.id === ausencia?.rrhh_tipo_ausencia_id);
    const [tipoId, setTipoId] = useState(ausencia?.rrhh_tipo_ausencia_id ?? disponibles[0]?.id ?? null);
    const [inicio, setInicio] = useState(ausencia?.fecha_inicio ?? fechaInicial ?? hoyISO());
    const [fin, setFin] = useState(darAlta ? hoyISO() : ausencia?.fecha_fin ?? fechaInicial ?? "");
    const [abierta, setAbierta] = useState(!darAlta && !!ausencia?.abierta);
    const [observaciones, setObservaciones] = useState(ausencia?.observaciones ?? "");
    const [archivo, setArchivo] = useState(null);
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);

    const tipo = disponibles.find((t) => t.id === tipoId);
    const puedeQuedarAbierta = !!tipo?.es_baja_medica;
    const sinFin = puedeQuedarAbierta && abierta;
    const dias = diasNaturales(inicio, sinFin ? null : fin);

    const guardar = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});

        const form = new FormData();
        form.append("rrhh_tipo_ausencia_id", tipoId ?? "");
        form.append("fecha_inicio", inicio);
        form.append("fecha_fin", sinFin ? "" : fin);
        form.append("observaciones", observaciones);
        if (archivo) form.append("justificante", archivo);
        if (ausencia) form.append("_method", "PUT");

        try {
            const url = ausencia ? `/rrhh/ausencias/${ausencia.id}` : `/rrhh/empleados/${empleado.id}/ausencias`;
            const { data } = await api.post(url, form, { headers: { "Content-Type": "multipart/form-data" } });
            showSuccess(data.message);
            if (data.aviso) showWarning(data.aviso, 8000);
            onGuardado(data);
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            if (!Object.keys(campos).length) showError(mensajeDeError(error, "No se pudo guardar la ausencia."));
        } finally {
            setGuardando(false);
        }
    };

    return (
        <Modal
            etiqueta={darAlta ? "Alta médica" : ausencia ? "Editar ausencia" : "Nueva ausencia"}
            titulo={empleado.nombre_completo}
            onCerrar={onCerrar}
            ancho="max-w-2xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={guardando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-ausencia" className={botonPrimario} disabled={guardando || !tipoId}>
                        {guardando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        {darAlta ? "Registrar alta médica" : "Guardar"}
                    </button>
                </>
            }
        >
            <form id="form-ausencia" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario
                    titulo="Tipo de ausencia"
                    descripcion="Vacaciones, baja médica, permiso…"
                    icono="mgc_tag_line"
                    color="cyan"
                    conError={!!errores.rrhh_tipo_ausencia_id}
                >
                    {disponibles.length === 0 ? (
                        <Aviso tipo="aviso">No hay tipos de ausencia activos. Créalos en Configuración.</Aviso>
                    ) : (
                        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            {disponibles.map((t) => {
                                const c = colorAusencia(t.color);
                                const marcado = t.id === tipoId;
                                return (
                                    <button
                                        key={t.id}
                                        type="button"
                                        onClick={() => {
                                            setTipoId(t.id);
                                            if (!t.es_baja_medica) setAbierta(false);
                                        }}
                                        className={`flex items-center gap-3 rounded-xl border px-3 py-2.5 text-left transition ${
                                            marcado ? "border-cyan-500 bg-cyan-50/60 ring-1 ring-cyan-500" : "border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50"
                                        }`}
                                    >
                                        <span className={`h-3 w-3 shrink-0 rounded-full ${c.punto}`}></span>
                                        <span className="min-w-0 flex-1">
                                            <span className="block text-sm font-medium text-slate-800">{t.nombre}</span>
                                            <span className="block text-[11px] text-slate-500">
                                                {[
                                                    t.es_vacaciones && "Descuenta vacaciones",
                                                    t.es_baja_medica && "Baja médica",
                                                    t.dias_anuales !== null && !t.es_vacaciones && `${t.dias_anuales} días/año`,
                                                    !t.retribuida && "No retribuida",
                                                ]
                                                    .filter(Boolean)
                                                    .join(" · ") || "Retribuida"}
                                            </span>
                                        </span>
                                        {marcado && <i className="mgc_check_circle_line text-lg text-cyan-600"></i>}
                                    </button>
                                );
                            })}
                        </div>
                    )}
                    {errores.rrhh_tipo_ausencia_id && <p className="mt-2 text-xs text-red-600">{errores.rrhh_tipo_ausencia_id}</p>}
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Fechas"
                    descripcion={dias ? `${dias} ${dias === 1 ? "día natural" : "días naturales"}` : "Ambos días incluidos"}
                    icono="mgc_calendar_line"
                    color="amber"
                    conError={!!(errores.fecha_inicio || errores.fecha_fin)}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Desde" obligatorio error={errores.fecha_inicio}>
                            <input
                                type="date"
                                value={inicio}
                                onChange={(e) => {
                                    setInicio(e.target.value);
                                    if (fin && e.target.value > fin) setFin(e.target.value);
                                }}
                                className={claseInput(errores.fecha_inicio)}
                            />
                        </Campo>
                        <Campo
                            etiqueta={puedeQuedarAbierta ? "Hasta (alta médica)" : "Hasta"}
                            obligatorio={!sinFin}
                            error={errores.fecha_fin}
                        >
                            <input
                                type="date"
                                value={sinFin ? "" : fin}
                                min={inicio}
                                disabled={sinFin}
                                onChange={(e) => setFin(e.target.value)}
                                className={claseInput(errores.fecha_fin, sinFin ? "bg-slate-100 text-slate-400" : "")}
                            />
                        </Campo>
                    </div>

                    {puedeQuedarAbierta && (
                        <label className="mt-3 flex items-start gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                checked={abierta}
                                onChange={(e) => setAbierta(e.target.checked)}
                                className="mt-0.5 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                            />
                            <span>
                                Sigue de baja (todavía sin alta médica)
                                <span className="block text-xs text-slate-500">
                                    Cuando le den el alta, edita la ausencia y pon la fecha.
                                </span>
                            </span>
                        </label>
                    )}
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Justificante"
                    descripcion={tipo?.requiere_justificante ? "Recomendado para este tipo (parte de baja, certificado…)" : "Opcional"}
                    icono="mgc_attachment_line"
                    color="violet"
                    conError={!!errores.justificante}
                >
                    {ausencia?.archivo && !archivo && (
                        <a
                            href={`/drive/ver/${ausencia.archivo.id}`}
                            target="_blank"
                            rel="noopener"
                            className="mb-3 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-cyan-700 hover:bg-cyan-50"
                        >
                            <i className="mgc_file_line"></i>
                            <span className="min-w-0 flex-1 break-all">{ausencia.archivo.nombre}</span>
                            <i className="mgc_eye_line"></i>
                        </a>
                    )}
                    <label className="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 px-4 py-3 transition hover:border-cyan-400 hover:bg-cyan-50/40">
                        <i className="mgc_upload_2_line text-xl text-cyan-600"></i>
                        <span className="min-w-0 flex-1 text-sm">
                            <span className="block break-all font-medium text-slate-700">
                                {archivo ? archivo.name : ausencia?.archivo ? "Sustituir el justificante" : "Adjuntar justificante"}
                            </span>
                            <span className="block text-xs text-slate-500">Se guarda en su carpeta del Drive («Ausencias y bajas») · máx. {MAX_MB} MB</span>
                        </span>
                        {archivo && (
                            <button
                                type="button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    setArchivo(null);
                                }}
                                aria-label="Quitar archivo"
                                className="text-slate-400 hover:text-rose-600"
                            >
                                <i className="mgc_close_line"></i>
                            </button>
                        )}
                        <input
                            type="file"
                            className="sr-only"
                            onChange={(e) => {
                                const f = e.target.files?.[0] ?? null;
                                if (f && f.size > MAX_MB * 1024 * 1024) {
                                    setErrores((p) => ({ ...p, justificante: `El archivo supera ${MAX_MB} MB.` }));
                                    return;
                                }
                                setErrores((p) => ({ ...p, justificante: null }));
                                setArchivo(f);
                            }}
                        />
                    </label>
                    {errores.justificante && <p className="mt-1 text-xs text-red-600">{errores.justificante}</p>}
                </SeccionFormulario>

                <SeccionFormulario titulo="Observaciones" descripcion="Opcional" icono="mgc_edit_line" color="slate" conError={!!errores.observaciones}>
                    <textarea
                        rows={2}
                        value={observaciones}
                        onChange={(e) => setObservaciones(e.target.value)}
                        placeholder="Ej.: nº de parte, motivo del permiso…"
                        className={claseInput(errores.observaciones)}
                    />
                </SeccionFormulario>

                {ausencia?.abierta && darAlta && (
                    <p className="text-xs text-slate-500">
                        Baja médica desde el {fechaCorta(ausencia.fecha_inicio)}. Revisa la fecha del alta médica antes de guardar.
                    </p>
                )}
            </form>
        </Modal>
    );
}
