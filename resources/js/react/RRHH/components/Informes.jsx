import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { formatEuro, formatNumero } from "../../shared/formato";
import { Cargando, Cifra, Panel, Segmentado } from "./Comunes";
import { MESES, botonSecundario, colorAusencia, hoyISO, inputBase, numeroDias } from "../utils";

/** Barras horizontales sencillas. */
function Barras({ filas, valor, etiqueta, formato = (v) => v, color = () => "bg-cyan-500" }) {
    const max = Math.max(...filas.map(valor), 0.0001);
    return (
        <ul className="space-y-2">
            {filas.map((f, i) => (
                <li key={i} className="text-sm">
                    <div className="mb-0.5 flex justify-between gap-2">
                        <span className="min-w-0 truncate text-slate-700" title={etiqueta(f)}>
                            {etiqueta(f)}
                        </span>
                        <span className="shrink-0 font-semibold text-slate-800">{formato(valor(f))}</span>
                    </div>
                    <div className="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div className={`h-full rounded-full ${color(f)}`} style={{ width: `${(valor(f) / max) * 100}%` }}></div>
                    </div>
                </li>
            ))}
        </ul>
    );
}

const EXPORTACIONES = [
    { tipo: "plantilla", titulo: "Plantilla", texto: "Datos de los empleados, contrato, obras y fin de contrato.", params: "estado", icono: "mgc_group_line" },
    { tipo: "altas-bajas", titulo: "Altas y bajas", texto: "Periodos que empiezan o terminan en las fechas, con motivo.", params: "rango", icono: "mgc_user_follow_line" },
    { tipo: "ausencias", titulo: "Ausencias", texto: "Vacaciones, bajas y permisos con días naturales y laborables.", params: "rango", icono: "mgc_calendar_month_line" },
    { tipo: "vacaciones", titulo: "Saldo de vacaciones", texto: "Días que corresponden, disfrutados, programados y pendientes.", params: "anio", icono: "mgc_sun_line" },
    { tipo: "nominas", titulo: "Nóminas", texto: "Importes de todas las nóminas del año y su estado de pago.", params: "anio", icono: "mgc_currency_euro_line" },
    { tipo: "horas-obra", titulo: "Horas por obra", texto: "Días y horas planificadas en el cuadrante por obra y empleado.", params: "rango", icono: "mgc_building_2_line" },
    { tipo: "formacion", titulo: "Formación", texto: "Todos los cursos con su caducidad y estado.", params: null, icono: "mgc_star_line" },
];

/** Tarjeta de una exportación a Excel. */
function Exportacion({ e, anioBase }) {
    const [desde, setDesde] = useState(`${anioBase}-01-01`);
    const [hasta, setHasta] = useState(hoyISO().slice(0, 4) === String(anioBase) ? hoyISO() : `${anioBase}-12-31`);
    const [anio, setAnio] = useState(anioBase);
    const [estado, setEstado] = useState("activo");

    useEffect(() => {
        setAnio(anioBase);
        setDesde(`${anioBase}-01-01`);
        setHasta(hoyISO().slice(0, 4) === String(anioBase) ? hoyISO() : `${anioBase}-12-31`);
    }, [anioBase]);

    const valido = e.params !== "rango" || (desde && hasta && desde <= hasta);
    const query = new URLSearchParams(
        e.params === "rango" ? { desde, hasta } : e.params === "anio" ? { anio } : e.params === "estado" ? { estado } : {},
    ).toString();

    return (
        <div className="flex flex-col rounded-2xl border border-slate-200 p-4">
            <div className="flex items-start gap-3">
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                    <i className={`${e.icono} text-lg`}></i>
                </span>
                <div className="min-w-0">
                    <p className="font-semibold text-slate-800">{e.titulo}</p>
                    <p className="text-xs text-slate-500">{e.texto}</p>
                </div>
            </div>

            <div className="mt-3 flex-1">
                {e.params === "rango" && (
                    <div className="grid grid-cols-2 gap-2">
                        <input type="date" value={desde} onChange={(ev) => setDesde(ev.target.value)} className={inputBase} aria-label="Desde" />
                        <input type="date" value={hasta} min={desde} onChange={(ev) => setHasta(ev.target.value)} className={inputBase} aria-label="Hasta" />
                    </div>
                )}
                {e.params === "anio" && (
                    <input type="number" min={2000} max={2100} value={anio} onChange={(ev) => setAnio(ev.target.value)} className={`${inputBase} w-28`} aria-label="Año" />
                )}
                {e.params === "estado" && <Segmentado opciones={{ activo: "De alta", baja: "De baja", todos: "Todos" }} value={estado} onChange={setEstado} />}
            </div>

            <a
                href={valido ? `/api/rrhh/informes/exportar/${e.tipo}${query ? `?${query}` : ""}` : undefined}
                aria-disabled={!valido}
                className={`${botonSecundario} mt-3 ${valido ? "" : "pointer-events-none opacity-50"}`}
            >
                <i className="mgc_download_line"></i> Descargar Excel
            </a>
        </div>
    );
}

/** Cifras del año y exportaciones a Excel. */
export default function Informes() {
    const { showError } = useNotification();
    const [anio, setAnio] = useState(new Date().getFullYear());
    const [datos, setDatos] = useState(null);

    useEffect(() => {
        setDatos(null);
        api.get("/rrhh/informes/resumen", { params: { anio } })
            .then(({ data }) => setDatos(data))
            .catch(() => showError("Error al cargar los informes"));
    }, [anio]);

    const anios = [];
    for (let a = new Date().getFullYear(); a >= new Date().getFullYear() - 5; a--) anios.push(a);

    return (
        <div className="space-y-4">
            <Panel
                titulo={`Cifras de ${anio}`}
                icono="mgc_chart_bar_line"
                acciones={
                    <select value={anio} onChange={(e) => setAnio(Number(e.target.value))} className={`${inputBase} w-28`} aria-label="Año">
                        {anios.map((a) => (
                            <option key={a} value={a}>
                                {a}
                            </option>
                        ))}
                    </select>
                }
            >
                {!datos ? (
                    <Cargando />
                ) : (
                    <div className="space-y-6 px-5 py-5 sm:px-6">
                        <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                            <Cifra etiqueta="Plantilla hoy" valor={datos.plantilla} tono="cyan" />
                            <Cifra etiqueta="Altas" valor={datos.altas} tono="emerald" />
                            <Cifra etiqueta="Bajas" valor={datos.bajas} />
                            <Cifra etiqueta="Absentismo" valor={`${formatNumero(datos.absentismo)} %`} tono={datos.absentismo > 5 ? "rose" : datos.absentismo > 3 ? "amber" : "slate"} detalle={`${datos.dias_baja_medica} días laborables de baja médica`} />
                            <Cifra etiqueta="Bruto en nóminas" valor={formatEuro(datos.nominas.bruto)} detalle={`${datos.nominas.registradas} nóminas`} />
                            <Cifra etiqueta="Horas planificadas" valor={numeroDias(datos.horas_planificadas)} detalle="en el cuadrante" />
                        </div>

                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <div>
                                <p className="mb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Bruto en nóminas por mes</p>
                                <div className="flex h-40 items-end gap-1">
                                    {datos.nominas.por_mes.map((m) => {
                                        const max = Math.max(...datos.nominas.por_mes.map((x) => x.bruto), 0.0001);
                                        return (
                                            <div key={m.mes} className="flex h-full flex-1 flex-col items-center justify-end gap-1" title={`${MESES[m.mes - 1]}: ${formatEuro(m.bruto)}`}>
                                                <div className="w-full rounded-t bg-cyan-500" style={{ height: `${(m.bruto / max) * 100}%`, minHeight: m.bruto ? "4px" : 0 }}></div>
                                                <span className="text-[10px] text-slate-400">{MESES[m.mes - 1][0]}</span>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            <div>
                                <p className="mb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Horas por obra</p>
                                {datos.horas_obra.length ? (
                                    <Barras filas={datos.horas_obra} valor={(f) => f.horas} etiqueta={(f) => f.obra} formato={(v) => `${numeroDias(v)} h`} color={() => "bg-emerald-500"} />
                                ) : (
                                    <p className="text-sm text-slate-400">Sin turnos asignados en {anio}.</p>
                                )}
                            </div>

                            <div>
                                <p className="mb-3 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Días de ausencia por tipo</p>
                                {datos.ausencias_por_tipo.length ? (
                                    <Barras filas={datos.ausencias_por_tipo} valor={(f) => f.dias} etiqueta={(f) => f.tipo} formato={(v) => `${v} días`} color={(f) => colorAusencia(f.color).barra} />
                                ) : (
                                    <p className="text-sm text-slate-400">Sin ausencias en {anio}.</p>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </Panel>

            <Panel titulo="Exportar a Excel" icono="mgc_download_line">
                <div className="grid grid-cols-1 gap-3 px-5 py-5 sm:px-6 md:grid-cols-2 xl:grid-cols-3">
                    {EXPORTACIONES.map((e) => (
                        <Exportacion key={e.tipo} e={e} anioBase={anio} />
                    ))}
                </div>
            </Panel>
        </div>
    );
}
