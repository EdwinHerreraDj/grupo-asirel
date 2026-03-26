import React, { useState, useEffect } from "react";
import api from "../../shared/api";
import { formatEuro } from "../utils/formato";

export default function ModalInforme({
    obraId,
    numeroCertificacion,
    onCancelar,
}) {
    const [capitulos, setCapitulos] = useState([]);
    const [seleccionados, setSeleccionados] = useState([]);
    const [loading, setLoading] = useState(true);
    const [descargando, setDescargando] = useState(false);

    useEffect(() => {
        api.get(
            `/obras/${obraId}/certificaciones/${numeroCertificacion}/capitulos`,
        )
            .then(({ data }) => {
                setCapitulos(data.capitulos ?? []);
                setSeleccionados((data.capitulos ?? []).map((c) => c.id));
            })
            .finally(() => setLoading(false));
    }, []);

    const total = capitulos
        .filter((c) => seleccionados.includes(c.id))
        .reduce((acc, c) => acc + c.total, 0);

    const toggleCapitulo = (id) => {
        setSeleccionados((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id],
        );
    };

    const handleDescargar = async () => {
        if (seleccionados.length === 0) return;
        setDescargando(true);
        try {
            const response = await api.post(
                "/certificaciones/informe-pdf",
                { certificacion_ids: seleccionados },
                { responseType: "blob" },
            );
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute(
                "download",
                `informe_certificacion_${numeroCertificacion}.pdf`,
            );
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);
            onCancelar();
        } catch (err) {
            console.error("Error al generar informe", err);
        } finally {
            setDescargando(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
            <div className="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.28)]">
                <div className="border-b border-slate-200/70 bg-gradient-to-r from-slate-50 via-white to-violet-50/50 px-6 py-5">
                    <div className="flex items-start gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-[0_10px_24px_rgba(99,102,241,0.25)]">
                            <i className="mgc_file_line text-lg"></i>
                        </div>
                        <div className="min-w-0">
                            <h3 className="text-lg font-semibold tracking-tight text-slate-900">
                                Generar informe
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                Selecciona los capítulos que quieres incluir en
                                el informe.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="px-6 py-5">
                    <div className="mb-5 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <p className="text-sm leading-6 text-slate-600">
                            Certificación{" "}
                            <span className="font-semibold text-slate-900">
                                {numeroCertificacion}
                            </span>{" "}
                            — Selecciona los capítulos a incluir.
                        </p>
                    </div>

                    {loading ? (
                        <div className="py-10">
                            <div className="flex flex-col items-center justify-center gap-3 text-center">
                                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <i className="mgc_loading_line animate-spin text-xl"></i>
                                </div>
                                <div>
                                    <p className="font-medium text-slate-700">
                                        Cargando capítulos
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        Estamos preparando la información del
                                        informe.
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="mb-6">
                            <div className="space-y-2">
                                {capitulos.map((cap) => (
                                    <label
                                        key={cap.id}
                                        className="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-violet-200 hover:bg-violet-50/40"
                                    >
                                        <div className="flex min-w-0 items-center gap-3">
                                            <input
                                                type="checkbox"
                                                checked={seleccionados.includes(
                                                    cap.id,
                                                )}
                                                onChange={() =>
                                                    toggleCapitulo(cap.id)
                                                }
                                                className="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500/30"
                                            />
                                            <span className="truncate text-sm font-medium text-slate-700">
                                                {cap.oficio}
                                            </span>
                                        </div>

                                        <span className="ml-3 shrink-0 text-sm font-semibold text-slate-900">
                                            {formatEuro(cap.total)}
                                        </span>
                                    </label>
                                ))}
                            </div>

                            <div className="mt-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex flex-wrap items-center gap-4">
                                    <button
                                        onClick={() =>
                                            setSeleccionados(
                                                capitulos.map((c) => c.id),
                                            )
                                        }
                                        className="text-xs font-semibold uppercase tracking-[0.08em] text-violet-700 transition hover:text-violet-800"
                                    >
                                        Seleccionar todos
                                    </button>

                                    <button
                                        onClick={() => setSeleccionados([])}
                                        className="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500 transition hover:text-slate-700"
                                    >
                                        Limpiar
                                    </button>
                                </div>

                                <div className="text-right">
                                    <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Total seleccionado
                                    </p>
                                    <p className="mt-1 text-lg font-semibold text-violet-700">
                                        {formatEuro(total)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button
                            onClick={onCancelar}
                            disabled={descargando}
                            className="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                        >
                            Cancelar
                        </button>

                        <button
                            onClick={handleDescargar}
                            disabled={descargando || seleccionados.length === 0}
                            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(99,102,241,0.22)] transition hover:from-violet-500 hover:to-indigo-500 disabled:opacity-50"
                        >
                            <i className="mgc_download_line"></i>
                            {descargando ? "Generando..." : "Descargar PDF"}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
