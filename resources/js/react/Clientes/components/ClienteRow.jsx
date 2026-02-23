// resources/js/react/Clientes/components/ClienteRow.jsx
import React from "react";

export default function ClienteRow({ cliente, onEditar, onEliminar }) {
    const getTodosLosTelefonos = () => {
        const telefonos = [];

        // Teléfono principal (string)
        if (cliente.telefono) {
            telefonos.push({
                numero: cliente.telefono,
                etiqueta: "Principal",
            });
        }

        // Teléfonos adicionales
        if (cliente.telefonos && Array.isArray(cliente.telefonos)) {
            cliente.telefonos.forEach((t) => {
                if (typeof t === "string") {
                    telefonos.push({
                        numero: t,
                        etiqueta: "",
                    });
                } else if (t?.numero) {
                    telefonos.push(t);
                }
            });
        }

        return telefonos;
    };

    const getTodosLosEmails = () => {
        const emails = [];
        if (cliente.email) emails.push(cliente.email);
        if (cliente.emails && Array.isArray(cliente.emails)) {
            emails.push(...cliente.emails);
        }
        return emails.filter(Boolean);
    };

    const telefonos = getTodosLosTelefonos();
    const emails = getTodosLosEmails();

    return (
        <tr className="group hover:bg-slate-50 transition-colors duration-150">
            {/* Nombre */}
            <td className="px-6 py-4 align-top">
                <div className="font-semibold text-slate-800 text-sm sm:text-base leading-tight">
                    {cliente.nombre}
                </div>

                {/* Info secundaria visible en móvil */}
                <div className="mt-1 space-y-1 lg:hidden text-xs text-slate-500">
                    {cliente.cif && <div>CIF: {cliente.cif}</div>}

                    {telefonos.length > 0 && (
                        <div>Tel: {telefonos[0].numero}</div>
                    )}

                    {cliente.direccion && (
                        <div className="line-clamp-1">{cliente.direccion}</div>
                    )}
                </div>
            </td>

            {/* CIF */}
            <td className="hidden sm:table-cell px-6 py-4 text-slate-600 align-top">
                {cliente.cif || "—"}
            </td>

            {/* Teléfonos */}
            <td className="hidden md:table-cell px-6 py-4 align-top">
                <div className="space-y-1">
                    {telefonos.length > 0 ? (
                        <>
                            <div className="font-medium text-slate-800">
                                {telefonos[0].numero}
                            </div>

                            {telefonos.slice(1).map((tel, index) => (
                                <div
                                    key={index}
                                    className="text-xs text-slate-500"
                                >
                                    {tel.numero}
                                </div>
                            ))}
                        </>
                    ) : (
                        <span className="text-slate-400">—</span>
                    )}
                </div>
            </td>

            {/* Emails */}
            <td className="hidden lg:table-cell px-6 py-4 align-top">
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

            {/* Dirección */}
            <td className="hidden xl:table-cell px-6 py-4 text-slate-600 align-top">
                <div className="truncate max-w-xs">
                    {cliente.direccion || "—"}
                </div>
            </td>

            {/* Activo */}
            <td className="px-6 py-4 text-center align-top">
                <span
                    className={`
                inline-flex items-center justify-center px-3 py-1 text-xs font-semibold rounded-full
                ${
                    cliente.activo
                        ? "bg-emerald-100 text-emerald-700"
                        : "bg-rose-100 text-rose-600"
                }
            `}
                >
                    {cliente.activo ? "Activo" : "Inactivo"}
                </span>
            </td>

            {/* Acciones */}
            <td className="px-6 py-4 align-top">
                <div className="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition">
                    <button
                        onClick={() => onEditar(cliente)}
                        className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all"
                    >
                        <i className="mgc_edit_2_line text-lg"></i>
                    </button>

                    <button
                        onClick={() => onEliminar(cliente)}
                        className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all"
                    >
                        <i className="mgc_delete_line text-lg"></i>
                    </button>
                </div>
            </td>
        </tr>
    );
}
