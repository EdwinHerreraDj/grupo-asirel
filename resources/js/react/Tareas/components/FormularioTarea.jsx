import React, { useState, useEffect } from "react";
import useLockBodyScroll from "../../shared/useLockBodyScroll";

const DEFAULT_DATA = {
    titulo: "",
    descripcion: "",
    prioridad: "media",
    estado: "pendiente",
    fecha_limite: "",
    obra_id: "",
    asignado_a: "",
};

export default function FormularioTarea({
    tarea,
    usuarios,
    obras,
    onGuardar,
    onCancelar,
}) {
    useLockBodyScroll(true);

    const [formData, setFormData] = useState(DEFAULT_DATA);
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (tarea) {
            setFormData({
                titulo: tarea.titulo || "",
                descripcion: tarea.descripcion || "",
                prioridad: tarea.prioridad || "media",
                estado: tarea.estado || "pendiente",
                fecha_limite: tarea.fecha_limite
                    ? String(tarea.fecha_limite).substring(0, 10)
                    : "",
                obra_id: tarea.obra_id || "",
                asignado_a: tarea.asignado_a || "",
            });
        }
    }, [tarea]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
        if (errors[name]) setErrors((prev) => ({ ...prev, [name]: null }));
    };

    const validate = () => {
        const newErrors = {};
        if (!formData.titulo.trim())
            newErrors.titulo = "El título es obligatorio.";
        if (!formData.asignado_a)
            newErrors.asignado_a = "Debes asignar la tarea a alguien.";
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!validate()) return;

        setSaving(true);
        try {
            const payload = {
                ...formData,
                obra_id: formData.obra_id || null,
                fecha_limite: formData.fecha_limite || null,
                asignado_a: parseInt(formData.asignado_a, 10),
            };
            await onGuardar(payload);
        } catch (err) {
            console.error(err);
        } finally {
            setSaving(false);
        }
    };

    const inputBase =
        "w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500";
    const inputError =
        "border-red-400 focus:border-red-500 focus:ring-red-500";

    return (
        <div
            className="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            role="dialog"
            aria-modal="true"
        >
            <div className="w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col">
                {/* Header */}
                <div className="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5 shrink-0">
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <div className="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span className="h-2 w-2 rounded-full bg-cyan-500"></span>
                                {tarea ? "Editar" : "Nueva"}
                            </div>
                            <h3 className="mt-2 text-lg font-semibold text-slate-900">
                                {tarea ? "Editar tarea" : "Nueva tarea"}
                            </h3>
                            <p className="mt-0.5 text-sm text-slate-500">
                                Los campos con * son obligatorios.
                            </p>
                        </div>
                        <button
                            onClick={onCancelar}
                            disabled={saving}
                            aria-label="Cerrar"
                            className="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50"
                        >
                            <i className="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                {/* Body */}
                <form
                    onSubmit={handleSubmit}
                    autoComplete="off"
                    className="overflow-y-auto overscroll-contain px-6 py-5 space-y-4"
                >
                    {/* Título */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Título <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="titulo"
                            value={formData.titulo}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="Ej: Revisar planos de obra X"
                            className={`${inputBase} ${
                                errors.titulo ? inputError : ""
                            }`}
                        />
                        {errors.titulo && (
                            <p className="mt-1 text-xs text-red-600">
                                {errors.titulo}
                            </p>
                        )}
                    </div>

                    {/* Descripción */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Descripción
                        </label>
                        <textarea
                            name="descripcion"
                            value={formData.descripcion}
                            onChange={handleChange}
                            rows={3}
                            autoComplete="off"
                            placeholder="Detalle adicional de la tarea…"
                            className={`${inputBase} resize-none`}
                        />
                    </div>

                    {/* Asignado + prioridad */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Asignar a <span className="text-red-500">*</span>
                            </label>
                            <select
                                name="asignado_a"
                                value={formData.asignado_a}
                                onChange={handleChange}
                                className={`${inputBase} ${
                                    errors.asignado_a ? inputError : ""
                                }`}
                            >
                                <option value="">— Selecciona usuario —</option>
                                {usuarios.map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {u.name}
                                    </option>
                                ))}
                            </select>
                            {errors.asignado_a && (
                                <p className="mt-1 text-xs text-red-600">
                                    {errors.asignado_a}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Prioridad
                            </label>
                            <select
                                name="prioridad"
                                value={formData.prioridad}
                                onChange={handleChange}
                                className={inputBase}
                            >
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                    </div>

                    {/* Estado + fecha límite */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Estado
                            </label>
                            <select
                                name="estado"
                                value={formData.estado}
                                onChange={handleChange}
                                className={inputBase}
                            >
                                <option value="pendiente">Pendiente</option>
                                <option value="en_curso">En curso</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Fecha límite
                            </label>
                            <input
                                type="date"
                                name="fecha_limite"
                                value={formData.fecha_limite}
                                onChange={handleChange}
                                className={inputBase}
                            />
                        </div>
                    </div>

                    {/* Obra (opcional) */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Obra <span className="text-slate-400 text-xs">(opcional)</span>
                        </label>
                        <select
                            name="obra_id"
                            value={formData.obra_id}
                            onChange={handleChange}
                            className={inputBase}
                        >
                            <option value="">— Sin obra asociada —</option>
                            {obras.map((o) => (
                                <option key={o.id} value={o.id}>
                                    {o.nombre}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Botones */}
                    <div className="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-200">
                        <button
                            type="button"
                            onClick={onCancelar}
                            disabled={saving}
                            className="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:opacity-60"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={saving}
                            className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow hover:from-cyan-500 hover:to-blue-500 disabled:opacity-60"
                        >
                            {saving ? (
                                <>
                                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    Guardando…
                                </>
                            ) : (
                                <>
                                    <i className="mgc_save_line"></i>
                                    {tarea ? "Guardar cambios" : "Crear tarea"}
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
