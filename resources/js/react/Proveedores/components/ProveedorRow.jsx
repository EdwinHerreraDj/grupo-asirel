import React from "react";

const TIPO_META = {
    material: {
        label: "Material",
        classes: "border-blue-200 bg-blue-50 text-blue-700",
    },
    mano_obra: {
        label: "Mano de obra",
        classes: "border-emerald-200 bg-emerald-50 text-emerald-700",
    },
    servicio: {
        label: "Servicio",
        classes: "border-amber-200 bg-amber-50 text-amber-700",
    },
    mixto: {
        label: "Mixto",
        classes: "border-purple-200 bg-purple-50 text-purple-700",
    },
};

function recolectarTelefonos(proveedor) {
    const telefonos = [];
    if (proveedor.telefono) {
        telefonos.push({ numero: proveedor.telefono, etiqueta: "Principal" });
    }
    if (Array.isArray(proveedor.telefonos)) {
        proveedor.telefonos.forEach((tel) => {
            if (typeof tel === "string" && tel) {
                telefonos.push({ numero: tel, etiqueta: "" });
            } else if (tel && tel.numero) {
                telefonos.push(tel);
            }
        });
    }
    return telefonos.filter((t) => t.numero);
}

function recolectarEmails(proveedor) {
    const emails = [];
    if (proveedor.email) emails.push(proveedor.email);
    if (Array.isArray(proveedor.emails)) emails.push(...proveedor.emails);
    return emails.filter(Boolean);
}

export function iniciales(nombre) {
    if (!nombre) return "?";
    const partes = nombre.trim().split(/\s+/);
    if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
    return (partes[0][0] + partes[1][0]).toUpperCase();
}

export default function ProveedorRow({ proveedor, onEditar, onEliminar }) {
    const telefonos = recolectarTelefonos(proveedor);
    const emails = recolectarEmails(proveedor);
    const tipo = TIPO_META[proveedor.tipo];

    return (
        <tr className="hover:bg-slate-50/70 transition-colors">
            {/* Nombre + CIF */}
            <td className="px-4 py-3 align-top">
                <div className="flex items-start gap-3 min-w-0">
                    <span className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 text-xs font-semibold">
                        {iniciales(proveedor.nombre)}
                    </span>
                    <div className="min-w-0">
                        <p className="font-semibold text-slate-900 truncate">
                            {proveedor.nombre}
                        </p>
                        <p className="text-xs text-slate-500 font-mono">
                            {proveedor.cif || "—"}
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

            {/* Tipo */}
            <td className="px-4 py-3 align-top hidden xl:table-cell">
                {tipo ? (
                    <span
                        className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium ${tipo.classes}`}
                    >
                        {tipo.label}
                    </span>
                ) : (
                    <span className="text-slate-400 text-xs">—</span>
                )}
            </td>

            {/* Estado */}
            <td className="px-4 py-3 text-center align-top">
                {proveedor.activo ? (
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Activo
                    </span>
                ) : (
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Inactivo
                    </span>
                )}
            </td>

            {/* Acciones */}
            <td className="px-4 py-3 align-top">
                <div className="flex justify-end gap-1.5">
                    <button
                        onClick={() => onEditar(proveedor)}
                        title="Editar"
                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50 transition"
                    >
                        <i className="mgc_edit_2_line"></i>
                    </button>
                    <button
                        onClick={() => onEliminar(proveedor)}
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

export function ProveedorCard({ proveedor, onEditar, onEliminar }) {
    const telefonos = recolectarTelefonos(proveedor);
    const emails = recolectarEmails(proveedor);
    const tipo = TIPO_META[proveedor.tipo];

    return (
        <div className="p-4 space-y-3">
            <div className="flex items-start justify-between gap-3">
                <div className="flex items-start gap-3 min-w-0">
                    <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 text-xs font-semibold">
                        {iniciales(proveedor.nombre)}
                    </span>
                    <div className="min-w-0">
                        <p className="font-semibold text-slate-900 truncate">
                            {proveedor.nombre}
                        </p>
                        <p className="text-xs text-slate-500 font-mono">
                            {proveedor.cif || "—"}
                        </p>
                    </div>
                </div>
                {proveedor.activo ? (
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 shrink-0">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Activo
                    </span>
                ) : (
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600 shrink-0">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Inactivo
                    </span>
                )}
            </div>

            {tipo && (
                <div>
                    <span
                        className={`inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium ${tipo.classes}`}
                    >
                        {tipo.label}
                    </span>
                </div>
            )}

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

            <div className="flex gap-2 pt-2 border-t border-slate-100">
                <button
                    onClick={() => onEditar(proveedor)}
                    className="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50"
                >
                    <i className="mgc_edit_2_line"></i> Editar
                </button>
                <button
                    onClick={() => onEliminar(proveedor)}
                    className="inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-100"
                >
                    <i className="mgc_delete_line"></i>
                </button>
            </div>
        </div>
    );
}
