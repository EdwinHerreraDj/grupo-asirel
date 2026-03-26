import React, { useState } from "react";
import { formatEuro, formatFecha, estadoFacturaLabel } from "../utils/formato";
import FilaCertificacion from "./FilaCertificacion";

export default function TablaCertificaciones({
    certificaciones,
    loading,
    page,
    lastPage,
    total,
    onPageChange,
    onVerDetalle,
    onNuevoCapitulo,
    onEliminar,
    onFacturar,
    onInforme,
}) {
    const [gruposAbiertos, setGruposAbiertos] = useState({});

    const toggleGrupo = (numero) => {
        setGruposAbiertos((prev) => ({
            ...prev,
            [numero]: !prev[numero],
        }));
    };

    // Agrupar por numero_certificacion
    const grupos = certificaciones.reduce((acc, cert) => {
        const num = cert.numero_certificacion ?? "sin-numero";
        if (!acc[num]) acc[num] = [];
        acc[num].push(cert);
        return acc;
    }, {});

    return (
        <div className="space-y-4">
            <div className="overflow-x-auto border border-gray-200 rounded-xl">
                <table className="min-w-full text-sm">
                    <thead className="bg-gray-50 text-gray-700">
                        <tr>
                            <th className="px-3 py-3 text-left w-8"></th>
                            <th className="px-3 py-3 text-left">Nº Cert.</th>
                            <th className="px-3 py-3 text-left">Oficio</th>
                            <th className="px-3 py-3 text-left">Cliente</th>
                            <th className="px-3 py-3 text-left">Fecha</th>
                            <th className="px-3 py-3 text-right">Total</th>
                            <th className="px-3 py-3 text-left">Estado</th>
                            <th className="px-3 py-3 text-left">Factura</th>
                            <th className="px-3 py-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td
                                    colSpan={9}
                                    className="px-3 py-10 text-center text-gray-400 text-sm"
                                >
                                    <i className="mgc_loading_line animate-spin mr-2"></i>
                                    Cargando...
                                </td>
                            </tr>
                        ) : Object.keys(grupos).length === 0 ? (
                            <tr>
                                <td
                                    colSpan={9}
                                    className="px-3 py-10 text-center text-gray-400 text-sm italic"
                                >
                                    No hay certificaciones que coincidan con los
                                    filtros.
                                </td>
                            </tr>
                        ) : (
                            Object.entries(grupos).map(([numero, caps]) => {
                                const abierto = !!gruposAbiertos[numero];
                                const totalGrupo = caps.reduce(
                                    (a, c) => a + c.total,
                                    0,
                                );
                                const todosAceptados = caps.every(
                                    (c) =>
                                        c.estado_certificacion === "aceptada",
                                );
                                const algunoFacturado = caps.some(
                                    (c) => c.estado_factura === "facturada",
                                );
                                const puedeFacturar =
                                    todosAceptados && !algunoFacturado;
                                const estadoFactura = estadoFacturaLabel(
                                    caps[0].estado_factura,
                                );
                                const numCaps = caps.length;

                                return (
                                    <React.Fragment key={numero}>
                                        {/* FILA GRUPO */}
                                        <tr
                                            className="border-t bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors"
                                            onClick={() => toggleGrupo(numero)}
                                        >
                                            <td className="px-3 py-3 text-center text-gray-400">
                                                <i
                                                    className={`mgc_${abierto ? "down" : "right"}_line text-xs`}
                                                ></i>
                                            </td>
                                            <td className="px-3 py-3">
                                                <div className="inline-flex items-center rounded-xl border border-slate-200 bg-white px-2.5 py-1 font-mono text-sm font-semibold text-slate-700">
                                                    {numero}
                                                </div>
                                            </td>
                                            <td className="px-3 py-3 text-sm text-gray-500 italic">
                                                {numCaps} capítulo
                                                {numCaps !== 1 ? "s" : ""}
                                            </td>
                                            <td className="px-3 py-3 text-sm text-gray-600">
                                                {caps[0].cliente_nombre}
                                            </td>
                                            <td className="px-3 py-3 text-sm text-gray-600">
                                                {formatFecha(
                                                    caps[0].fecha_ingreso,
                                                )}
                                            </td>
                                            <td className="px-3 py-3 text-right font-bold text-gray-800">
                                                {formatEuro(totalGrupo)}
                                            </td>
                                            <td className="px-3 py-3">
                                                {todosAceptados ? (
                                                    <span className="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-800 border-green-200">
                                                        Todos aceptados
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 border-yellow-200">
                                                        Pendiente
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-3 py-3">
                                                <span
                                                    className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${estadoFactura.color}`}
                                                >
                                                    {estadoFactura.label}
                                                </span>
                                            </td>
                                            <td
                                                className="px-3 py-3 text-right"
                                                onClick={(e) =>
                                                    e.stopPropagation()
                                                }
                                            >
                                                <div className="flex items-center justify-end gap-1">
                                                    {/* BOTÓN INFORME */}
                                                    <button
                                                        onClick={() =>
                                                            onInforme(numero)
                                                        }
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-500 transition hover:border-slate-200 hover:bg-white hover:shadow-sm"
                                                        title="Generar informe"
                                                    >
                                                        <i className="mgc_file_line text-base"></i>
                                                    </button>

                                                    {/* BOTÓN FACTURAR */}
                                                    {puedeFacturar && (
                                                        <button
                                                            onClick={() =>
                                                                onFacturar(
                                                                    numero,
                                                                )
                                                            }
                                                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-600 text-white text-xs font-semibold hover:bg-cyan-700 transition shadow-sm"
                                                            title="Facturar"
                                                        >
                                                            <i className="mgc_bill_line text-sm"></i>
                                                            Facturar
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>

                                        {/* FILAS DE CAPÍTULOS */}
                                        {abierto &&
                                            caps.map((cert) => (
                                                <FilaCertificacion
                                                    key={cert.id}
                                                    cert={cert}
                                                    esCapitulo
                                                    onVerDetalle={onVerDetalle}
                                                    onNuevoCapitulo={
                                                        onNuevoCapitulo
                                                    }
                                                    onEliminar={onEliminar}
                                                />
                                            ))}
                                    </React.Fragment>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>

            {/* PAGINACIÓN */}
            {lastPage > 1 && (
                <div className="flex items-center justify-between text-sm text-gray-600">
                    <span>{total} certificaciones</span>
                    <div className="flex gap-1">
                        <button
                            onClick={() => onPageChange(page - 1)}
                            disabled={page === 1}
                            className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            ‹
                        </button>
                        {Array.from({ length: lastPage }, (_, i) => i + 1).map(
                            (p) => (
                                <button
                                    key={p}
                                    onClick={() => onPageChange(p)}
                                    className={`px-3 py-1.5 rounded-lg border transition ${
                                        p === page
                                            ? "bg-primary text-white border-primary"
                                            : "border-gray-200 hover:bg-gray-100"
                                    }`}
                                >
                                    {p}
                                </button>
                            ),
                        )}
                        <button
                            onClick={() => onPageChange(page + 1)}
                            disabled={page === lastPage}
                            className="px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            ›
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
