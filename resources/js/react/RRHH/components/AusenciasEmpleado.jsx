import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import ModalAusencia from "./ModalAusencia";
import ModalConfirmar from "./ModalConfirmar";
import { Cargando, Vacio } from "./Comunes";
import { botonPrimario, colorAusencia, fechaCorta, inputBase, mensajeDeError, numeroDias } from "../utils";

/** Tarjeta de saldo anual (vacaciones, asuntos propios…). */
function Saldo({ s }) {
    const c = colorAusencia(s.color);
    const total = Math.max(s.devengados, s.disfrutados + s.programados, 0.0001);
    const pctDisfrutados = Math.min(100, (s.disfrutados / total) * 100);
    const pctProgramados = Math.min(100 - pctDisfrutados, (s.programados / total) * 100);
    const negativo = s.pendientes < 0;

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
            <div className="flex items-center gap-2">
                <span className={`h-2.5 w-2.5 rounded-full ${c.punto}`}></span>
                <p className="min-w-0 flex-1 truncate text-sm font-semibold text-slate-800">{s.tipo}</p>
                <span className="text-[11px] text-slate-400">{s.computo === "laborables" ? "días laborables" : "días naturales"}</span>
            </div>

            <p className={`mt-2 text-2xl font-bold ${negativo ? "text-rose-600" : "text-slate-900"}`}>
                {numeroDias(s.pendientes)}
                <span className="ml-1 text-sm font-medium text-slate-500">{negativo ? "días de más" : "pendientes"}</span>
            </p>

            <div className="mt-3 flex h-2 overflow-hidden rounded-full bg-slate-100">
                <div className={c.barra} style={{ width: `${pctDisfrutados}%` }}></div>
                <div className={c.barraSuave} style={{ width: `${pctProgramados}%` }}></div>
            </div>

            <dl className="mt-3 grid grid-cols-3 gap-2 text-xs">
                <div>
                    <dt className="text-slate-400">Le corresponden</dt>
                    <dd className="font-semibold text-slate-700">{numeroDias(s.devengados)}</dd>
                </div>
                <div>
                    <dt className="flex items-center gap-1 text-slate-400">
                        <span className={`h-1.5 w-1.5 rounded-full ${c.barra}`}></span> Disfrutados
                    </dt>
                    <dd className="font-semibold text-slate-700">{numeroDias(s.disfrutados)}</dd>
                </div>
                <div>
                    <dt className="flex items-center gap-1 text-slate-400">
                        <span className={`h-1.5 w-1.5 rounded-full ${c.barraSuave}`}></span> Programados
                    </dt>
                    <dd className="font-semibold text-slate-700">{numeroDias(s.programados)}</dd>
                </div>
            </dl>

            {(s.proporcional || s.personalizado) && (
                <p className="mt-2 text-[11px] text-slate-500">
                    {s.personalizado ? `${numeroDias(s.anuales)} días/año propios del empleado` : `${numeroDias(s.anuales)} días/año`}
                    {s.proporcional && " · proporcional al tiempo de alta en el año"}
                </p>
            )}
        </div>
    );
}

/** Ausencias y saldo anual del empleado (dentro de su ficha). */
export default function AusenciasEmpleado({ empleado }) {
    const { showSuccess, showError } = useNotification();
    const [anio, setAnio] = useState(new Date().getFullYear());
    const [datos, setDatos] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [modal, setModal] = useState(null); // {ausencia?, darAlta?}
    const [borrar, setBorrar] = useState(null);

    const cargar = async (a = anio) => {
        setCargando(true);
        try {
            const { data } = await api.get(`/rrhh/empleados/${empleado.id}/ausencias`, { params: { anio: a } });
            setDatos(data);
        } catch (error) {
            showError(mensajeDeError(error, "Error al cargar las ausencias."));
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        cargar(anio);
    }, [empleado.id, anio]);

    const eliminar = async () => {
        try {
            const { data } = await api.delete(`/rrhh/ausencias/${borrar.id}`);
            showSuccess(data.message);
            setBorrar(null);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudo eliminar."));
        }
    };

    return (
        <section className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <h3 className="flex items-center gap-2 font-semibold text-slate-900">
                    <i className="mgc_calendar_month_line text-lg text-cyan-600"></i>
                    Ausencias y vacaciones
                </h3>
                <div className="flex items-center gap-2">
                    <select value={anio} onChange={(e) => setAnio(Number(e.target.value))} className={`${inputBase} w-28`} aria-label="Año">
                        {(datos?.anios ?? [anio]).map((a) => (
                            <option key={a} value={a}>
                                {a}
                            </option>
                        ))}
                    </select>
                    <button type="button" onClick={() => setModal({})} className={`${botonPrimario} flex-1 sm:flex-none`} disabled={!datos}>
                        <i className="mgc_calendar_add_line"></i> Nueva ausencia
                    </button>
                </div>
            </div>

            {cargando && !datos ? (
                <Cargando />
            ) : (
                <div className={`space-y-5 px-5 py-5 sm:px-6 ${cargando ? "opacity-60" : ""}`}>
                    {datos?.saldos?.length > 0 && (
                        <div className="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                            {datos.saldos.map((s) => (
                                <Saldo key={s.tipo_id} s={s} />
                            ))}
                        </div>
                    )}

                    {datos?.resumen?.length > 0 && (
                        <div>
                            <p className="mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Resumen de {anio}</p>
                            <div className="flex flex-wrap gap-2">
                                {datos.resumen.map((r) => (
                                    <span key={r.tipo_id} className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium ${colorAusencia(r.color).suave}`}>
                                        {r.tipo}: <strong>{r.naturales}</strong> {r.naturales === 1 ? "día" : "días"}
                                        <span className="opacity-70">({r.laborables} lab.)</span>
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}

                    {datos?.ausencias?.length === 0 ? (
                        <Vacio icono="mgc_sun_line" titulo={`Sin ausencias en ${anio}`} texto="Registra vacaciones, bajas médicas o permisos con «Nueva ausencia»." />
                    ) : (
                        <ul className="space-y-2">
                            {datos?.ausencias?.map((a) => {
                                const c = colorAusencia(a.tipo?.color);
                                return (
                                    <li key={a.id} className="flex overflow-hidden rounded-2xl border border-slate-200">
                                        <span className={`w-1.5 shrink-0 ${c.barra}`}></span>
                                        <div className="flex min-w-0 flex-1 flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center">
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <p className="font-semibold text-slate-800">{a.tipo?.nombre}</p>
                                                    {a.abierta && (
                                                        <span className="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700">En curso</span>
                                                    )}
                                                    {a.tipo && !a.tipo.retribuida && (
                                                        <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">No retribuida</span>
                                                    )}
                                                </div>
                                                <p className="text-sm text-slate-600">
                                                    {a.abierta
                                                        ? `Desde el ${fechaCorta(a.fecha_inicio)}`
                                                        : a.fecha_inicio === a.fecha_fin
                                                          ? fechaCorta(a.fecha_inicio)
                                                          : `${fechaCorta(a.fecha_inicio)} → ${fechaCorta(a.fecha_fin)}`}
                                                    <span className="text-slate-400">
                                                        {" · "}
                                                        {a.dias.naturales} {a.dias.naturales === 1 ? "día" : "días"} ({a.dias.laborables} laborables)
                                                        {a.abierta && " hasta hoy"}
                                                    </span>
                                                </p>
                                                {a.observaciones && <p className="mt-0.5 whitespace-pre-line text-xs text-slate-500">{a.observaciones}</p>}
                                                {a.archivo && (
                                                    <a
                                                        href={`/drive/ver/${a.archivo.id}`}
                                                        target="_blank"
                                                        rel="noopener"
                                                        className="mt-1 inline-flex max-w-full items-center gap-1 text-xs font-medium text-cyan-700 hover:underline"
                                                    >
                                                        <i className="mgc_attachment_line"></i>
                                                        <span className="truncate">{a.archivo.nombre}</span>
                                                    </a>
                                                )}
                                            </div>
                                            <div className="flex shrink-0 items-center gap-1">
                                                {a.abierta && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setModal({ ausencia: a, darAlta: true })}
                                                        className="inline-flex h-8 items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
                                                    >
                                                        <i className="mgc_heartbeat_line"></i> Alta médica
                                                    </button>
                                                )}
                                                <button
                                                    type="button"
                                                    onClick={() => setModal({ ausencia: a })}
                                                    title="Editar"
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-cyan-700"
                                                >
                                                    <i className="mgc_edit_line"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setBorrar(a)}
                                                    title="Eliminar"
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700"
                                                >
                                                    <i className="mgc_delete_line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
            )}

            {modal && datos && (
                <ModalAusencia
                    empleado={empleado}
                    ausencia={modal.ausencia}
                    darAlta={modal.darAlta}
                    tipos={datos.tipos}
                    onCerrar={() => setModal(null)}
                    onGuardado={(r) => {
                        setModal(null);
                        const nuevoAnio = Number(r.ausencia?.fecha_inicio?.slice(0, 4));
                        if (nuevoAnio && nuevoAnio !== anio && !modal.ausencia) setAnio(nuevoAnio);
                        else cargar();
                    }}
                />
            )}

            {borrar && (
                <ModalConfirmar
                    titulo="Eliminar ausencia"
                    textoConfirmar="Eliminar"
                    peligro
                    onConfirmar={eliminar}
                    onCerrar={() => setBorrar(null)}
                >
                    <p>
                        Se eliminará <strong>{borrar.tipo?.nombre}</strong>{" "}
                        {borrar.abierta ? `desde el ${fechaCorta(borrar.fecha_inicio)}` : `del ${fechaCorta(borrar.fecha_inicio)} al ${fechaCorta(borrar.fecha_fin)}`}.
                    </p>
                    {borrar.archivo && <p className="mt-2 text-xs text-slate-500">El justificante se conserva en su carpeta del Drive.</p>}
                </ModalConfirmar>
            )}
        </section>
    );
}
