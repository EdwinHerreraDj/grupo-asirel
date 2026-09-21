import React, { useEffect, useMemo, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import SelectorObras from "./SelectorObras";
import { ModalAsignar, ModalCopiar, ModalDia } from "./ModalesCuadrante";
import { Cargando, Segmentado, Vacio } from "./Comunes";
import { DIAS_SEMANA, botonPrimario, botonSecundario, colorAusencia, fechaDiaMes, hoyISO, inputBase, lunesDe, numeroDias, sumarDias } from "../utils";

const dentro = (fecha, desde, hasta) => fecha >= desde && (!hasta || fecha <= hasta);
const RAYADO = "bg-[repeating-linear-gradient(45deg,#e2e8f0_0,#e2e8f0_2px,transparent_2px,transparent_5px)]";

/**
 * Cuadrante de turnos: turno y obra de cada empleado cada día (1 o 2
 * semanas). Pulsar una celda edita ese día.
 */
export default function Cuadrante({ onAbrirFicha }) {
    const { showError } = useNotification();
    const [desde, setDesde] = useState(() => lunesDe(hoyISO()));
    const [semanas, setSemanas] = useState("1");
    const [datos, setDatos] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [search, setSearch] = useState("");
    const [buscar, setBuscar] = useState("");
    const [obra, setObra] = useState(null);
    const [modal, setModal] = useState(null); // {tipo: dia|asignar|copiar, ...}

    const dias = semanas === "2" ? 14 : 7;
    const hasta = sumarDias(desde, dias - 1);

    const cargar = async () => {
        setCargando(true);
        try {
            const { data } = await api.get("/rrhh/cuadrante", { params: { desde, hasta, search: buscar || undefined, obra_id: obra?.id } });
            setDatos(data);
        } catch {
            showError("Error al cargar el cuadrante");
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        cargar();
    }, [desde, hasta, buscar, obra?.id]);

    useEffect(() => {
        const t = setTimeout(() => setBuscar(search.trim()), 350);
        return () => clearTimeout(t);
    }, [search]);

    const turnos = useMemo(() => Object.fromEntries((datos?.turnos ?? []).map((t) => [t.id, t])), [datos]);
    const tiposAusencia = useMemo(() => Object.fromEntries((datos?.tipos_ausencia ?? []).map((t) => [t.id, t])), [datos]);
    const hoy = hoyISO();

    const cerrarYRecargar = () => {
        setModal(null);
        cargar();
    };

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="space-y-3 border-b border-slate-200 px-4 py-4 sm:px-6">
                <div className="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div className="flex flex-wrap items-center gap-2">
                        <button type="button" onClick={() => setDesde(sumarDias(desde, -7))} className={`${botonSecundario} px-3`} aria-label="Semana anterior">
                            <i className="mgc_left_line"></i>
                        </button>
                        <h3 className="min-w-[11rem] text-center text-base font-semibold text-slate-900 sm:text-lg">
                            {fechaDiaMes(desde)} – {fechaDiaMes(hasta)} {hasta.slice(0, 4)}
                        </h3>
                        <button type="button" onClick={() => setDesde(sumarDias(desde, 7))} className={`${botonSecundario} px-3`} aria-label="Semana siguiente">
                            <i className="mgc_right_line"></i>
                        </button>
                        {desde !== lunesDe(hoy) && (
                            <button type="button" onClick={() => setDesde(lunesDe(hoy))} className={`${botonSecundario} px-3`}>
                                Esta semana
                            </button>
                        )}
                        <div className="w-40">
                            <Segmentado opciones={{ 1: "1 semana", 2: "2 semanas" }} value={semanas} onChange={setSemanas} />
                        </div>
                    </div>

                    <div className="flex flex-col gap-2 sm:flex-row">
                        <button type="button" onClick={() => setModal({ tipo: "copiar" })} className={botonSecundario} disabled={!datos}>
                            <i className="mgc_copy_2_line"></i> Copiar {semanas === "2" ? "2 semanas" : "semana"} anterior
                        </button>
                        <button type="button" onClick={() => setModal({ tipo: "asignar" })} className={botonPrimario} disabled={!datos?.empleados?.length}>
                            <i className="mgc_calendar_add_line"></i> Asignar turnos
                        </button>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:w-[34rem]">
                    <div className="relative">
                        <i className="mgc_search_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Buscar empleado…" className={`${inputBase} pl-9`} />
                    </div>
                    <SelectorObras value={obra} onChange={setObra} placeholder="Filtrar por obra…" />
                </div>

                {datos?.turnos?.length > 0 && (
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-600">
                        {datos.turnos.filter((t) => t.activo).map((t) => (
                            <span key={t.id} className="inline-flex items-center gap-1.5">
                                <span className={`h-3 w-3 rounded ${colorAusencia(t.color).celda}`}></span>
                                {t.nombre} <span className="text-slate-400">({t.horario})</span>
                            </span>
                        ))}
                        <span className="inline-flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded border border-rose-200 bg-rose-50"></span> Ausencia
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <span className={`h-3 w-3 rounded ${RAYADO}`}></span> Sin alta
                        </span>
                    </div>
                )}
            </div>

            {cargando && !datos ? (
                <Cargando texto="Cargando cuadrante…" />
            ) : datos?.empleados?.length === 0 ? (
                <Vacio icono="mgc_calendar_month_line" titulo="Nadie en estas fechas" texto={buscar || obra ? "Ningún empleado coincide con los filtros." : "No hay empleados de alta en estas fechas."} />
            ) : (
                <div className={`overflow-x-auto overscroll-x-contain ${cargando ? "opacity-60" : ""}`}>
                    <table className="border-separate border-spacing-0 text-xs">
                        <thead>
                            <tr>
                                <th className="sticky left-0 z-20 min-w-[10rem] border-b border-r border-slate-200 bg-white px-3 py-2 text-left font-semibold text-slate-500 sm:min-w-[15rem]">
                                    Empleado · horas
                                </th>
                                {datos?.dias.map((d) => (
                                    <th
                                        key={d.fecha}
                                        title={d.festivo ?? undefined}
                                        className={`min-w-[5.75rem] border-b border-l border-slate-100 px-1 py-1.5 text-center font-medium ${
                                            d.fecha === hoy ? "bg-cyan-600 text-white" : d.festivo ? "bg-amber-100 text-amber-800" : d.semana >= 6 ? "bg-slate-100 text-slate-500" : "bg-white text-slate-600"
                                        }`}
                                    >
                                        <span className="block text-[10px] uppercase">{DIAS_SEMANA[d.semana]}</span>
                                        <span className="block text-sm font-semibold">{d.dia}</span>
                                        {d.festivo && <span className="block truncate text-[10px] font-normal">Festivo</span>}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {datos?.empleados.map((e) => {
                                const porFecha = Object.fromEntries(e.asignaciones.map((a) => [a.fecha, a]));
                                const horas = e.asignaciones.reduce((s, a) => s + (turnos[a.turno_id]?.horas ?? 0), 0);
                                const objetivo = e.horas_semanales ? e.horas_semanales * (dias / 7) : null;
                                const tono = objetivo === null ? "text-slate-500" : horas > objetivo + 0.01 ? "text-rose-600" : horas < objetivo - 0.01 ? "text-amber-600" : "text-emerald-600";

                                return (
                                    <tr key={e.id} className="group">
                                        <td className="sticky left-0 z-10 border-b border-r border-slate-100 bg-white px-3 py-1.5 group-hover:bg-slate-50">
                                            <button type="button" onClick={() => onAbrirFicha(e.id)} className="block max-w-[9rem] truncate text-left text-sm font-medium text-slate-800 hover:text-cyan-700 sm:max-w-[14rem]" title={e.nombre}>
                                                {e.nombre}
                                            </button>
                                            <span className={`text-[11px] font-semibold ${tono}`} title="Horas planificadas / horas de contrato">
                                                {numeroDias(horas)} h{objetivo !== null && ` / ${numeroDias(objetivo)} h`}
                                            </span>
                                        </td>
                                        {datos.dias.map((d) => {
                                            const a = porFecha[d.fecha];
                                            const aus = e.ausencias.find((x) => dentro(d.fecha, x.desde, x.hasta));
                                            const deAlta = e.periodos.some((p) => dentro(d.fecha, p.desde, p.hasta));
                                            const t = a ? turnos[a.turno_id] : null;
                                            const fondo = d.festivo ? "bg-amber-50/60" : d.semana >= 6 ? "bg-slate-50" : "";

                                            let contenido;
                                            if (!deAlta) {
                                                contenido = <span className={`block h-full min-h-[2.5rem] w-full rounded ${RAYADO}`} title="No estaba de alta"></span>;
                                            } else if (aus) {
                                                const ta = tiposAusencia[aus.tipo_id];
                                                contenido = (
                                                    <span className={`flex min-h-[2.5rem] items-center justify-center rounded border px-1 text-center text-[10px] font-semibold leading-tight ${colorAusencia(ta?.color).suave}`} title={ta?.nombre}>
                                                        <span className="line-clamp-2">{ta?.nombre ?? "Ausencia"}</span>
                                                    </span>
                                                );
                                            } else {
                                                contenido = (
                                                    <button
                                                        type="button"
                                                        onClick={() => setModal({ tipo: "dia", empleado: e, fecha: d.fecha, asignacion: a })}
                                                        title={a ? `${t?.nombre} ${t?.horario}${a.obra ? ` · ${a.obra.nombre}` : ""}${a.observaciones ? ` · ${a.observaciones}` : ""}` : "Asignar turno"}
                                                        className={`flex min-h-[2.5rem] w-full flex-col justify-center rounded px-1.5 py-1 text-left transition ${
                                                            a ? `${colorAusencia(t?.color).celda} text-white hover:opacity-90` : "text-slate-300 hover:bg-cyan-50 hover:text-cyan-600"
                                                        }`}
                                                    >
                                                        {a ? (
                                                            <>
                                                                <span className="truncate text-[11px] font-semibold">{t?.nombre}</span>
                                                                {a.obra && <span className="truncate text-[10px] opacity-90">{a.obra.nombre}</span>}
                                                            </>
                                                        ) : (
                                                            <i className="mgc_add_line mx-auto text-sm opacity-0 group-hover:opacity-100"></i>
                                                        )}
                                                    </button>
                                                );
                                            }

                                            return (
                                                <td key={d.fecha} className={`border-b border-l border-slate-100 p-0.5 align-middle ${fondo}`}>
                                                    {contenido}
                                                </td>
                                            );
                                        })}
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}

            {modal?.tipo === "dia" && (
                <ModalDia empleado={modal.empleado} fecha={modal.fecha} asignacion={modal.asignacion} turnos={datos.turnos} onCerrar={() => setModal(null)} onGuardado={cerrarYRecargar} />
            )}
            {modal?.tipo === "asignar" && (
                <ModalAsignar empleados={datos.empleados} turnos={datos.turnos} desde={desde} hasta={hasta} onCerrar={() => setModal(null)} onGuardado={cerrarYRecargar} />
            )}
            {modal?.tipo === "copiar" && <ModalCopiar desde={desde} dias={dias} onCerrar={() => setModal(null)} onGuardado={cerrarYRecargar} />}
        </div>
    );
}
