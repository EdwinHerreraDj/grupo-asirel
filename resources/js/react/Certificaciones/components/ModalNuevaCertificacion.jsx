import React, { useState } from "react";

export default function ModalNuevaCertificacion({
    obraId,
    oficios,
    clientes,
    onGuardar,
    onCancelar,
}) {
    const [form, setForm] = useState({
        cliente_id: "",
        obra_gasto_categoria_id: "",
        fecha_ingreso: new Date().toISOString().split("T")[0],
        fecha_contable: "",
        fecha_vencimiento: "",
        iva_porcentaje: 21,
        retencion_porcentaje: 0,
        numero_certificacion: "",
    });
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);

    const set = (campo, valor) => {
        setForm((prev) => ({ ...prev, [campo]: valor }));
        if (errores[campo]) setErrores((prev) => ({ ...prev, [campo]: null }));
    };

    const validar = () => {
        const e = {};
        if (!form.cliente_id) e.cliente_id = "Obligatorio.";
        if (!form.obra_gasto_categoria_id)
            e.obra_gasto_categoria_id = "Obligatorio.";
        if (!form.fecha_ingreso) e.fecha_ingreso = "Obligatorio.";
        setErrores(e);
        return Object.keys(e).length === 0;
    };

    const handleGuardar = async () => {
        if (!validar()) return;
        setGuardando(true);
        try {
            await onGuardar(form);
        } finally {
            setGuardando(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
            <div className="max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.28)]">
                <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-cyan-50/50 px-6 py-5">
                    <div className="flex items-start gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-[0_10px_24px_rgba(37,99,235,0.25)]">
                            <i className="mgc_add_line text-lg"></i>
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-lg font-semibold tracking-tight text-slate-900">
                                Nueva certificación
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                Completa los datos principales para registrar
                                una nueva certificación.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="max-h-[calc(90vh-88px)] overflow-y-auto px-6 py-6">
                    <div className="space-y-6">
                        {/* NÚMERO */}
                        <div className="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                            <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Nº Certificación
                            </label>
                            <input
                                type="text"
                                value={form.numero_certificacion}
                                onChange={(e) =>
                                    set("numero_certificacion", e.target.value)
                                }
                                placeholder="Ej: 2026-001"
                                className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                            />
                        </div>

                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {/* CLIENTE */}
                            <div className="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Cliente{" "}
                                    <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={form.cliente_id}
                                    onChange={(e) =>
                                        set("cliente_id", e.target.value)
                                    }
                                    className={`h-11 w-full rounded-xl border bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 ${
                                        errores.cliente_id
                                            ? "border-red-400 focus:border-red-400"
                                            : "border-slate-300 focus:border-cyan-500"
                                    }`}
                                >
                                    <option value="">
                                        — Selecciona cliente —
                                    </option>
                                    {clientes.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.nombre}
                                        </option>
                                    ))}
                                </select>
                                {errores.cliente_id && (
                                    <p className="mt-2 text-xs font-medium text-red-500">
                                        {errores.cliente_id}
                                    </p>
                                )}
                            </div>

                            {/* OFICIO */}
                            <div className="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                                <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Oficio / Capítulo{" "}
                                    <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={form.obra_gasto_categoria_id}
                                    onChange={(e) =>
                                        set(
                                            "obra_gasto_categoria_id",
                                            e.target.value,
                                        )
                                    }
                                    className={`h-11 w-full rounded-xl border bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 ${
                                        errores.obra_gasto_categoria_id
                                            ? "border-red-400 focus:border-red-400"
                                            : "border-slate-300 focus:border-cyan-500"
                                    }`}
                                >
                                    <option value="">
                                        — Selecciona oficio —
                                    </option>
                                    {oficios.map((o) => (
                                        <option key={o.id} value={o.id}>
                                            {o.nombre}
                                        </option>
                                    ))}
                                </select>
                                {errores.obra_gasto_categoria_id && (
                                    <p className="mt-2 text-xs font-medium text-red-500">
                                        {errores.obra_gasto_categoria_id}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* FECHAS */}
                        <div className="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                            <div className="mb-4">
                                <h4 className="text-sm font-semibold text-slate-900">
                                    Fechas
                                </h4>
                                <p className="mt-1 text-sm text-slate-500">
                                    Define las fechas principales de la
                                    certificación.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                <div>
                                    <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Fecha ingreso{" "}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={form.fecha_ingreso}
                                        onChange={(e) =>
                                            set("fecha_ingreso", e.target.value)
                                        }
                                        className={`h-11 w-full rounded-xl border bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/20 ${
                                            errores.fecha_ingreso
                                                ? "border-red-400 focus:border-red-400"
                                                : "border-slate-300 focus:border-cyan-500"
                                        }`}
                                    />
                                    {errores.fecha_ingreso && (
                                        <p className="mt-2 text-xs font-medium text-red-500">
                                            {errores.fecha_ingreso}
                                        </p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Fecha contable
                                    </label>
                                    <input
                                        type="date"
                                        value={form.fecha_contable}
                                        onChange={(e) =>
                                            set(
                                                "fecha_contable",
                                                e.target.value,
                                            )
                                        }
                                        className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                    />
                                </div>

                                <div className="md:col-span-2 xl:col-span-1">
                                    <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Fecha vencimiento
                                    </label>
                                    <input
                                        type="date"
                                        value={form.fecha_vencimiento}
                                        onChange={(e) =>
                                            set(
                                                "fecha_vencimiento",
                                                e.target.value,
                                            )
                                        }
                                        className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* FISCALIDAD */}
                        <div className="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                            <div className="mb-4">
                                <h4 className="text-sm font-semibold text-slate-900">
                                    Fiscalidad
                                </h4>
                                <p className="mt-1 text-sm text-slate-500">
                                    Configura los porcentajes fiscales
                                    aplicables.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        IVA %
                                    </label>
                                    <input
                                        type="number"
                                        value={form.iva_porcentaje}
                                        onChange={(e) =>
                                            set(
                                                "iva_porcentaje",
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                    />
                                </div>

                                <div>
                                    <label className="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Retención %
                                    </label>
                                    <input
                                        type="number"
                                        value={form.retencion_porcentaje}
                                        onChange={(e) =>
                                            set(
                                                "retencion_porcentaje",
                                                e.target.value,
                                            )
                                        }
                                        min="0"
                                        step="0.01"
                                        className="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-6 flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                        <button
                            onClick={onCancelar}
                            disabled={guardando}
                            className="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                        >
                            Cancelar
                        </button>

                        <button
                            onClick={handleGuardar}
                            disabled={guardando}
                            className="inline-flex h-11 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500 disabled:opacity-50"
                        >
                            {guardando ? "Guardando..." : "Crear certificación"}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
