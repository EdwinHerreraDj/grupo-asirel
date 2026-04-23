import React, { useState } from "react";
import { calcularImporte, formatEuro, redondear } from "../utils/calculos";

export default function PartidaFilaEdicion({
    inicial,
    modo = "coste",
    guardando,
    esNueva = false,
    onGuardar,
    onCancelar,
}) {
    const soloVenta = modo === "venta";

    const [form, setForm] = useState({
        codigo: inicial.codigo ?? "",
        descripcion: inicial.descripcion ?? "",
        unidad: inicial.unidad ?? "",
        medicion:
            inicial.medicion === null || inicial.medicion === undefined
                ? ""
                : redondear(inicial.medicion),
        precio_unitario:
            inicial.precio_unitario === null ||
            inicial.precio_unitario === undefined
                ? ""
                : redondear(inicial.precio_unitario),
    });

    const [errores, setErrores] = useState({});

    const importePreview = calcularImporte(form.medicion, form.precio_unitario);

    const handleChange = (campo, valor) => {
        setForm((prev) => ({ ...prev, [campo]: valor }));
        if (errores[campo]) {
            setErrores((prev) => ({ ...prev, [campo]: null }));
        }
    };

    const validar = () => {
        const nuevosErrores = {};

        // Descripción solo obligatoria en modo coste
        if (!soloVenta && !form.descripcion.trim()) {
            nuevosErrores.descripcion = "La descripción es obligatoria.";
        }
        if (
            form.medicion === "" ||
            isNaN(form.medicion) ||
            parseFloat(form.medicion) < 0
        ) {
            nuevosErrores.medicion = "Medición inválida.";
        }
        if (
            form.precio_unitario === "" ||
            isNaN(form.precio_unitario) ||
            parseFloat(form.precio_unitario) < 0
        ) {
            nuevosErrores.precio_unitario = "Precio inválido.";
        }

        setErrores(nuevosErrores);
        return Object.keys(nuevosErrores).length === 0;
    };

    const handleGuardar = () => {
        if (!validar()) return;
        onGuardar({
            ...form,
            medicion: parseFloat(form.medicion),
            precio_unitario: parseFloat(form.precio_unitario),
        });
    };

    const rowClass = esNueva ? "border-t bg-blue-50" : "border-t bg-yellow-50";

    const inputBase =
        "w-full rounded-xl border bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:ring-4 border-slate-200 focus:border-cyan-400 focus:ring-cyan-100";
    const inputError = "border-red-300 focus:border-red-400 focus:ring-red-100";
    const readonlyCell = "px-3 py-3 text-sm text-gray-500";

    return (
        <tr className={rowClass}>
            {/* CÓDIGO */}
            <td className="px-3 py-3 align-top">
                {soloVenta ? (
                    <span className={readonlyCell}>{form.codigo || "—"}</span>
                ) : (
                    <input
                        type="text"
                        value={form.codigo}
                        onChange={(e) => handleChange("codigo", e.target.value)}
                        placeholder="Cód."
                        className={inputBase}
                    />
                )}
            </td>

            {/* DESCRIPCIÓN */}
            <td className="px-3 py-3 align-top">
                {soloVenta ? (
                    <span className="px-1 text-sm text-gray-700">
                        {form.descripcion}
                    </span>
                ) : (
                    <>
                        <input
                            type="text"
                            value={form.descripcion}
                            onChange={(e) =>
                                handleChange("descripcion", e.target.value)
                            }
                            placeholder="Descripción *"
                            className={`${inputBase} ${errores.descripcion ? inputError : ""}`}
                        />
                        {errores.descripcion && (
                            <p className="mt-1.5 text-xs font-medium text-red-500">
                                {errores.descripcion}
                            </p>
                        )}
                    </>
                )}
            </td>

            {/* UNIDAD */}
            <td className="px-3 py-3 align-top">
                {soloVenta ? (
                    <span className={readonlyCell + " block text-center"}>
                        {form.unidad || "—"}
                    </span>
                ) : (
                    <input
                        type="text"
                        value={form.unidad}
                        onChange={(e) => handleChange("unidad", e.target.value)}
                        placeholder="ud"
                        className={inputBase + " text-center"}
                    />
                )}
            </td>

            {/* MEDICIÓN — editable siempre */}
            <td className="px-3 py-3 align-top">
                <input
                    type="number"
                    step="0.0001"
                    value={form.medicion}
                    onChange={(e) => handleChange("medicion", e.target.value)}
                    placeholder="0"
                    className={`${inputBase} text-right ${errores.medicion ? inputError : ""}`}
                />
                {errores.medicion && (
                    <p className="mt-1.5 text-xs font-medium text-red-500">
                        {errores.medicion}
                    </p>
                )}
            </td>

            {/* PRECIO UNITARIO — editable siempre */}
            <td className="px-3 py-3 align-top">
                <input
                    type="number"
                    step="0.0001"
                    value={form.precio_unitario}
                    onChange={(e) =>
                        handleChange("precio_unitario", e.target.value)
                    }
                    placeholder="0.00"
                    className={`${inputBase} text-right ${errores.precio_unitario ? inputError : ""}`}
                />
                {errores.precio_unitario && (
                    <p className="mt-1.5 text-xs font-medium text-red-500">
                        {errores.precio_unitario}
                    </p>
                )}
            </td>

            {/* IMPORTE PREVIEW */}
            <td className="px-3 py-3 align-top text-right">
                <div className="inline-flex min-h-[42px] items-center justify-end rounded-xl bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-800">
                    {formatEuro(importePreview)}
                </div>
            </td>

            {/* ACCIONES */}
            <td className="px-3 py-3 align-top">
                <div className="flex justify-end gap-2">
                    <button
                        onClick={handleGuardar}
                        disabled={guardando}
                        className="inline-flex items-center rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:from-cyan-600 hover:to-blue-700 disabled:opacity-50"
                    >
                        {guardando ? "..." : "Guardar"}
                    </button>
                    <button
                        onClick={onCancelar}
                        disabled={guardando}
                        className="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                </div>
            </td>
        </tr>
    );
}
