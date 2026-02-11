// resources/js/react/Clientes/components/ClienteRow.jsx
import React from "react";

export default function ClienteRow({ cliente, onEditar, onEliminar }) {
    const getTodosLosTelefonos = () => {
        const telefonos = [];
        if (cliente.telefono) telefonos.push(cliente.telefono);
        if (cliente.telefonos && Array.isArray(cliente.telefonos)) {
            telefonos.push(...cliente.telefonos);
        }
        return telefonos.filter(Boolean);
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
        <tr className="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
            {/* Nombre */}
            <td className="px-4 py-3">
                <div className="font-medium text-gray-900 dark:text-white">
                    {cliente.nombre}
                </div>
                {cliente.descripcion && (
                    <div className="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 lg:hidden">
                        {cliente.descripcion}
                    </div>
                )}
            </td>

            {/* CIF */}
            <td className="px-4 py-3 text-gray-700 dark:text-gray-300">
                {cliente.cif || "—"}
            </td>

            {/* Teléfonos */}
            <td className="px-4 py-3">
                <div className="space-y-1">
                    {telefonos.length > 0 ? (
                        <>
                            <div className="font-medium text-gray-900 dark:text-white">
                                {telefonos[0]}
                            </div>
                            {telefonos.slice(1).map((tel, index) => (
                                <div
                                    key={index}
                                    className="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {tel}
                                </div>
                            ))}
                        </>
                    ) : (
                        <span className="text-gray-400">—</span>
                    )}
                </div>
            </td>

            {/* Emails */}
            <td className="px-4 py-3">
                <div className="space-y-1 max-w-xs">
                    {emails.length > 0 ? (
                        <>
                            <div className="font-medium text-gray-900 dark:text-white truncate">
                                {emails[0]}
                            </div>
                            {emails.slice(1).map((email, index) => (
                                <div
                                    key={index}
                                    className="text-xs text-gray-500 dark:text-gray-400 truncate"
                                >
                                    {email}
                                </div>
                            ))}
                        </>
                    ) : (
                        <span className="text-gray-400">—</span>
                    )}
                </div>
            </td>

            {/* Dirección - oculta en móvil */}
            <td className="px-4 py-3 text-gray-700 dark:text-gray-300 hidden lg:table-cell max-w-xs">
                <div className="truncate">{cliente.direccion || "—"}</div>
            </td>

            {/* Activo */}
            <td className="px-4 py-3 text-center">
                <span
                    className={`
                    px-3 py-1 text-xs font-semibold rounded-full
                    ${
                        cliente.activo
                            ? "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"
                            : "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400"
                    }
                `}
                >
                    {cliente.activo ? "Sí" : "No"}
                </span>
            </td>

            {/* Acciones */}
            <td className="px-4 py-3">
                <div className="flex justify-end gap-2">
                    <button
                        onClick={() => onEditar(cliente)}
                        className="p-2 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 rounded-lg transition"
                        title="Editar"
                    >
                        <i className="mgc_edit_2_line text-lg"></i>
                    </button>

                    <button
                        onClick={() => onEliminar(cliente)}
                        className="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
                        title="Eliminar"
                    >
                        <i className="mgc_delete_line text-lg"></i>
                    </button>
                </div>
            </td>
        </tr>
    );
}
