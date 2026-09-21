import React, { useCallback, useEffect, useState } from "react";
import { NotificationProvider } from "../shared/NotificationContext";
import ListaEmpleados from "./components/ListaEmpleados";
import FichaEmpleado from "./components/FichaEmpleado";
import Alertas from "./components/Alertas";
import Cuadrante from "./components/Cuadrante";
import Informes from "./components/Informes";
import api from "../shared/api";
import Calendario from "./components/Calendario";
import NominasMes from "./components/NominasMes";
import Configuracion from "./components/Configuracion";

const PESTANAS = [
    { id: "empleados", texto: "Empleados", icono: "mgc_group_line" },
    { id: "cuadrante", texto: "Cuadrante", icono: "mgc_time_line" },
    { id: "calendario", texto: "Ausencias", icono: "mgc_calendar_month_line" },
    { id: "nominas", texto: "Nóminas", icono: "mgc_currency_euro_line" },
    { id: "alertas", texto: "Alertas", icono: "mgc_alert_line" },
    { id: "informes", texto: "Informes", icono: "mgc_chart_bar_line" },
    { id: "configuracion", texto: "Configuración", icono: "mgc_settings_3_line" },
];

// ?empleado=ID abre la ficha (y se conserva al recargar la página).
const leerEmpleadoDeUrl = () => {
    const id = Number(new URLSearchParams(window.location.search).get("empleado"));
    return Number.isInteger(id) && id > 0 ? id : null;
};

const escribirEmpleadoEnUrl = (id) => {
    const url = new URL(window.location.href);
    if (id) url.searchParams.set("empleado", id);
    else {
        url.searchParams.delete("empleado");
        url.searchParams.delete("pestana");
    }
    window.history.replaceState(null, "", url);
};

function RrhhAppContent() {
    const [pestana, setPestana] = useState("empleados");
    const [empleadoId, setEmpleadoId] = useState(leerEmpleadoDeUrl);
    const [avisos, setAvisos] = useState(null); // {critico, aviso}

    // Contador de alertas urgentes y avisos en la pestaña.
    useEffect(() => {
        if (empleadoId) return;
        api.get("/rrhh/alertas").then(({ data }) => setAvisos(data.totales)).catch(() => {});
    }, [empleadoId]);

    const abrirFicha = useCallback((id) => {
        setEmpleadoId(id);
        escribirEmpleadoEnUrl(id);
        window.scrollTo({ top: 0 });
    }, []);

    const cerrarFicha = useCallback(() => {
        setEmpleadoId(null);
        escribirEmpleadoEnUrl(null);
    }, []);

    if (empleadoId) {
        return <FichaEmpleado id={empleadoId} onVolver={cerrarFicha} />;
    }

    return (
        <div>
            <div className="space-y-4">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                    <div className="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                        <div className="mb-4">
                            <a
                                href="/empresa"
                                className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                            >
                                <i className="mgc_arrow_left_line text-lg"></i>
                                <span className="hidden sm:inline">Regresar</span>
                            </a>
                        </div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                            <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                            Empresa
                        </div>
                        <h2 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">Recursos humanos</h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Fichas de empleados, cuadrante de turnos, ausencias, nóminas, formación, alertas e informes.
                        </p>
                    </div>

                    <nav className="flex gap-1 overflow-x-auto px-3 py-2 sm:px-4" aria-label="Secciones">
                        {PESTANAS.map((p) => (
                            <button
                                key={p.id}
                                type="button"
                                onClick={() => setPestana(p.id)}
                                className={`inline-flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-semibold transition ${
                                    pestana === p.id
                                        ? "bg-cyan-50 text-cyan-700"
                                        : "text-slate-500 hover:bg-slate-50 hover:text-slate-800"
                                }`}
                            >
                                <i className={`${p.icono} text-base`}></i>
                                {p.texto}
                                {p.id === "alertas" && avisos && avisos.critico + avisos.aviso > 0 && (
                                    <span
                                        className={`flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1 text-[11px] font-bold text-white ${
                                            avisos.critico ? "bg-rose-500" : "bg-amber-500"
                                        }`}
                                    >
                                        {avisos.critico + avisos.aviso}
                                    </span>
                                )}
                            </button>
                        ))}
                    </nav>
                </div>

                {pestana === "empleados" && <ListaEmpleados onAbrirFicha={abrirFicha} />}
                {pestana === "calendario" && <Calendario onAbrirFicha={abrirFicha} />}
                {pestana === "nominas" && <NominasMes onAbrirFicha={abrirFicha} />}
                {pestana === "cuadrante" && <Cuadrante onAbrirFicha={abrirFicha} />}
                {pestana === "alertas" && <Alertas onAbrirFicha={abrirFicha} onCambio={setAvisos} />}
                {pestana === "informes" && <Informes />}
                {pestana === "configuracion" && <Configuracion />}
            </div>
        </div>
    );
}

export default function RrhhApp() {
    return (
        <NotificationProvider>
            <RrhhAppContent />
        </NotificationProvider>
    );
}
