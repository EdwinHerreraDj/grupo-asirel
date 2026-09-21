import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Cargando, EstadoDocumento, Vacio } from "./Comunes";
import { fechaCorta, textoDias } from "../utils";

const TARJETAS = [
    { clave: "faltan", texto: "Faltan", clases: "border-rose-200 bg-rose-50 text-rose-700" },
    { clave: "vencidos", texto: "Caducados", clases: "border-rose-200 bg-rose-50 text-rose-700" },
    { clave: "proximos", texto: "Caducan pronto", clases: "border-amber-200 bg-amber-50 text-amber-700" },
    { clave: "sin_fecha", texto: "Sin fecha", clases: "border-amber-200 bg-amber-50 text-amber-700" },
];

/** Empleados de alta con documentos que faltan, están caducados o caducan pronto. */
export default function DocumentacionPendiente({ onAbrirFicha }) {
    const { showError } = useNotification();
    const [datos, setDatos] = useState(null);

    useEffect(() => {
        api.get("/rrhh/documentacion-pendiente")
            .then(({ data }) => setDatos(data))
            .catch(() => {
                showError("Error al cargar la documentación pendiente");
                setDatos({ empleados: [], totales: {} });
            });
    }, []);

    if (!datos) {
        return (
            <div className="rounded-3xl border border-slate-200 bg-white">
                <Cargando />
            </div>
        );
    }

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="grid grid-cols-2 gap-3 border-b border-slate-200 px-4 py-4 sm:px-6 lg:grid-cols-4">
                {TARJETAS.map((t) => (
                    <div key={t.clave} className={`rounded-2xl border px-4 py-3 ${t.clases}`}>
                        <p className="text-[11px] font-semibold uppercase tracking-[0.12em]">{t.texto}</p>
                        <p className="mt-1 text-xl font-bold">{datos.totales?.[t.clave] ?? 0}</p>
                    </div>
                ))}
            </div>

            {datos.empleados.length === 0 ? (
                <Vacio icono="mgc_check_circle_line" titulo="Todo al día" texto="Ningún empleado de alta tiene documentación pendiente." />
            ) : (
                <ul className="divide-y divide-slate-100">
                    {datos.empleados.map(({ empleado, problemas }) => (
                        <li key={empleado.id}>
                            <button
                                type="button"
                                onClick={() => onAbrirFicha(empleado.id)}
                                className="w-full px-4 py-4 text-left transition hover:bg-cyan-50/40 sm:px-6"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="break-words font-semibold text-slate-800">{empleado.nombre_completo}</p>
                                        <p className="text-xs text-slate-500">
                                            <span className="font-mono">{empleado.dni}</span>
                                            {empleado.puesto && ` · ${empleado.puesto}`}
                                            {empleado.obra && ` · ${empleado.obra}`}
                                        </p>
                                    </div>
                                    <i className="mgc_right_line text-lg text-slate-400"></i>
                                </div>
                                <ul className="mt-2 flex flex-col gap-1.5">
                                    {problemas.map((p) => (
                                        <li key={p.tipo_id} className="flex flex-wrap items-center gap-2 text-sm">
                                            <EstadoDocumento estado={p.estado} />
                                            <span className="text-slate-700">{p.tipo}</span>
                                            {p.caduca && (
                                                <span className="text-xs text-slate-500">
                                                    {fechaCorta(p.caduca)} ({textoDias(p.dias)})
                                                </span>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
