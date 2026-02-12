// resources/js/react/Proveedores/components/ProveedorRow.jsx
import React from "react";

export default function ProveedorRow({ proveedor, onEditar, onEliminar }) {
    const getTodosLosTelefonos = () => {
        const telefonos = [];

        // Teléfono principal
        if (proveedor.telefono) {
            telefonos.push({
                numero: proveedor.telefono,
                etiqueta: "Principal",
            });
        }

        // Teléfonos adicionales
        if (proveedor.telefonos && Array.isArray(proveedor.telefonos)) {
            proveedor.telefonos.forEach((tel) => {
                if (typeof tel === "string") {
                    telefonos.push({ numero: tel, etiqueta: "" });
                } else if (tel.numero) {
                    telefonos.push(tel);
                }
            });
        }

        return telefonos.filter((t) => t.numero);
    };

    const getTodosLosEmails = () => {
        const emails = [];
        if (proveedor.email) emails.push(proveedor.email);
        if (proveedor.emails && Array.isArray(proveedor.emails)) {
            emails.push(...proveedor.emails);
        }
        return emails.filter(Boolean);
    };

    const telefonos = getTodosLosTelefonos();
    const emails = getTodosLosEmails();

    const getTipoBadge = (tipo) => {
        const tipos = {
            servicios:
                "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400",
            productos:
                "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400",
            mixto: "bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400",
        };
        return (
            tipos[tipo] ||
            "bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400"
        );
    };

    return (
        <tr className="group hover:bg-slate-50 transition-colors duration-150">
            {/* Nombre */}
            <td className="px-6 py-4 align-top">
                <div className="font-semibold text-slate-800">
                    {proveedor.nombre}
                </div>
                {proveedor.direccion && (
                    <div className="text-xs text-slate-500 mt-1 line-clamp-1 lg:hidden leading-relaxed">
                        {proveedor.direccion}
                    </div>
                )}
            </td>

            {/* CIF */}
            <td className="px-6 py-4 text-slate-600 align-top">
                {proveedor.cif || "—"}
            </td>

            {/* Teléfonos con etiquetas */}
            <td className="px-6 py-4 align-top">
                <div className="space-y-2">
                    {telefonos.length > 0 ? (
                        <>
                            {telefonos[0] && (
                                <div className="flex items-center gap-2">
                                    <span className="font-medium text-slate-800">
                                        {telefonos[0].numero}
                                    </span>
                                    {telefonos[0].etiqueta && (
                                        <span className="px-2.5 py-0.5 text-xs font-semibold bg-cyan-100 text-cyan-700 rounded-full">
                                            {telefonos[0].etiqueta}
                                        </span>
                                    )}
                                </div>
                            )}

                            {telefonos.slice(1).map((tel, index) => (
                                <div
                                    key={index}
                                    className="flex items-center gap-2"
                                >
                                    <span className="text-xs text-slate-500">
                                        {tel.numero}
                                    </span>
                                    {tel.etiqueta && (
                                        <span className="px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-600 rounded-full">
                                            {tel.etiqueta}
                                        </span>
                                    )}
                                </div>
                            ))}
                        </>
                    ) : (
                        <span className="text-slate-400">—</span>
                    )}
                </div>
            </td>

            {/* Emails */}
            <td className="px-6 py-4 align-top">
                <div className="space-y-1 max-w-xs">
                    {emails.length > 0 ? (
                        <>
                            <div className="font-medium text-slate-800 truncate">
                                {emails[0]}
                            </div>
                            {emails.slice(1).map((email, index) => (
                                <div
                                    key={index}
                                    className="text-xs text-slate-500 truncate"
                                >
                                    {email}
                                </div>
                            ))}
                        </>
                    ) : (
                        <span className="text-slate-400">—</span>
                    )}
                </div>
            </td>

            {/* Tipo - oculta en móvil */}
            <td className="px-6 py-4 hidden xl:table-cell align-top">
                {proveedor.tipo && (
                    <span
                        className={`inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full ${getTipoBadge(proveedor.tipo)}`}
                    >
                        {proveedor.tipo.charAt(0).toUpperCase() +
                            proveedor.tipo.slice(1)}
                    </span>
                )}
            </td>

            {/* Activo */}
            <td className="px-6 py-4 text-center align-top">
                <span
                    className={`
                inline-flex items-center justify-center px-3 py-1 text-xs font-semibold rounded-full
                ${
                    proveedor.activo
                        ? "bg-emerald-100 text-emerald-700"
                        : "bg-rose-100 text-rose-600"
                }
            `}
                >
                    {proveedor.activo ? "Activo" : "Inactivo"}
                </span>
            </td>

            {/* Acciones */}
            <td className="px-6 py-4 align-top">
                <div className="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition">
                    <button
                        onClick={() => onEditar(proveedor)}
                        className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all"
                        title="Editar"
                    >
                        <i className="mgc_edit_2_line text-lg"></i>
                    </button>

                    <button
                        onClick={() => onEliminar(proveedor)}
                        className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all"
                        title="Eliminar"
                    >
                        <i className="mgc_delete_line text-lg"></i>
                    </button>
                </div>
            </td>
        </tr>
    );
}
