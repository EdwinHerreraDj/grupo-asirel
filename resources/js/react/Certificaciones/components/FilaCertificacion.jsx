import React, { useState, useRef } from "react";
import {
    formatEuro,
    formatFecha,
    estadoCertificacionLabel,
    estadoFacturaLabel,
} from "../utils/formato";

export default function FilaCertificacion({
    cert,
    onVerDetalle,
    onNuevoCapitulo,
    onEliminar,
}) {
    const [menuAbierto, setMenuAbierto] = useState(false);
    const [menuPos, setMenuPos] = useState({ top: 0, right: 0 });
    const btnRef = useRef(null);

    const estadoCert = estadoCertificacionLabel(cert.estado_certificacion);
    const estadoFactura = estadoFacturaLabel(cert.estado_factura);

    const handleAbrirMenu = () => {
        if (btnRef.current) {
            const rect = btnRef.current.getBoundingClientRect();
            const alturaMenu = 180;
            const espacioAbajo = window.innerHeight - rect.bottom;

            if (espacioAbajo < alturaMenu) {
                setMenuPos({
                    bottom: window.innerHeight - rect.top + 4,
                    right: window.innerWidth - rect.right,
                    top: "auto",
                });
            } else {
                setMenuPos({
                    top: rect.bottom + 4,
                    right: window.innerWidth - rect.right,
                    bottom: "auto",
                });
            }
        }
        setMenuAbierto((v) => !v);
    };

    return (
        <tr className="border-t border-slate-100 bg-white hover:bg-slate-50/50 transition-colors">
            <td className="px-3 py-3"></td>
            <td className="px-3 py-3"></td>
            <td className="px-3 py-3">
                <div className="text-sm font-medium text-slate-700 pl-2 border-l-2 border-slate-200">
                    {cert.oficio_nombre}
                </div>
            </td>
            <td className="px-3 py-3 text-sm text-slate-500">
                {cert.cliente_nombre}
            </td>
            <td className="px-3 py-3 text-sm text-slate-500">
                {formatFecha(cert.fecha_ingreso)}
            </td>
            <td className="px-3 py-3 text-right text-sm font-semibold text-slate-800">
                {formatEuro(cert.total)}
            </td>
            <td className="px-3 py-3">
                <span
                    className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${estadoCert.color}`}
                >
                    {estadoCert.label}
                </span>
            </td>
            <td className="px-3 py-3">
                <span
                    className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${estadoFactura.color}`}
                >
                    {estadoFactura.label}
                </span>
            </td>
            <td className="px-3 py-3 text-right">
                <button
                    ref={btnRef}
                    onClick={handleAbrirMenu}
                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-400 transition hover:border-slate-200 hover:bg-white hover:text-slate-600"
                >
                    <i className="mgc_more_2_line text-base"></i>
                </button>

                {menuAbierto && (
                    <>
                        <div
                            className="fixed inset-0 z-40"
                            onClick={() => setMenuAbierto(false)}
                        />
                        <div
                            className="fixed z-50 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
                            style={{
                                top: menuPos.top,
                                bottom: menuPos.bottom,
                                right: menuPos.right,
                            }}
                        >
                            <button
                                onClick={() => {
                                    onVerDetalle(cert.id);
                                    setMenuAbierto(false);
                                }}
                                className="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            >
                                <i className="mgc_eye_line text-slate-400"></i>
                                Ver detalle
                            </button>

                            {cert.estado_factura !== "facturada" && (
                                <button
                                    onClick={() => {
                                        onNuevoCapitulo(cert);
                                        setMenuAbierto(false);
                                    }}
                                    className="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                >
                                    <i className="mgc_add_line text-slate-400"></i>
                                    Nuevo capítulo
                                </button>
                            )}

                            <div className="my-1 border-t border-slate-100" />

                            <button
                                onClick={() => {
                                    onEliminar(cert.id);
                                    setMenuAbierto(false);
                                }}
                                className="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                            >
                                <i className="mgc_delete_line"></i>
                                Eliminar
                            </button>
                        </div>
                    </>
                )}
            </td>
        </tr>
    );
}
