import React from "react";
import { formatEuro } from "../../utils/formato";

export default function ResumenFiscal({ certificacion, presupuesto }) {
    const pctCertificado =
        presupuesto?.importe_contratado > 0
            ? Math.round(
                  (presupuesto.importe_certificado /
                      presupuesto.importe_contratado) *
                      100,
              )
            : 0;

    return (
        <div className="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
            {/* FISCAL */}
            <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-4 sm:px-6">
                    <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Fiscal
                    </div>
                    <h4 className="mt-3 text-base font-semibold text-slate-900">
                        Resumen fiscal
                    </h4>
                    <p className="mt-1 text-sm text-slate-500">
                        Desglose económico e impositivo de la certificación.
                    </p>
                </div>

                <div className="space-y-3 px-5 py-5 sm:px-6">
                    <div className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm">
                        <span className="text-slate-600">Base imponible</span>
                        <span className="font-semibold text-slate-900">
                            {formatEuro(certificacion.base_imponible)}
                        </span>
                    </div>

                    <div className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm">
                        <span className="text-slate-600">
                            IVA ({certificacion.iva_porcentaje}%)
                        </span>
                        <span className="font-semibold text-slate-900">
                            {formatEuro(certificacion.iva_importe)}
                        </span>
                    </div>

                    {certificacion.retencion_porcentaje > 0 && (
                        <div className="flex items-center justify-between rounded-2xl border border-red-200 bg-red-50/70 px-4 py-3 text-sm">
                            <span className="text-slate-600">
                                Retención ({certificacion.retencion_porcentaje}
                                %)
                            </span>
                            <span className="font-semibold text-red-600">
                                -{formatEuro(certificacion.retencion_importe)}
                            </span>
                        </div>
                    )}

                    <div className="mt-2 flex items-center justify-between rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-3.5 text-sm">
                        <span className="font-semibold text-white/90">
                            Total
                        </span>
                        <span className="text-base font-bold text-white">
                            {formatEuro(certificacion.total)}
                        </span>
                    </div>
                </div>
            </div>

            {/* CONTROL PRESUPUESTO */}
            {presupuesto && (
                <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                    <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-emerald-50/40 px-5 py-4 sm:px-6">
                        <div className="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-700">
                            <span className="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Presupuesto
                        </div>
                        <h4 className="mt-3 text-base font-semibold text-slate-900">
                            Control presupuesto
                        </h4>
                        <p className="mt-1 text-sm text-slate-500">
                            Seguimiento del importe contratado y del avance
                            certificado.
                        </p>
                    </div>

                    <div className="space-y-3 px-5 py-5 sm:px-6">
                        <div className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm">
                            <span className="text-slate-600">Contratado</span>
                            <span className="font-semibold text-slate-900">
                                {formatEuro(presupuesto.importe_contratado)}
                            </span>
                        </div>

                        <div className="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm">
                            <span className="text-slate-600">
                                Certificado acumulado
                            </span>
                            <span className="font-semibold text-slate-900">
                                {formatEuro(presupuesto.importe_certificado)}
                            </span>
                        </div>

                        <div
                            className="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm ${
                    presupuesto.importe_contratado -
                        presupuesto.importe_certificado <
                    0
                        ? 'border-red-200 bg-red-50/70'
                        : 'border-emerald-200 bg-emerald-50/70'
                }"
                        >
                            <span className="font-semibold text-slate-700">
                                Pendiente
                            </span>
                            <span
                                className={
                                    presupuesto.importe_contratado -
                                        presupuesto.importe_certificado <
                                    0
                                        ? "font-bold text-red-600"
                                        : "font-bold text-emerald-600"
                                }
                            >
                                {formatEuro(
                                    presupuesto.importe_contratado -
                                        presupuesto.importe_certificado,
                                )}
                            </span>
                        </div>

                        <div className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                            <div className="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
                                <span>Ejecutado</span>
                                <span>{pctCertificado}%</span>
                            </div>

                            <div className="h-2.5 w-full overflow-hidden rounded-full bg-slate-200">
                                <div
                                    className={`h-2.5 rounded-full transition-all ${
                                        pctCertificado > 100
                                            ? "bg-gradient-to-r from-red-500 to-rose-500"
                                            : "bg-gradient-to-r from-emerald-500 to-green-500"
                                    }`}
                                    style={{
                                        width: `${Math.min(pctCertificado, 100)}%`,
                                    }}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
