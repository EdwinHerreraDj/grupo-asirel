import React, { useEffect, useMemo, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import ModalAusencia from "./ModalAusencia";
import SelectorObras from "./SelectorObras";
import { Cargando, Vacio } from "./Comunes";
import { DIAS_SEMANA, MESES, botonSecundario, colorAusencia, fechaCorta, hoyISO, inputBase } from "../utils";

/** ¿La fecha ISO está dentro de [desde, hasta] (hasta null = abierto)? */
const dentro = (fecha, desde, hasta) => fecha >= desde && (!hasta || fecha <= hasta);

/**
 * Calendario mensual del equipo: una fila por empleado y una columna por día.
 * Pulsar un día libre registra una ausencia; pulsar el nombre abre la ficha.
 */
export default function Calendario({ onAbrirFicha }) {
    const { showError } = useNotification();
    const ahora = new Date();
    const [anio, setAnio] = useState(ahora.getFullYear());
    const [mes, setMes] = useState(ahora.getMonth() + 1);
    const [datos, setDatos] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [search, setSearch] = useState("");
    const [buscar, setBuscar] = useState("");
    const [obra, setObra] = useState(null);
    const [nueva, setNueva] = useState(null); // {empleado, fecha}

    const cargar = async () => {
        setCargando(true);
        try {
            const { data } = await api.get("/rrhh/calendario", {
                params: { anio, mes, search: buscar || undefined, obra_id: obra?.id },
            });
            setDatos(data);
        } catch {
            showError("Error al cargar el calendario");
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        cargar();
    }, [anio, mes, buscar, obra?.id]);

    // Búsqueda con retardo.
    useEffect(() => {
        const t = setTimeout(() => setBuscar(search.trim()), 350);
        return () => clearTimeout(t);
    }, [search]);

    const mover = (delta) => {
        const d = new Date(anio, mes - 1 + delta, 1);
        setAnio(d.getFullYear());
        setMes(d.getMonth() + 1);
    };

    const hoy = hoyISO();
    const tiposPorId = useMemo(() => Object.fromEntries((datos?.tipos ?? []).map((t) => [t.id, t])), [datos]);

    // empleado → fecha → {ausencia, tipo} ; y ausentes por día
    const { mapa, ausentesPorDia, usados } = useMemo(() => {
        const mapa = {};
        const ausentesPorDia = {};
        const usados = new Set();
        for (const e of datos?.empleados ?? []) {
            mapa[e.id] = {};
            for (const d of datos.dias) {
                const a = e.ausencias.find((x) => dentro(d.fecha, x.desde, x.hasta));
                if (a) {
                    mapa[e.id][d.fecha] = a;
                    usados.add(a.tipo_id);
                    ausentesPorDia[d.fecha] = (ausentesPorDia[d.fecha] ?? 0) + 1;
                }
            }
        }
        return { mapa, ausentesPorDia, usados };
    }, [datos]);

    const leyenda = (datos?.tipos ?? []).filter((t) => t.activo || usados.has(t.id));
    const festivosMes = (datos?.dias ?? []).filter((d) => d.festivo);
    const esMesActual = anio === ahora.getFullYear() && mes === ahora.getMonth() + 1;

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            {/* Controles */}
            <div className="space-y-3 border-b border-slate-200 px-4 py-4 sm:px-6">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-2">
                        <button type="button" onClick={() => mover(-1)} className={`${botonSecundario} px-3`} aria-label="Mes anterior">
                            <i className="mgc_left_line"></i>
                        </button>
                        <h3 className="min-w-[10rem] text-center text-lg font-semibold text-slate-900">
                            {MESES[mes - 1]} {anio}
                        </h3>
                        <button type="button" onClick={() => mover(1)} className={`${botonSecundario} px-3`} aria-label="Mes siguiente">
                            <i className="mgc_right_line"></i>
                        </button>
                        {!esMesActual && (
                            <button
                                type="button"
                                onClick={() => {
                                    setAnio(ahora.getFullYear());
                                    setMes(ahora.getMonth() + 1);
                                }}
                                className={`${botonSecundario} px-3`}
                            >
                                Hoy
                            </button>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-[34rem]">
                        <div className="relative">
                            <i className="mgc_search_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input
                                type="search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar empleado…"
                                className={`${inputBase} pl-9`}
                            />
                        </div>
                        <SelectorObras value={obra} onChange={setObra} placeholder="Filtrar por obra…" />
                    </div>
                </div>

                {leyenda.length > 0 && (
                    <div className="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-600">
                        {leyenda.map((t) => (
                            <span key={t.id} className="inline-flex items-center gap-1.5">
                                <span className={`h-3 w-3 rounded ${colorAusencia(t.color).celda}`}></span>
                                {t.nombre}
                            </span>
                        ))}
                        <span className="inline-flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded border border-amber-200 bg-amber-100"></span>
                            Festivo
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded bg-slate-100"></span>
                            Fin de semana
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded bg-[repeating-linear-gradient(45deg,#e2e8f0_0,#e2e8f0_2px,transparent_2px,transparent_5px)]"></span>
                            No estaba de alta
                        </span>
                    </div>
                )}
            </div>

            {cargando && !datos ? (
                <Cargando texto="Cargando calendario…" />
            ) : datos?.empleados?.length === 0 ? (
                <Vacio
                    icono="mgc_calendar_month_line"
                    titulo="Nadie en este mes"
                    texto={buscar || obra ? "Ningún empleado coincide con los filtros." : "No hay empleados de alta en este mes."}
                />
            ) : (
                <div className={`overflow-x-auto overscroll-x-contain ${cargando ? "opacity-60" : ""}`}>
                    <table className="border-separate border-spacing-0 text-xs">
                        <thead>
                            <tr>
                                <th className="sticky left-0 z-20 min-w-[9rem] border-b border-r border-slate-200 bg-white px-3 py-2 text-left font-semibold text-slate-500 sm:min-w-[14rem]">
                                    Empleado
                                </th>
                                {datos?.dias.map((d) => {
                                    const finde = d.semana >= 6;
                                    return (
                                        <th
                                            key={d.fecha}
                                            title={d.festivo ?? undefined}
                                            className={`min-w-[2rem] border-b border-slate-200 px-0.5 py-1.5 text-center font-medium ${
                                                d.festivo ? "bg-amber-100 text-amber-800" : finde ? "bg-slate-100 text-slate-400" : "bg-white text-slate-500"
                                            } ${d.fecha === hoy ? "!bg-cyan-600 !text-white" : ""}`}
                                        >
                                            <span className="block text-[10px] uppercase">{DIAS_SEMANA[d.semana]}</span>
                                            <span className="block text-sm font-semibold">{d.dia}</span>
                                        </th>
                                    );
                                })}
                            </tr>
                        </thead>
                        <tbody>
                            {datos?.empleados.map((e) => (
                                <tr key={e.id} className="group">
                                    <td className="sticky left-0 z-10 border-b border-r border-slate-100 bg-white px-3 py-1.5 group-hover:bg-slate-50">
                                        <button
                                            type="button"
                                            onClick={() => onAbrirFicha(e.id)}
                                            className="block max-w-[8rem] truncate text-left text-sm font-medium text-slate-800 hover:text-cyan-700 sm:max-w-[13rem]"
                                            title={e.nombre}
                                        >
                                            {e.nombre}
                                        </button>
                                        {e.puesto && <span className="hidden max-w-[13rem] truncate text-[11px] text-slate-400 sm:block">{e.puesto}</span>}
                                    </td>
                                    {datos.dias.map((d) => {
                                        const a = mapa[e.id]?.[d.fecha];
                                        const deAlta = e.periodos.some((p) => dentro(d.fecha, p.desde, p.hasta));
                                        const finde = d.semana >= 6;
                                        const tipo = a ? tiposPorId[a.tipo_id] : null;

                                        let fondo = "bg-white hover:bg-cyan-50";
                                        if (!deAlta) fondo = "bg-[repeating-linear-gradient(45deg,#e2e8f0_0,#e2e8f0_2px,transparent_2px,transparent_5px)]";
                                        else if (d.festivo) fondo = "bg-amber-50 hover:bg-cyan-50";
                                        else if (finde) fondo = "bg-slate-50 hover:bg-cyan-50";

                                        return (
                                            <td key={d.fecha} className={`h-9 border-b border-slate-100 p-0.5 ${d.fecha === hoy ? "bg-cyan-50/60" : ""}`}>
                                                {a ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => onAbrirFicha(e.id)}
                                                        title={`${tipo?.nombre ?? "Ausencia"}: ${fechaCorta(a.desde)} → ${a.hasta ? fechaCorta(a.hasta) : "sin fecha de fin"}`}
                                                        className={`block h-full w-full rounded ${colorAusencia(tipo?.color).celda} ${finde || d.festivo ? "opacity-60" : ""} transition hover:opacity-80`}
                                                    ></button>
                                                ) : deAlta ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => setNueva({ empleado: { id: e.id, nombre_completo: e.nombre_completo }, fecha: d.fecha })}
                                                        title={`Registrar ausencia el ${fechaCorta(d.fecha)}`}
                                                        className={`block h-full w-full rounded transition ${fondo}`}
                                                    ></button>
                                                ) : (
                                                    <span className={`block h-full w-full rounded ${fondo}`} title="No estaba de alta"></span>
                                                )}
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td className="sticky left-0 z-10 border-r border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500">
                                    Ausentes
                                </td>
                                {datos?.dias.map((d) => (
                                    <td key={d.fecha} className="bg-slate-50 py-2 text-center text-xs font-semibold text-slate-600">
                                        {ausentesPorDia[d.fecha] || ""}
                                    </td>
                                ))}
                            </tr>
                        </tfoot>
                    </table>
                </div>
            )}

            {festivosMes.length > 0 && (
                <div className="border-t border-slate-200 px-4 py-3 text-xs text-slate-600 sm:px-6">
                    <span className="font-semibold text-slate-700">Festivos del mes:</span>{" "}
                    {festivosMes.map((d) => `${d.dia} (${d.festivo})`).join(" · ")}
                </div>
            )}

            {nueva && (
                <ModalAusencia
                    empleado={nueva.empleado}
                    fechaInicial={nueva.fecha}
                    tipos={datos?.tipos ?? []}
                    onCerrar={() => setNueva(null)}
                    onGuardado={() => {
                        setNueva(null);
                        cargar();
                    }}
                />
            )}
        </div>
    );
}
