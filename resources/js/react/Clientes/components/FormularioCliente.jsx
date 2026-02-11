// resources/js/react/Clientes/components/FormularioCliente.jsx
import React, { useState, useEffect } from "react";

export default function FormularioCliente({ cliente, onGuardar, onCancelar }) {
    const [formData, setFormData] = useState({
        nombre: "",
        cif: "",
        email: "",
        telefono: "",
        direccion: "",
        descripcion: "",
        activo: true,
    });

    const [emailsAdicionales, setEmailsAdicionales] = useState([""]);
    const [telefonosAdicionales, setTelefonosAdicionales] = useState([""]);
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (cliente) {
            setFormData({
                nombre: cliente.nombre || "",
                cif: cliente.cif || "",
                email: cliente.email || "",
                telefono: cliente.telefono || "",
                direccion: cliente.direccion || "",
                descripcion: cliente.descripcion || "",
                activo: cliente.activo ?? true,
            });

            setEmailsAdicionales(
                cliente.emails && cliente.emails.length > 0
                    ? cliente.emails
                    : [""],
            );

            setTelefonosAdicionales(
                cliente.telefonos && cliente.telefonos.length > 0
                    ? cliente.telefonos
                    : [""],
            );
        }
    }, [cliente]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: type === "checkbox" ? checked : value,
        }));
        // Limpiar error del campo
        if (errors[name]) {
            setErrors((prev) => ({ ...prev, [name]: null }));
        }
    };

    const handleEmailAdicionalChange = (index, value) => {
        const newEmails = [...emailsAdicionales];
        newEmails[index] = value;
        setEmailsAdicionales(newEmails);
    };

    const handleTelefonoAdicionalChange = (index, value) => {
        const newTelefonos = [...telefonosAdicionales];
        newTelefonos[index] = value;
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
        setTelefonosAdicionales([...telefonosAdicionales, ""]);
    };

    const eliminarTelefono = (index) => {
        const newTelefonos = telefonosAdicionales.filter((_, i) => i !== index);
        setTelefonosAdicionales(newTelefonos.length > 0 ? newTelefonos : [""]);
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
                (t) => t.trim() !== "",
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
        <form onSubmit={handleSubmit} className="space-y-4">
            <h3 className="text-xl font-bold text-gray-800 dark:text-white mb-4">
                {cliente ? "Editar Cliente" : "Nuevo Cliente"}
            </h3>

            {/* Nombre */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nombre <span className="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="nombre"
                    value={formData.nombre}
                    onChange={handleChange}
                    className={`w-full px-4 py-2 rounded-lg border ${
                        errors.nombre
                            ? "border-red-500 focus:ring-red-500"
                            : "border-gray-300 focus:ring-primary"
                    } focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white`}
                    placeholder="Nombre del cliente"
                />
                {errors.nombre && (
                    <span className="text-red-500 text-xs mt-1">
                        {errors.nombre}
                    </span>
                )}
            </div>

            {/* CIF */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    CIF
                </label>
                <input
                    type="text"
                    name="cif"
                    value={formData.cif}
                    onChange={handleChange}
                    className="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="A12345678"
                />
            </div>

            {/* Email Principal */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Email Principal <span className="text-red-500">*</span>
                </label>
                <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className={`w-full px-4 py-2 rounded-lg border ${
                        errors.email
                            ? "border-red-500 focus:ring-red-500"
                            : "border-gray-300 focus:ring-primary"
                    } focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white`}
                    placeholder="email@ejemplo.com"
                />
                {errors.email && (
                    <span className="text-red-500 text-xs mt-1">
                        {errors.email}
                    </span>
                )}
            </div>

            {/* Emails Adicionales */}
            <div>
                <div className="flex justify-between items-center mb-2">
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Emails Adicionales
                    </label>
                    <button
                        type="button"
                        onClick={agregarEmail}
                        className="text-sm text-primary hover:text-primary/80 font-medium flex items-center gap-1"
                    >
                        <i className="mgc_add_line"></i> Agregar email
                    </button>
                </div>

                <div className="space-y-2">
                    {emailsAdicionales.map((email, index) => (
                        <div key={index} className="flex gap-2">
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
                                className={`flex-1 px-4 py-2 rounded-lg border ${
                                    errors[`email_${index}`]
                                        ? "border-red-500"
                                        : "border-gray-300"
                                } focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white`}
                            />
                            {emailsAdicionales.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => eliminarEmail(index)}
                                    className="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
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
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Teléfono Principal <span className="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="telefono"
                    value={formData.telefono}
                    onChange={handleChange}
                    className={`w-full px-4 py-2 rounded-lg border ${
                        errors.telefono
                            ? "border-red-500 focus:ring-red-500"
                            : "border-gray-300 focus:ring-primary"
                    } focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white`}
                    placeholder="+34 123 456 789"
                />
                {errors.telefono && (
                    <span className="text-red-500 text-xs mt-1">
                        {errors.telefono}
                    </span>
                )}
            </div>

            {/* Teléfonos Adicionales */}
            <div>
                <div className="flex justify-between items-center mb-2">
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Teléfonos Adicionales
                    </label>
                    <button
                        type="button"
                        onClick={agregarTelefono}
                        className="text-sm text-primary hover:text-primary/80 font-medium flex items-center gap-1"
                    >
                        <i className="mgc_add_line"></i> Agregar teléfono
                    </button>
                </div>

                <div className="space-y-2">
                    {telefonosAdicionales.map((telefono, index) => (
                        <div key={index} className="flex gap-2">
                            <input
                                type="text"
                                value={telefono}
                                onChange={(e) =>
                                    handleTelefonoAdicionalChange(
                                        index,
                                        e.target.value,
                                    )
                                }
                                placeholder="+34 123 456 789"
                                className="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                            {telefonosAdicionales.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => eliminarTelefono(index)}
                                    className="px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
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
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Dirección
                </label>
                <textarea
                    name="direccion"
                    value={formData.direccion}
                    onChange={handleChange}
                    rows="2"
                    className="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Dirección completa"
                />
            </div>

            {/* Descripción */}
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Descripción
                </label>
                <textarea
                    name="descripcion"
                    value={formData.descripcion}
                    onChange={handleChange}
                    rows="3"
                    className="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Información adicional del cliente"
                />
            </div>

            {/* Activo */}
            <div className="flex items-center gap-2">
                <input
                    type="checkbox"
                    name="activo"
                    id="activo"
                    checked={formData.activo}
                    onChange={handleChange}
                    className="rounded border-gray-300 text-primary focus:ring-primary"
                />
                <label
                    htmlFor="activo"
                    className="text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                    Activo
                </label>
            </div>

            {/* Botones */}
            <div className="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    onClick={onCancelar}
                    className="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition"
                    disabled={saving}
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    disabled={saving}
                    className="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    {saving ? (
                        <>
                            <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Guardando...
                        </>
                    ) : (
                        <>
                            <i className="mgc_save_line"></i>
                            {cliente ? "Actualizar" : "Guardar"}
                        </>
                    )}
                </button>
            </div>
        </form>
    );
}
