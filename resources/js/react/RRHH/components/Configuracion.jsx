import React, { useState } from "react";
import TiposDocumento from "./TiposDocumento";
import TiposAusencia from "./TiposAusencia";
import Festivos from "./Festivos";

const APARTADOS = [
    { id: "documentos", texto: "Tipos de documento", icono: "mgc_file_line" },
    { id: "ausencias", texto: "Tipos de ausencia", icono: "mgc_tag_line" },
    { id: "festivos", texto: "Festivos", icono: "mgc_flag_1_line" },
];

/** Configuración del módulo: documentos, ausencias y festivos. */
export default function Configuracion() {
    const [apartado, setApartado] = useState("documentos");

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="border-b border-slate-200 bg-slate-50/60 px-3 py-2 sm:px-4">
                <div className="flex gap-1 overflow-x-auto">
                    {APARTADOS.map((a) => (
                        <button
                            key={a.id}
                            type="button"
                            onClick={() => setApartado(a.id)}
                            className={`inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold transition ${
                                apartado === a.id ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-800"
                            }`}
                        >
                            <i className={a.icono}></i>
                            {a.texto}
                        </button>
                    ))}
                </div>
            </div>

            {apartado === "documentos" && <TiposDocumento integrado />}
            {apartado === "ausencias" && <TiposAusencia />}
            {apartado === "festivos" && <Festivos />}
        </div>
    );
}
