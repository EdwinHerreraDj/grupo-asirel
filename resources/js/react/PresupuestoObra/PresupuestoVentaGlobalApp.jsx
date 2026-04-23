import React, { useState } from "react";
import { PresupuestoVentaInner } from "./PresupuestoVentaApp";
import ObraSearchSelect from "./components/ObraSearchSelect";
import { NotificationProvider } from "../shared/NotificationContext";

function PresupuestoVentaGlobalInner({ obras, urlRegresar }) {
    const [obraSeleccionada, setObraSeleccionada] = useState(null);

    return (
        <div className="space-y-4">
            {/* Cabecera global (solo título) */}
            <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                <div className="bg-gradient-to-r from-slate-50 via-white to-emerald-50/40 px-5 py-5 sm:px-6">
                    <div className="min-w-0">
                        <div className="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-700">
                            <span className="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Planificación
                        </div>
                        <h2 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                            Presupuesto de venta
                            <span className="text-base font-normal text-slate-400">
                                {" "}
                                · global
                            </span>
                        </h2>
                        <p className="mt-1 text-sm text-slate-500 truncate">
                            {obraSeleccionada
                                ? obraSeleccionada.nombre
                                : "Selecciona una obra para ver su presupuesto de venta"}
                        </p>
                    </div>
                </div>
            </div>

            {/* Selector de obra — fuera de la cabecera para que el dropdown
                no se corte por el overflow-hidden */}
            <ObraSearchSelect
                obras={obras}
                value={obraSeleccionada?.id ?? null}
                onChange={setObraSeleccionada}
                accent="emerald"
            />

            {/* Contenido per-obra */}
            {obraSeleccionada ? (
                <PresupuestoVentaInner
                    key={obraSeleccionada.id}
                    obraId={String(obraSeleccionada.id)}
                    obraNombre={obraSeleccionada.nombre}
                    urlRegresar={urlRegresar}
                />
            ) : (
                <div className="rounded-3xl border border-dashed border-slate-200 bg-white/50 px-6 py-16 text-center">
                    <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i className="mgc_building_2_line text-2xl"></i>
                    </div>
                    <p className="text-sm font-semibold text-slate-700">
                        Selecciona una obra
                    </p>
                    <p className="mt-1 text-xs text-slate-500">
                        Elige una obra del selector superior para gestionar su
                        presupuesto de venta y márgenes.
                    </p>
                </div>
            )}
        </div>
    );
}

export default function PresupuestoVentaGlobalApp() {
    const el = document.getElementById("react-presupuesto-venta-global");
    const urlRegresar = el?.dataset?.urlRegresar ?? "/";

    let obras = [];
    try {
        obras = JSON.parse(el?.dataset?.obras || "[]");
    } catch (e) {
        console.error("Error parseando obras:", e);
    }

    return (
        <NotificationProvider>
            <PresupuestoVentaGlobalInner
                obras={obras}
                urlRegresar={urlRegresar}
            />
        </NotificationProvider>
    );
}
