import React, { useEffect, useMemo, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import DocumentacionPendiente from "./DocumentacionPendiente";
import { Cargando, Cifra, Vacio } from "./Comunes";

const NIVELES = {
    critico: { texto: "Urgente", barra: "bg-rose-500", chip: "border-rose-200 bg-rose-50 text-rose-700", icono: "mgc_warning_line" },
    aviso: { texto: "Aviso", barra: "bg-amber-500", chip: "border-amber-200 bg-amber-50 text-amber-700", icono: "mgc_alert_line" },
    info: { texto: "Info", barra: "bg-sky-500", chip: "border-sky-200 bg-sky-50 text-sky-700", icono: "mgc_information_line" },
};

const ICONOS = {
    contratos: "mgc_paper_line",
    documentacion: "mgc_file_check_line",
    formacion: "mgc_star_line",
    bajas: "mgc_heartbeat_line",
    nominas: "mgc_currency_euro_line",
    anticipos: "mgc_bank_card_line",
    vacaciones: "mgc_sun_line",
    cuadrante: "mgc_calendar_month_line",
    obras: "mgc_building_2_line",
    cumpleanos: "mgc_star_line",
};

/** Panel de alertas de Recursos humanos. */
export default function Alertas({ onAbrirFicha, onCambio }) {
    const { showError } = useNotification();
    const [datos, setDatos] = useState(null);
    const [nivel, setNivel] = useState("");
    const [categoria, setCategoria] = useState("");
    const [verDocumentacion, setVerDocumentacion] = useState(false);

    useEffect(() => {
        api.get("/rrhh/alertas")
            .then(({ data }) => {
                setDatos(data);
                onCambio?.(data.totales);
            })
            .catch(() => {
                showError("Error al cargar las alertas");
                setDatos({ alertas: [], totales: { critico: 0, aviso: 0, info: 0, total: 0 }, categorias: {} });
            });
    }, []);

    const filtradas = useMemo(
        () => (datos?.alertas ?? []).filter((a) => (!nivel || a.nivel === nivel) && (!categoria || a.categoria === categoria)),
        [datos, nivel, categoria],
    );
    const categoriasConAlertas = useMemo(() => [...new Set((datos?.alertas ?? []).map((a) => a.categoria))], [datos]);

    if (!datos) {
        return (
            <div className="rounded-3xl border border-slate-200 bg-white">
                <Cargando texto="Revisando…" />
            </div>
        );
    }

    const t = datos.totales;

    return (
        <div className="space-y-4">
            <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                <div className="space-y-4 border-b border-slate-200 px-4 py-4 sm:px-6">
                    <div className="grid grid-cols-3 gap-3 sm:max-w-xl">
                        {Object.entries(NIVELES).map(([clave, n]) => (
                            <button key={clave} type="button" onClick={() => setNivel(nivel === clave ? "" : clave)} className={`text-left transition ${nivel && nivel !== clave ? "opacity-50" : ""}`}>
                                <Cifra etiqueta={clave === "critico" ? "Urgentes" : clave === "aviso" ? "Avisos" : "Informativas"} valor={t[clave]} tono={clave === "critico" ? "rose" : clave === "aviso" ? "amber" : "cyan"} />
                            </button>
                        ))}
                    </div>

                    {categoriasConAlertas.length > 1 && (
                        <div className="flex flex-wrap gap-1.5">
                            <button
                                type="button"
                                onClick={() => setCategoria("")}
                                className={`rounded-full border px-3 py-1 text-xs font-semibold ${!categoria ? "border-cyan-500 bg-cyan-50 text-cyan-800" : "border-slate-200 text-slate-600 hover:bg-slate-50"}`}
                            >
                                Todas
                            </button>
                            {categoriasConAlertas.map((c) => (
                                <button
                                    key={c}
                                    type="button"
                                    onClick={() => setCategoria(categoria === c ? "" : c)}
                                    className={`inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold ${
                                        categoria === c ? "border-cyan-500 bg-cyan-50 text-cyan-800" : "border-slate-200 text-slate-600 hover:bg-slate-50"
                                    }`}
                                >
                                    <i className={ICONOS[c] ?? "mgc_alert_line"}></i>
                                    {datos.categorias[c] ?? c}
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                {t.total === 0 ? (
                    <Vacio icono="mgc_check_circle_line" titulo="Todo en orden" texto="No hay nada que requiera atención ahora mismo." />
                ) : filtradas.length === 0 ? (
                    <Vacio icono="mgc_filter_line" titulo="Nada con estos filtros" />
                ) : (
                    <ul className="divide-y divide-slate-100">
                        {filtradas.map((a, i) => {
                            const n = NIVELES[a.nivel];
                            const Contenido = (
                                <>
                                    <span className={`w-1 shrink-0 self-stretch rounded-full ${n.barra}`}></span>
                                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                        <i className={`${ICONOS[a.categoria] ?? "mgc_alert_line"} text-lg`}></i>
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="flex flex-wrap items-center gap-2">
                                            <span className="font-semibold text-slate-800">{a.titulo}</span>
                                            <span className={`rounded-full border px-2 py-0.5 text-[10px] font-semibold ${n.chip}`}>{n.texto}</span>
                                        </span>
                                        {a.empleado && <span className="block text-sm font-medium text-cyan-700">{a.empleado.nombre_completo}</span>}
                                        <span className="block text-sm text-slate-600">{a.detalle}</span>
                                    </span>
                                    {a.empleado && <i className="mgc_right_line self-center text-lg text-slate-400"></i>}
                                </>
                            );

                            return (
                                <li key={i}>
                                    {a.empleado ? (
                                        <button type="button" onClick={() => onAbrirFicha(a.empleado.id)} className="flex w-full gap-3 px-4 py-3 text-left transition hover:bg-cyan-50/40 sm:px-6">
                                            {Contenido}
                                        </button>
                                    ) : (
                                        <div className="flex gap-3 px-4 py-3 sm:px-6">{Contenido}</div>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                )}
            </div>

            <div>
                <button type="button" onClick={() => setVerDocumentacion(!verDocumentacion)} className="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-cyan-700 hover:text-cyan-800">
                    <i className={`mgc_right_line transition ${verDocumentacion ? "rotate-90" : ""}`}></i>
                    Detalle de documentación y formación pendiente
                </button>
                {verDocumentacion && <DocumentacionPendiente onAbrirFicha={onAbrirFicha} />}
            </div>
        </div>
    );
}
