import React from "react";
import { formatFecha } from "../../utils/formato";

const ESTILOS_EVENTO = {
    creada: {
        icono: "mgc_document_2_line",
        color: "bg-slate-100 text-slate-700 border-slate-200",
        titulo: "Creada",
    },
    aceptada: {
        icono: "mgc_check_line",
        color: "bg-emerald-100 text-emerald-700 border-emerald-200",
        titulo: "Aceptada",
    },
    anulada: {
        icono: "mgc_refresh_1_line",
        color: "bg-amber-100 text-amber-700 border-amber-200",
        titulo: "Anulada aceptación",
    },
    facturada: {
        icono: "mgc_bill_line",
        color: "bg-blue-100 text-blue-700 border-blue-200",
        titulo: "Facturada",
    },
};

function formatHora(fecha) {
    if (!fecha) return "";
    try {
        return new Date(fecha).toLocaleTimeString("es-ES", {
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return "";
    }
}

export default function TimelineEventos({ eventos = [] }) {
    if (!eventos.length) return null;

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-5 py-4 sm:px-6">
                <div className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                    <span className="h-2 w-2 rounded-full bg-slate-500"></span>
                    Auditoría
                </div>
                <h4 className="mt-3 text-base font-semibold text-slate-900">
                    Historial de la certificación
                </h4>
                <p className="mt-1 text-sm text-slate-500">
                    Transiciones de estado registradas con usuario y fecha.
                </p>
            </div>

            <ol className="relative space-y-4 px-5 py-5 sm:px-6">
                <span className="absolute left-9 top-6 bottom-6 w-px bg-slate-200"></span>

                {eventos.map((ev) => {
                    const estilo =
                        ESTILOS_EVENTO[ev.tipo] ?? {
                            icono: "mgc_round_line",
                            color: "bg-slate-100 text-slate-700 border-slate-200",
                            titulo: ev.tipo,
                        };

                    return (
                        <li
                            key={ev.id}
                            className="relative flex items-start gap-4"
                        >
                            <span
                                className={`z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border ${estilo.color}`}
                            >
                                <i
                                    className={`${estilo.icono} text-base`}
                                ></i>
                            </span>

                            <div className="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-800">
                                        {estilo.titulo}
                                    </p>
                                    <p className="text-xs text-slate-500">
                                        {formatFecha(ev.fecha)}{" "}
                                        {formatHora(ev.fecha)}
                                    </p>
                                </div>

                                <p className="mt-1 text-xs text-slate-500">
                                    {ev.usuario
                                        ? `por ${ev.usuario}`
                                        : "sistema"}
                                </p>

                                {ev.motivo && (
                                    <p className="mt-2 rounded-lg bg-white px-3 py-2 text-xs text-slate-600 border border-slate-200">
                                        “{ev.motivo}”
                                    </p>
                                )}
                            </div>
                        </li>
                    );
                })}
            </ol>
        </div>
    );
}
