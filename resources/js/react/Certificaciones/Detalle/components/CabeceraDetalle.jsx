import React from "react";
import {
    formatFecha,
    estadoCertificacionLabel,
    estadoFacturaLabel,
} from "../../utils/formato";

export default function CabeceraDetalle({
    certificacion,
    onAceptar,
    onImpuestos,
    onAnular,
    onDescargarPdf,
    descargando = false,
    urlVolver,
}) {
    const estadoCert = estadoCertificacionLabel(
        certificacion.estado_certificacion,
    );
    const estadoFactura = estadoFacturaLabel(certificacion.estado_factura);
    const editable = certificacion.estado_certificacion === "pendiente";
    const puedeAnular = certificacion.puede_anular;
    const avance = certificacion.avance_oficio ?? { certificadas: 0, total: 0 };
    const facturaEmitida = certificacion.factura_emitida;
    const capitulosHermanos = certificacion.capitulos_hermanos ?? [];
    const tieneHermanos = capitulosHermanos.length > 1;

    return (
        <div className="mb-6 space-y-5">
            {/* NAVEGACIÓN */}
            <div className="flex items-center gap-3">
                <a
                    href={urlVolver}
                    className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:text-slate-900"
                >
                    <i className="mgc_arrow_left_line text-lg"></i>
                    Certificaciones
                </a>
            </div>

            {/* TÍTULO + ESTADOS + ACCIONES */}
            <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                    <div className="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div className="min-w-0">
                            <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                Detalle
                            </div>

                            <h2 className="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                                Certificación{" "}
                                <span className="font-mono">
                                    {certificacion.numero_certificacion ?? "—"}
                                </span>
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                {certificacion.oficio_nombre} —{" "}
                                {certificacion.cliente_nombre}
                            </p>

                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                <span
                                    className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${estadoCert.color}`}
                                >
                                    {estadoCert.label}
                                </span>
                                <span
                                    className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${estadoFactura.color}`}
                                >
                                    {estadoFactura.label}
                                </span>

                                {facturaEmitida && (
                                    <a
                                        href={facturaEmitida.url}
                                        className="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                        title="Ver factura"
                                    >
                                        <i className="mgc_bill_line"></i>
                                        Factura {facturaEmitida.referencia}
                                        <i className="mgc_arrow_right_line"></i>
                                    </a>
                                )}

                                {avance.total > 0 && (
                                    <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        <i className="mgc_pie_chart_line"></i>
                                        {avance.certificadas} de {avance.total}{" "}
                                        partidas del oficio certificadas
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* ACCIONES */}
                        <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                            {editable && (
                                <>
                                    <button
                                        onClick={onImpuestos}
                                        className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900"
                                    >
                                        <i className="mgc_pig_money_line text-base"></i>
                                        Impuestos
                                    </button>

                                    <button
                                        onClick={onAceptar}
                                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(5,150,105,0.22)] transition hover:from-emerald-500 hover:to-green-500"
                                    >
                                        <i className="mgc_check_line text-base"></i>
                                        Aceptar certificación
                                    </button>
                                </>
                            )}

                            {puedeAnular && onAnular && (
                                <button
                                    onClick={onAnular}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:border-amber-400 hover:bg-amber-100"
                                >
                                    <i className="mgc_refresh_1_line text-base"></i>
                                    Anular aceptación
                                </button>
                            )}

                            {onDescargarPdf && (
                                <button
                                    onClick={onDescargarPdf}
                                    disabled={descargando}
                                    className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900 disabled:opacity-60"
                                >
                                    <i className="mgc_download_2_line text-base"></i>
                                    {descargando ? "Generando…" : "Descargar PDF"}
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                {/* NAVEGACIÓN ENTRE CAPÍTULOS HERMANOS */}
                {tieneHermanos && (
                    <div className="border-b border-slate-200/70 bg-white px-5 py-3 sm:px-6">
                        <div className="flex items-center gap-2 flex-wrap">
                            <span className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Capítulos:
                            </span>
                            <div className="flex flex-wrap gap-1.5">
                                {capitulosHermanos.map((h) => (
                                    <a
                                        key={h.id}
                                        href={h.actual ? undefined : h.url}
                                        onClick={(e) =>
                                            h.actual && e.preventDefault()
                                        }
                                        className={`inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium transition ${
                                            h.actual
                                                ? "border-cyan-300 bg-cyan-100 text-cyan-800 cursor-default"
                                                : "border-slate-200 bg-slate-50 text-slate-700 hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700"
                                        }`}
                                    >
                                        {h.oficio_nombre}
                                    </a>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* DATOS */}
                <div className="px-5 py-5 sm:px-6">
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        {[
                            {
                                label: "Obra",
                                valor: certificacion.obra_nombre ?? "—",
                            },
                            {
                                label: "Fecha ingreso",
                                valor: formatFecha(certificacion.fecha_ingreso),
                            },
                            {
                                label: "Fecha contable",
                                valor: formatFecha(
                                    certificacion.fecha_contable,
                                ),
                            },
                            {
                                label: "Fecha vencimiento",
                                valor: formatFecha(
                                    certificacion.fecha_vencimiento,
                                ),
                            },
                        ].map(({ label, valor }) => (
                            <div
                                key={label}
                                className="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3"
                            >
                                <p className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    {label}
                                </p>
                                <p className="mt-1 text-sm font-semibold text-slate-900 truncate" title={valor}>
                                    {valor}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
