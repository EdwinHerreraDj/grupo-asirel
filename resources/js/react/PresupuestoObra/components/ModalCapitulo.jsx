import React, { useState, useEffect } from "react";

export default function ModalCapitulo({
    capitulo = null, // null = crear, objeto = editar
    onGuardar,
    onCancelar,
    guardando,
}) {
    const [form, setForm] = useState({
        nombre: "",
        descripcion: "",
    });
    const [errores, setErrores] = useState({});

    useEffect(() => {
        if (capitulo) {
            setForm({
                nombre: capitulo.oficio_nombre ?? "",
                descripcion: capitulo.descripcion ?? "",
            });
        } else {
            setForm({ nombre: "", descripcion: "" });
        }
    }, [capitulo]);

    const validar = () => {
        const nuevos = {};
        if (!form.nombre.trim()) nuevos.nombre = "El nombre es obligatorio.";
        setErrores(nuevos);
        return Object.keys(nuevos).length === 0;
    };

    const handleGuardar = () => {
        if (!validar()) return;
        onGuardar(form);
    };

    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">
                <h3 className="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                    <span className="w-1.5 h-5 bg-primary rounded"></span>
                    {capitulo ? "Editar capítulo" : "Nuevo capítulo"}
                </h3>

                {/* NOMBRE */}
                <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Nombre <span className="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        value={form.nombre}
                        onChange={(e) =>
                            setForm((p) => ({ ...p, nombre: e.target.value }))
                        }
                        className={`w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 ${
                            errores.nombre
                                ? "border-red-400"
                                : "border-gray-300"
                        }`}
                        placeholder="Ej: 01 - Albañilería"
                        autoFocus
                    />
                    {errores.nombre && (
                        <p className="text-red-500 text-xs mt-1">
                            {errores.nombre}
                        </p>
                    )}
                </div>

                {/* DESCRIPCIÓN */}
                <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Descripción
                    </label>
                    <textarea
                        value={form.descripcion}
                        onChange={(e) =>
                            setForm((p) => ({
                                ...p,
                                descripcion: e.target.value,
                            }))
                        }
                        rows={3}
                        className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        placeholder="Descripción opcional del capítulo"
                    />
                </div>

                {/* BOTONES */}
                <div className="flex justify-end gap-2">
                    <button
                        onClick={onCancelar}
                        disabled={guardando}
                        className="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition text-sm disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={handleGuardar}
                        disabled={guardando}
                        className="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 shadow text-sm disabled:opacity-50"
                    >
                        {guardando ? "Guardando..." : "Guardar"}
                    </button>
                </div>
            </div>
        </div>
    );
}
