import React, { useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Aviso, Campo, Modal, SeccionFormulario, claseInput } from "./Comunes";
import { botonPrimario, botonSecundario, hoyISO, mensajeDeError } from "../utils";

const MAX_MB = 50;

/**
 * Sube uno o varios archivos al apartado (subcarpeta del Drive) usando la
 * misma API que el Drive (POST /api/files).
 */
export default function SubirDocumento({ apartado, empleado, onCerrar, onSubido }) {
    const { showSuccess, showError } = useNotification();
    const [archivos, setArchivos] = useState([]);
    const [conCaducidad, setConCaducidad] = useState(apartado.requiere_caducidad);
    const [caducidad, setCaducidad] = useState("");
    const [error, setError] = useState("");
    const [subiendo, setSubiendo] = useState(null); // {actual, total}

    const elegir = (e) => {
        const lista = Array.from(e.target.files || []);
        const grandes = lista.filter((f) => f.size > MAX_MB * 1024 * 1024);
        setError(grandes.length ? `Supera ${MAX_MB} MB: ${grandes.map((f) => f.name).join(", ")}` : "");
        setArchivos(lista.filter((f) => f.size <= MAX_MB * 1024 * 1024));
    };

    const subir = async (e) => {
        e.preventDefault();
        if (!archivos.length) return setError("Elige al menos un archivo.");
        if (conCaducidad && !caducidad) return setError("Indica la fecha de caducidad.");
        setError("");

        let subidos = 0;
        for (const [i, archivo] of archivos.entries()) {
            setSubiendo({ actual: i + 1, total: archivos.length });
            const form = new FormData();
            form.append("file", archivo);
            form.append("folder_id", apartado.folder_id);
            form.append("tiene_caducidad", conCaducidad ? "1" : "0");
            if (conCaducidad) form.append("fecha_caducidad", caducidad);

            try {
                await api.post("/files", form, { headers: { "Content-Type": "multipart/form-data" } });
                subidos++;
            } catch (err) {
                const msg = err.response?.data?.errors
                    ? Object.values(err.response.data.errors).flat()[0]
                    : mensajeDeError(err, "Error al subir el archivo.");
                setError(`${archivo.name}: ${msg}`);
                break;
            }
        }
        setSubiendo(null);

        if (subidos) {
            showSuccess(subidos === 1 ? "Documento subido" : `${subidos} documentos subidos`);
            if (subidos === archivos.length) onSubido();
            else setArchivos(archivos.slice(subidos));
        } else {
            showError("No se pudo subir el documento.");
        }
    };

    return (
        <Modal
            etiqueta="Subir documento"
            titulo={apartado.tipo}
            subtitulo={empleado.nombre_completo}
            onCerrar={subiendo ? () => {} : onCerrar}
            ancho="max-w-lg"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={!!subiendo}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-subir" className={botonPrimario} disabled={!!subiendo || !archivos.length}>
                        {subiendo ? (
                            <>
                                <i className="mgc_loading_line animate-spin"></i>
                                Subiendo {subiendo.total > 1 ? `${subiendo.actual}/${subiendo.total}` : "…"}
                            </>
                        ) : (
                            <>
                                <i className="mgc_upload_2_line"></i> Subir
                            </>
                        )}
                    </button>
                </>
            }
        >
            <form id="form-subir" onSubmit={subir} className="space-y-4">
                <SeccionFormulario titulo="Archivos" descripcion={`PDF, imágenes u otros documentos · máx. ${MAX_MB} MB cada uno`} icono="mgc_upload_2_line" color="cyan">
                    <label className="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 px-4 py-7 text-center transition hover:border-cyan-400 hover:bg-cyan-50/40">
                        <i className="mgc_upload_2_line text-3xl text-cyan-600"></i>
                        <span className="text-sm font-semibold text-slate-700">
                            {archivos.length ? "Cambiar archivos" : "Pulsa para elegir archivos"}
                        </span>
                        <span className="text-xs text-slate-500">Puedes elegir varios a la vez</span>
                        <input type="file" multiple onChange={elegir} className="sr-only" disabled={!!subiendo} />
                    </label>

                    {archivos.length > 0 && (
                        <ul className="mt-3 space-y-1.5">
                            {archivos.map((f, i) => (
                                <li key={f.name + f.size} className="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                    <i className="mgc_file_line text-slate-400"></i>
                                    <span className="min-w-0 flex-1 break-all">{f.name}</span>
                                    <span className="shrink-0 text-xs text-slate-400">{(f.size / 1024 / 1024).toFixed(1)} MB</span>
                                    {!subiendo && (
                                        <button
                                            type="button"
                                            onClick={() => setArchivos(archivos.filter((_, j) => j !== i))}
                                            aria-label={`Quitar ${f.name}`}
                                            className="text-slate-400 hover:text-rose-600"
                                        >
                                            <i className="mgc_close_line"></i>
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    )}
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Caducidad"
                    descripcion={apartado.requiere_caducidad ? "Este documento caduca: indica hasta cuándo es válido" : "Opcional"}
                    icono="mgc_time_line"
                    color="amber"
                >
                    <label className="flex items-center gap-2 text-sm text-slate-700">
                        <input
                            type="checkbox"
                            checked={conCaducidad}
                            onChange={(e) => setConCaducidad(e.target.checked)}
                            className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                        />
                        Tiene fecha de caducidad
                    </label>

                    {conCaducidad && (
                        <Campo etiqueta="Válido hasta" obligatorio className="mt-3 sm:max-w-xs">
                            <input type="date" min={hoyISO()} value={caducidad} onChange={(e) => setCaducidad(e.target.value)} className={claseInput(false)} />
                        </Campo>
                    )}
                </SeccionFormulario>

                {error && <Aviso tipo="peligro">{error}</Aviso>}
            </form>
        </Modal>
    );
}
