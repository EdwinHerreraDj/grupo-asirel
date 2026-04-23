import React from "react";

function recolectarTelefonos(cliente) {
    const telefonos = [];
    if (cliente.telefono) {
        telefonos.push({ numero: cliente.telefono, etiqueta: "Principal" });
    }
    if (Array.isArray(cliente.telefonos)) {
        cliente.telefonos.forEach((tel) => {
            if (typeof tel === "string" && tel) {
                telefonos.push({ numero: tel, etiqueta: "" });
            } else if (tel && tel.numero) {
                telefonos.push(tel);
            }
        });
    }
    return telefonos.filter((t) => t.numero);
}

function recolectarEmails(cliente) {
    const emails = [];
    if (cliente.email) emails.push(cliente.email);
    if (Array.isArray(cliente.emails)) emails.push(...cliente.emails);
    return emails.filter(Boolean);
}

export function direccionCompleta(c) {
    if (!c) return null;
    const linea1 = (c.direccion || "").trim();
    const cpPob = [c.codigo_postal, c.poblacion].filter(Boolean).join(" ");
    const provPais = [c.provincia, c.pais].filter(Boolean).join(", ");
    const partes = [linea1, cpPob, provPais].filter(Boolean);
    return partes.length ? partes.join(", ") : null;
}

export function iniciales(nombre) {
    if (!nombre) return "?";
    const partes = nombre.trim().split(/\s+/);
    if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
    return (partes[0][0] + partes[1][0]).toUpperCase();
}

function EstadoBadge({ activo }) {
    if (activo) {
        return (
            <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Activo
            </span>
        );
    }
    return (
        <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
            <span className="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
            Inactivo
        </span>
    );
}

export default function ClienteRow({ cliente, onEditar, onEliminar }) {
    const telefonos = recolectarTelefonos(cliente);
    const emails = recolectarEmails(cliente);

    return (
        <tr className="hover:bg-slate-50/70 transition-colors">
            {/* Nombre + CIF */}
            <td className="px-4 py-3 align-top">
                <div className="flex items-start gap-3 min-w-0">
                    <span className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 text-xs font-semibold">
                        {iniciales(cliente.nombre)}
                    </span>
                    <div className="min-w-0">
                        <p className="font-semibold text-slate-900 truncate">
                            {cliente.nombre}
                        </p>
                        <p className="text-xs text-slate-500 font-mono">
                            {cliente.cif || "—"}
                        </p>
                    </div>
                </div>
            </td>

            {/* Contacto */}
            <td className="px-4 py-3 align-top">
                {telefonos.length === 0 && emails.length === 0 ? (
                    <span className="text-slate-400">—</span>
                ) : (
                    <div className="space-y-1 text-xs">
                        {telefonos.slice(0, 2).map((tel, idx) => (
                            <div
                                key={`tel-${idx}`}
                                className="flex items-center gap-1.5 text-slate-700"
                            >
                                <i className="mgc_phone_line text-slate-400"></i>
                                <span className="font-medium">
                                    {tel.numero}
                                </span>
                                {tel.etiqueta && (
                                    <span className="text-[10px] text-slate-500">
                                        · {tel.etiqueta}
                                    </span>
                                )}
                            </div>
                        ))}
                        {telefonos.length > 2 && (
                            <div className="text-[10px] text-slate-400">
                                +{telefonos.length - 2} tel. más
                            </div>
                        )}
                        {emails.slice(0, 2).map((em, idx) => (
                            <div
                                key={`em-${idx}`}
                                className="flex items-center gap-1.5 text-slate-600 truncate"
                            >
                                <i className="mgc_mail_line text-slate-400"></i>
                                <span className="truncate">{em}</span>
                            </div>
                        ))}
                        {emails.length > 2 && (
                            <div className="text-[10px] text-slate-400">
                                +{emails.length - 2} email más
                            </div>
                        )}
                    </div>
                )}
            </td>

            {/* Dirección */}
            <td className="px-4 py-3 align-top hidden xl:table-cell">
                {direccionCompleta(cliente) ? (
                    <p className="text-xs text-slate-600 line-clamp-2 max-w-xs">
                        {direccionCompleta(cliente)}
                    </p>
                ) : (
                    <span className="text-slate-400 text-xs">—</span>
                )}
            </td>

            {/* Estado */}
            <td className="px-4 py-3 text-center align-top">
                <EstadoBadge activo={cliente.activo} />
            </td>

            {/* Acciones */}
            <td className="px-4 py-3 align-top">
                <div className="flex justify-end gap-1.5">
                    <button
                        onClick={() => onEditar(cliente)}
                        title="Editar"
                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50 transition"
                    >
                        <i className="mgc_edit_2_line"></i>
                    </button>
                    <button
                        onClick={() => onEliminar(cliente)}
                        title="Eliminar"
                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50 transition"
                    >
                        <i className="mgc_delete_line"></i>
                    </button>
                </div>
            </td>
        </tr>
    );
}

export function ClienteCard({ cliente, onEditar, onEliminar }) {
    const telefonos = recolectarTelefonos(cliente);
    const emails = recolectarEmails(cliente);

    return (
        <div className="p-4 space-y-3">
            <div className="flex items-start justify-between gap-3">
                <div className="flex items-start gap-3 min-w-0">
                    <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 text-xs font-semibold">
                        {iniciales(cliente.nombre)}
                    </span>
                    <div className="min-w-0">
                        <p className="font-semibold text-slate-900 truncate">
                            {cliente.nombre}
                        </p>
                        <p className="text-xs text-slate-500 font-mono">
                            {cliente.cif || "—"}
                        </p>
                    </div>
                </div>
                <div className="shrink-0">
                    <EstadoBadge activo={cliente.activo} />
                </div>
            </div>

            {(telefonos.length > 0 || emails.length > 0) && (
                <div className="space-y-1 text-xs">
                    {telefonos.slice(0, 2).map((tel, idx) => (
                        <div
                            key={`tel-${idx}`}
                            className="flex items-center gap-1.5 text-slate-700"
                        >
                            <i className="mgc_phone_line text-slate-400"></i>
                            <span className="font-medium">{tel.numero}</span>
                            {tel.etiqueta && (
                                <span className="text-[10px] text-slate-500">
                                    · {tel.etiqueta}
                                </span>
                            )}
                        </div>
                    ))}
                    {emails.slice(0, 2).map((em, idx) => (
                        <div
                            key={`em-${idx}`}
                            className="flex items-center gap-1.5 text-slate-600 truncate"
                        >
                            <i className="mgc_mail_line text-slate-400"></i>
                            <span className="truncate">{em}</span>
                        </div>
                    ))}
                </div>
            )}

            {direccionCompleta(cliente) && (
                <p className="text-xs text-slate-500 flex items-start gap-1.5">
                    <i className="mgc_location_line text-slate-400 mt-0.5"></i>
                    <span className="line-clamp-2">{direccionCompleta(cliente)}</span>
                </p>
            )}

            <div className="flex gap-2 pt-2 border-t border-slate-100">
                <button
                    onClick={() => onEditar(cliente)}
                    className="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50"
                >
                    <i className="mgc_edit_2_line"></i> Editar
                </button>
                <button
                    onClick={() => onEliminar(cliente)}
                    className="inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-100"
                >
                    <i className="mgc_delete_line"></i>
                </button>
            </div>
        </div>
    );
}
