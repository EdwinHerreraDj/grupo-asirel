// resources/js/react/Proveedores/components/FormularioProveedor.jsx
import React, { useState, useEffect } from "react";

export default function FormularioProveedor({
    proveedor,
    onGuardar,
    onCancelar,
}) {
    const [formData, setFormData] = useState({
        nombre: "",
        cif: "",
        email: "",
        telefono: "",
        direccion: "",
        tipo: "servicios",
        activo: true,
    });

    const [emailsAdicionales, setEmailsAdicionales] = useState([""]);
    const [telefonosAdicionales, setTelefonosAdicionales] = useState([
        { numero: "", etiqueta: "" },
    ]);

    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (proveedor) {
            setFormData({
                nombre: proveedor.nombre || "",
                cif: proveedor.cif || "",
                email: proveedor.email || "",
                telefono: proveedor.telefono || "",
                direccion: proveedor.direccion || "",
                tipo: proveedor.tipo || "servicios",
                activo: proveedor.activo ?? true,
            });

            setEmailsAdicionales(
                proveedor.emails && proveedor.emails.length > 0
                    ? proveedor.emails
                    : [""],
            );

            setTelefonosAdicionales(
                proveedor.telefonos && proveedor.telefonos.length > 0
                    ? proveedor.telefonos.map((t) =>
                          typeof t === "string"
                              ? { numero: t, etiqueta: "" }
                              : t,
                      )
                    : [{ numero: "", etiqueta: "" }],
            );
        }
    }, [proveedor]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: type === "checkbox" ? checked : value,
        }));
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const handleEmailAdicionalChange = (index, value) => {
        const newEmails = [...emailsAdicionales];
        newEmails[index] = value;
        setEmailsAdicionales(newEmails);
    };

    const handleTelefonoAdicionalChange = (index, field, value) => {
        const newTelefonos = [...telefonosAdicionales];
        newTelefonos[index][field] = value;
        setTelefonosAdicionales(newTelefonos);
    };

    const agregarEmail = () => {
        setEmailsAdicionales([...emailsAdicionales, ""]);
    };

    const eliminarEmail = (index) => {
        const newEmails = emailsAdicionales.filter((_, i) => i !== index);
        setEmailsAdicionales(newEmails.length > 0 ? newEmails : [""]);
    };

    const agregarTelefono = () => {
        setTelefonosAdicionales([
            ...telefonosAdicionales,
            { numero: "", etiqueta: "" },
        ]);
    };

    const eliminarTelefono = (index) => {
        const newTelefonos = telefonosAdicionales.filter((_, i) => i !== index);
        setTelefonosAdicionales(
            newTelefonos.length > 0
                ? newTelefonos
                : [{ numero: "", etiqueta: "" }],
        );
    };

    const validateForm = () => {
        const newErrors = {};

        if (!formData.nombre.trim()) {
            newErrors.nombre = "El nombre es obligatorio";
        }

        if (!formData.email.trim()) {
            newErrors.email = "El email principal es obligatorio";
        } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = "El email no es válido";
        }

        if (!formData.telefono.trim()) {
            newErrors.telefono = "El teléfono principal es obligatorio";
        }

        // Validar emails adicionales
        emailsAdicionales.forEach((email, index) => {
            if (email && !/\S+@\S+\.\S+/.test(email)) {
                newErrors[`email_${index}`] = "Email no válido";
            }
        });

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setSaving(true);

        try {
            const emailsLimpios = emailsAdicionales.filter(
                (e) => e.trim() !== "",
            );
            const telefonosLimpios = telefonosAdicionales.filter(
                (t) => t.numero.trim() !== "",
            );

            const data = {
                ...formData,
                emails: emailsLimpios.length > 0 ? emailsLimpios : null,
                telefonos:
                    telefonosLimpios.length > 0 ? telefonosLimpios : null,
            };

            await onGuardar(data);
        } catch (error) {
            console.error("Error saving:", error);
        } finally {
            setSaving(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-10">
            {/* Header */}
            <div>
                <h3 className="text-2xl font-bold text-slate-800">
                    {proveedor ? "Editar Proveedor" : "Nuevo Proveedor"}
                </h3>
                <p className="text-sm text-slate-500 mt-1">
                    Completa la información general del proveedor
                </p>
                <div className="mt-3 h-1 w-14 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full"></div>
            </div>

            {/* Información básica */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Nombre */}
                <div className="md:col-span-2">
                    <label className="block text-sm font-semibold text-slate-700 mb-2">
                        Nombre <span className="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nombre"
                        value={formData.nombre}
                        onChange={handleChange}
                        className={`w-full px-4 py-3 rounded-2xl border bg-slate-50 focus:bg-white transition-all shadow-sm ${
                            errors.nombre
                                ? "border-rose-500 focus:ring-2 focus:ring-rose-500"
                                : "border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        }`}
                        placeholder="Nombre del proveedor"
                    />
                    {errors.nombre && (
                        <span className="text-rose-500 text-xs mt-1 block">
                            {errors.nombre}
                        </span>
                    )}
                </div>

                {/* CIF */}
                <div>
                    <label className="block text-sm font-semibold text-slate-700 mb-2">
                        CIF
                    </label>
                    <input
                        type="text"
                        name="cif"
                        value={formData.cif}
                        onChange={handleChange}
                        className="w-full px-4 py-3 rounded-2xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                        placeholder="A12345678"
                    />
                </div>

                {/* Tipo */}
                <div>
                    <label className="block text-sm font-semibold text-slate-700 mb-2">
                        Tipo de proveedor
                    </label>
                    <select
                        name="tipo"
                        value={formData.tipo}
                        onChange={handleChange}
                        className="w-full px-4 py-3 rounded-2xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                    >
                        <option value="servicios">Servicios</option>
                        <option value="productos">Productos</option>
                        <option value="mixto">Mixto</option>
                    </select>
                </div>

                {/* Email Principal */}
                <div className="md:col-span-2">
                    <label className="block text-sm font-semibold text-slate-700 mb-2">
                        Email Principal <span className="text-rose-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        className={`w-full px-4 py-3 rounded-2xl border bg-slate-50 focus:bg-white transition-all shadow-sm ${
                            errors.email
                                ? "border-rose-500 focus:ring-2 focus:ring-rose-500"
                                : "border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        }`}
                        placeholder="email@ejemplo.com"
                    />
                    {errors.email && (
                        <span className="text-rose-500 text-xs mt-1 block">
                            {errors.email}
                        </span>
                    )}
                </div>
            </div>

            {/* Emails Adicionales */}
            <div className="bg-slate-50/60 border border-slate-200 rounded-2xl p-5">
                <div className="flex justify-between items-center mb-4">
                    <label className="text-sm font-semibold text-slate-700">
                        Emails Adicionales
                    </label>
                    <button
                        type="button"
                        onClick={agregarEmail}
                        className="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition"
                    >
                        <i className="mgc_add_line"></i> Agregar
                    </button>
                </div>

                <div className="space-y-3">
                    {emailsAdicionales.map((email, index) => (
                        <div key={index} className="flex gap-3">
                            <input
                                type="email"
                                value={email}
                                onChange={(e) =>
                                    handleEmailAdicionalChange(
                                        index,
                                        e.target.value,
                                    )
                                }
                                placeholder="email@ejemplo.com"
                                className={`flex-1 px-4 py-3 rounded-2xl border bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm ${
                                    errors[`email_${index}`]
                                        ? "border-rose-500"
                                        : "border-slate-300"
                                }`}
                            />
                            {emailsAdicionales.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => eliminarEmail(index)}
                                    className="w-11 h-11 flex items-center justify-center rounded-2xl border border-slate-200 text-rose-600 hover:bg-rose-50 transition"
                                >
                                    <i className="mgc_delete_line text-lg"></i>
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            {/* Teléfono Principal */}
            <div>
                <label className="block text-sm font-semibold text-slate-700 mb-2">
                    Teléfono Principal <span className="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    name="telefono"
                    value={formData.telefono}
                    onChange={handleChange}
                    className={`w-full px-4 py-3 rounded-2xl border bg-slate-50 focus:bg-white transition-all shadow-sm ${
                        errors.telefono
                            ? "border-rose-500 focus:ring-2 focus:ring-rose-500"
                            : "border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    }`}
                    placeholder="+34 123 456 789"
                />
                {errors.telefono && (
                    <span className="text-rose-500 text-xs mt-1 block">
                        {errors.telefono}
                    </span>
                )}
            </div>

            {/* Teléfonos Adicionales */}
            <div className="bg-slate-50/60 border border-slate-200 rounded-2xl p-5">
                <div className="flex justify-between items-center mb-4">
                    <label className="text-sm font-semibold text-slate-700">
                        Teléfonos Adicionales
                    </label>
                    <button
                        type="button"
                        onClick={agregarTelefono}
                        className="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 transition"
                    >
                        <i className="mgc_add_line"></i> Agregar
                    </button>
                </div>

                <div className="space-y-3">
                    {telefonosAdicionales.map((telefono, index) => (
                        <div
                            key={index}
                            className="flex flex-col sm:flex-row gap-3"
                        >
                            <input
                                type="text"
                                value={telefono.numero}
                                onChange={(e) =>
                                    handleTelefonoAdicionalChange(
                                        index,
                                        "numero",
                                        e.target.value,
                                    )
                                }
                                placeholder="Número"
                                className="flex-1 px-4 py-3 rounded-2xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                            />

                            <input
                                type="text"
                                value={telefono.etiqueta}
                                onChange={(e) =>
                                    handleTelefonoAdicionalChange(
                                        index,
                                        "etiqueta",
                                        e.target.value,
                                    )
                                }
                                placeholder="Etiqueta (Ej: Facturación)"
                                className="sm:w-52 px-4 py-3 rounded-2xl border border-slate-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                            />

                            {telefonosAdicionales.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => eliminarTelefono(index)}
                                    className="w-11 h-11 flex items-center justify-center rounded-2xl border border-slate-200 text-rose-600 hover:bg-rose-50 transition"
                                >
                                    <i className="mgc_delete_line text-lg"></i>
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            {/* Dirección */}
            <div>
                <label className="block text-sm font-semibold text-slate-700 mb-2">
                    Dirección
                </label>
                <textarea
                    name="direccion"
                    value={formData.direccion}
                    onChange={handleChange}
                    rows="2"
                    className="w-full px-4 py-3 rounded-2xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                    placeholder="Dirección completa"
                />
            </div>

            {/* Activo */}
            <div className="flex items-center gap-3">
                <input
                    type="checkbox"
                    name="activo"
                    id="activo"
                    checked={formData.activo}
                    onChange={handleChange}
                    className="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                />
                <label
                    htmlFor="activo"
                    className="text-sm font-medium text-slate-700"
                >
                    Proveedor activo
                </label>
            </div>

            {/* Botones */}
            <div className="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200">
                <button
                    type="button"
                    onClick={onCancelar}
                    className="w-full sm:w-auto px-5 py-3 rounded-2xl bg-white border border-slate-300 text-slate-600 font-medium hover:bg-slate-100 transition-all"
                    disabled={saving}
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    disabled={saving}
                    className="w-full sm:w-auto px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    {saving ? (
                        <>
                            <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Guardando...
                        </>
                    ) : (
                        <>
                            <i className="mgc_save_line"></i>
                            {proveedor ? "Actualizar" : "Guardar"}
                        </>
                    )}
                </button>
            </div>
        </form>
    );
}
