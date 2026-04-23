import React, { useState, useEffect } from "react";

const TIPOS = {
    material: "Material",
    mano_obra: "Mano de obra",
    servicio: "Servicio",
    mixto: "Mixto",
};

const DEFAULT_DATA = {
    nombre: "",
    cif: "",
    email: "",
    telefono: "",
    direccion: "",
    codigo_postal: "",
    poblacion: "",
    provincia: "",
    pais: "",
    tipo: "material",
    activo: true,
};

export default function FormularioProveedor({
    proveedor,
    onGuardar,
    onCancelar,
}) {
    const [formData, setFormData] = useState(DEFAULT_DATA);
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
                codigo_postal: proveedor.codigo_postal || "",
                poblacion: proveedor.poblacion || "",
                provincia: proveedor.provincia || "",
                pais: proveedor.pais || "",
                tipo: proveedor.tipo || "material",
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
        if (errors[name]) setErrors((prev) => ({ ...prev, [name]: null }));
    };

    const handleEmailAdicionalChange = (index, value) => {
        const next = [...emailsAdicionales];
        next[index] = value;
        setEmailsAdicionales(next);
    };

    const handleTelefonoAdicionalChange = (index, field, value) => {
        const next = [...telefonosAdicionales];
        next[index] = { ...next[index], [field]: value };
        setTelefonosAdicionales(next);
    };

    const agregarEmail = () =>
        setEmailsAdicionales([...emailsAdicionales, ""]);

    const eliminarEmail = (index) => {
        const next = emailsAdicionales.filter((_, i) => i !== index);
        setEmailsAdicionales(next.length > 0 ? next : [""]);
    };

    const agregarTelefono = () =>
        setTelefonosAdicionales([
            ...telefonosAdicionales,
            { numero: "", etiqueta: "" },
        ]);

    const eliminarTelefono = (index) => {
        const next = telefonosAdicionales.filter((_, i) => i !== index);
        setTelefonosAdicionales(
            next.length > 0 ? next : [{ numero: "", etiqueta: "" }],
        );
    };

    const validateForm = () => {
        const newErrors = {};
        if (!formData.nombre.trim()) {
            newErrors.nombre = "El nombre es obligatorio.";
        }
        if (formData.email && !/\S+@\S+\.\S+/.test(formData.email)) {
            newErrors.email = "El email no es válido.";
        }
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
        if (!validateForm()) return;

        setSaving(true);
        try {
            const emailsLimpios = emailsAdicionales.filter(
                (em) => em.trim() !== "",
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

    const inputBase =
        "w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500";
    const inputError =
        "border-red-400 focus:border-red-500 focus:ring-red-500";

    return (
        <form onSubmit={handleSubmit} className="space-y-6" autoComplete="off">
            {/* Truco anti-autofill de Chrome: campos honeypot ocultos */}
            <input type="text" name="fakeusernameremembered" autoComplete="username" className="hidden" tabIndex={-1} aria-hidden="true" />
            <input type="password" name="fakepasswordremembered" autoComplete="new-password" className="hidden" tabIndex={-1} aria-hidden="true" />

            {/* Datos básicos */}
            <section className="space-y-4">
                <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Datos básicos
                </h4>

                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        Nombre <span className="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="nombre"
                        value={formData.nombre}
                        onChange={handleChange}
                        autoComplete="off"
                        placeholder="Ej: Materiales Construmat S.L."
                        className={`${inputBase} ${
                            errors.nombre ? inputError : ""
                        }`}
                    />
                    {errors.nombre && (
                        <p className="mt-1 text-xs text-red-600">
                            {errors.nombre}
                        </p>
                    )}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            CIF
                        </label>
                        <input
                            type="text"
                            name="cif"
                            value={formData.cif}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="A12345678"
                            className={`${inputBase} font-mono`}
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Tipo
                        </label>
                        <select
                            name="tipo"
                            value={formData.tipo}
                            onChange={handleChange}
                            className={inputBase}
                        >
                            {Object.entries(TIPOS).map(([k, v]) => (
                                <option key={k} value={k}>
                                    {v}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
            </section>

            {/* Contacto principal */}
            <section className="space-y-4">
                <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Contacto principal
                </h4>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="email@ejemplo.com"
                            className={`${inputBase} ${
                                errors.email ? inputError : ""
                            }`}
                        />
                        {errors.email && (
                            <p className="mt-1 text-xs text-red-600">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Teléfono
                        </label>
                        <input
                            type="text"
                            name="telefono"
                            value={formData.telefono}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="+34 123 456 789"
                            className={inputBase}
                        />
                    </div>
                </div>
            </section>

            {/* Emails adicionales */}
            <section className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                <div className="flex items-center justify-between mb-3">
                    <h4 className="text-sm font-medium text-slate-700">
                        Emails adicionales
                    </h4>
                    <button
                        type="button"
                        onClick={agregarEmail}
                        className="inline-flex items-center gap-1 text-xs font-semibold text-cyan-700 hover:text-cyan-800"
                    >
                        <i className="mgc_add_line"></i> Añadir
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
                                autoComplete="off"
                                placeholder="email@ejemplo.com"
                                className={`${inputBase} bg-white ${
                                    errors[`email_${index}`] ? inputError : ""
                                }`}
                            />
                            {emailsAdicionales.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => eliminarEmail(index)}
                                    className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200"
                                >
                                    <i className="mgc_delete_line"></i>
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            </section>

            {/* Teléfonos adicionales */}
            <section className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                <div className="flex items-center justify-between mb-3">
                    <h4 className="text-sm font-medium text-slate-700">
                        Teléfonos adicionales
                    </h4>
                    <button
                        type="button"
                        onClick={agregarTelefono}
                        className="inline-flex items-center gap-1 text-xs font-semibold text-cyan-700 hover:text-cyan-800"
                    >
                        <i className="mgc_add_line"></i> Añadir
                    </button>
                </div>

                <div className="space-y-2">
                    {telefonosAdicionales.map((telefono, index) => (
                        <div
                            key={index}
                            className="flex flex-col sm:flex-row gap-2"
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
                                autoComplete="off"
                                placeholder="Número"
                                className={`${inputBase} bg-white flex-1`}
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
                                autoComplete="off"
                                placeholder="Etiqueta (Ej: Facturación)"
                                className={`${inputBase} bg-white sm:w-56`}
                            />
                            {telefonosAdicionales.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() => eliminarTelefono(index)}
                                    className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200"
                                >
                                    <i className="mgc_delete_line"></i>
                                </button>
                            )}
                        </div>
                    ))}
                </div>
            </section>

            {/* Dirección */}
            <section className="space-y-4">
                <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Dirección
                </h4>

                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        Dirección
                    </label>
                    <input
                        type="text"
                        name="direccion"
                        value={formData.direccion}
                        onChange={handleChange}
                        autoComplete="off"
                        placeholder="Calle, número, piso…"
                        className={inputBase}
                    />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Código postal
                        </label>
                        <input
                            type="text"
                            name="codigo_postal"
                            value={formData.codigo_postal}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="28001"
                            className={inputBase}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Población
                        </label>
                        <input
                            type="text"
                            name="poblacion"
                            value={formData.poblacion}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="Madrid"
                            className={inputBase}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Provincia
                        </label>
                        <input
                            type="text"
                            name="provincia"
                            value={formData.provincia}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="Madrid"
                            className={inputBase}
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            País
                        </label>
                        <input
                            type="text"
                            name="pais"
                            value={formData.pais}
                            onChange={handleChange}
                            autoComplete="off"
                            placeholder="España"
                            className={inputBase}
                        />
                    </div>
                </div>
            </section>

            {/* Estado */}
            <section className="space-y-4">
                <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Estado
                </h4>

                <label className="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="activo"
                        id="activo"
                        checked={formData.activo}
                        onChange={handleChange}
                        className="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                    />
                    <span className="text-sm text-slate-700">
                        Proveedor activo
                    </span>
                </label>
            </section>

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
                            {proveedor ? "Guardar cambios" : "Crear proveedor"}
                        </>
                    )}
                </button>
            </div>
        </form>
    );
}
