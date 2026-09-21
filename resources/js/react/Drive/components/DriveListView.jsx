// resources/js/react/Drive/components/DriveListView.jsx
import React, { useMemo, useState } from "react";
import { useClipboard } from "../context/ClipboardContext";
import { useNotification } from "../context/NotificationContext";
import useDescargarCarpeta from "../hooks/useDescargarCarpeta";
import DownloadModal from "./DownloadModal";
import {
    esPrevisualizable,
    esZip,
    estadoCaducidad,
    formatoFecha,
    formatoFechaHora,
    formatoTamano,
    iconoArchivo,
    tipoArchivo,
} from "../utils/archivos";

/**
 * Vista de lista del Drive: carpetas y archivos en una tabla con toda la
 * información (tipo, tamaño, fecha de subida, autor y caducidad), ordenable
 * por columnas. Alterna con la vista de cuadrícula.
 */

const CAMPOS_CARPETA = ["nombre", "subido", "tamano"];

const compararTexto = (a, b) =>
    a.localeCompare(b, "es", { sensitivity: "base", numeric: true });

const marcaTiempo = (valor) => {
    if (!valor) return null;
    const t = new Date(valor).getTime();
    return Number.isNaN(t) ? null : t;
};

const valorArchivo = (file, campo) => {
    switch (campo) {
        case "nombre":
            return file.nombre ?? "";
        case "tipo":
            return tipoArchivo(file.nombre);
        case "tamano":
            return Number(file.tamaño) || 0;
        case "subido":
            return marcaTiempo(file.created_at);
        case "autor":
            return file.usuario?.name ?? null;
        case "caducidad":
            return file.fecha_caducidad
                ? marcaTiempo(String(file.fecha_caducidad).slice(0, 10))
                : null;
        default:
            return null;
    }
};

const elementosDe = (folder) =>
    folder.files_count === undefined && folder.children_count === undefined
        ? null
        : (folder.files_count ?? 0) + (folder.children_count ?? 0);

const valorCarpeta = (folder, campo) => {
    switch (campo) {
        case "subido":
            return marcaTiempo(folder.created_at);
        case "tamano":
            return elementosDe(folder);
        default:
            return folder.nombre ?? "";
    }
};

/** Ordena dejando siempre al final los valores vacíos. */
const ordenar = (lista, campo, dir, obtener) =>
    [...lista].sort((a, b) => {
        const va = obtener(a, campo);
        const vb = obtener(b, campo);

        if (va === null && vb === null) return 0;
        if (va === null) return 1;
        if (vb === null) return -1;

        const r = typeof va === "string" ? compararTexto(va, vb) : va - vb;
        return dir === "asc" ? r : -r;
    });

function Cabecera({ campo, etiqueta, orden, onOrdenar, className = "", derecha = false }) {
    const activo = orden.campo === campo;

    return (
        <th
            scope="col"
            className={`px-3 py-3 ${derecha ? "text-right" : "text-left"} ${className}`}
            aria-sort={activo ? (orden.dir === "asc" ? "ascending" : "descending") : "none"}
        >
            <button
                type="button"
                onClick={() => onOrdenar(campo)}
                className={`inline-flex items-center gap-1 uppercase tracking-wide transition hover:text-slate-800 ${
                    activo ? "text-slate-800" : ""
                }`}
            >
                {etiqueta}
                <i
                    className={`text-sm ${
                        activo
                            ? orden.dir === "asc"
                                ? "mgc_up_line"
                                : "mgc_down_line"
                            : "mgc_down_line opacity-0"
                    }`}
                ></i>
            </button>
        </th>
    );
}

function BotonAccion({ icono, titulo, onClick, peligro = false }) {
    return (
        <button
            type="button"
            title={titulo}
            aria-label={titulo}
            onClick={(e) => {
                e.stopPropagation();
                onClick();
            }}
            className={`inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition ${
                peligro
                    ? "text-rose-500 hover:bg-rose-50 hover:text-rose-700"
                    : "text-slate-500 hover:bg-slate-100 hover:text-slate-800"
            }`}
        >
            <i className={`${icono} text-lg`}></i>
        </button>
    );
}

function InputNombre({ valor, onCambiar, onGuardar, onCancelar }) {
    return (
        <input
            type="text"
            autoFocus
            value={valor}
            onChange={(e) => onCambiar(e.target.value)}
            onBlur={onGuardar}
            onKeyDown={(e) => {
                if (e.key === "Enter") onGuardar();
                if (e.key === "Escape") onCancelar();
            }}
            onClick={(e) => e.stopPropagation()}
            className="w-full rounded-lg border border-indigo-500 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
    );
}

export default function DriveListView({
    folders,
    files,
    onFolderClick,
    onDeleteFolder,
    onRenameFolder,
    onDeleteFile,
    onDownloadFile,
    onRenameFile,
    onExtractFile,
    onPreviewFile,
    selectedFiles,
    onSelectFile,
    mostrarUbicacion = false,
}) {
    const [orden, setOrden] = useState({ campo: "subido", dir: "desc" });
    const [renombrando, setRenombrando] = useState(null);

    const { cutSingleFolder } = useClipboard();
    const { showSuccess } = useNotification();
    const { descargarCarpeta, carpetaDescargando } = useDescargarCarpeta();

    const carpetasOrdenadas = useMemo(() => {
        const campo = CAMPOS_CARPETA.includes(orden.campo) ? orden.campo : "nombre";
        const dir = CAMPOS_CARPETA.includes(orden.campo) ? orden.dir : "asc";
        return ordenar(folders, campo, dir, valorCarpeta);
    }, [folders, orden]);

    const archivosOrdenados = useMemo(
        () => ordenar(files, orden.campo, orden.dir, valorArchivo),
        [files, orden],
    );

    const cambiarOrden = (campo) =>
        setOrden((prev) =>
            prev.campo === campo
                ? { campo, dir: prev.dir === "asc" ? "desc" : "asc" }
                : { campo, dir: ["nombre", "tipo", "autor", "caducidad"].includes(campo) ? "asc" : "desc" },
        );

    const empezarRenombrar = (tipo, item) =>
        setRenombrando({ tipo, id: item.id, nombre: item.nombre, original: item.nombre });

    const guardarNombre = async () => {
        if (!renombrando) return;

        const nombre = renombrando.nombre.trim();
        if (!nombre || nombre === renombrando.original) {
            setRenombrando(null);
            return;
        }

        const ok =
            renombrando.tipo === "carpeta"
                ? await onRenameFolder(renombrando.id, nombre)
                : await onRenameFile(renombrando.id, nombre);

        if (ok) setRenombrando(null);
    };

    const cortarCarpeta = (folder) => {
        const resultado = cutSingleFolder(folder);
        if (resultado.success) showSuccess(resultado.message);
    };

    const editando = (tipo, id) => renombrando?.tipo === tipo && renombrando.id === id;

    const cabecera = { orden, onOrdenar: cambiarOrden };

    return (
        <>
            <div className="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 text-xs font-semibold text-slate-500">
                        <tr className="border-b border-slate-200">
                            <th scope="col" className="w-10 px-3 py-3">
                                <span className="sr-only">Seleccionar</span>
                            </th>
                            <Cabecera campo="nombre" etiqueta="Nombre" {...cabecera} />
                            <Cabecera campo="tipo" etiqueta="Tipo" className="hidden lg:table-cell" {...cabecera} />
                            <Cabecera campo="tamano" etiqueta="Tamaño" className="hidden sm:table-cell" derecha {...cabecera} />
                            <Cabecera campo="subido" etiqueta="Subido" className="hidden md:table-cell" {...cabecera} />
                            <Cabecera campo="autor" etiqueta="Subido por" className="hidden xl:table-cell" {...cabecera} />
                            <Cabecera campo="caducidad" etiqueta="Caducidad" {...cabecera} />
                            <th scope="col" className="px-3 py-3 text-right">
                                <span className="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100">
                        {carpetasOrdenadas.map((folder) => {
                            const elementos = elementosDe(folder);
                            const textoElementos =
                                elementos === null
                                    ? "—"
                                    : `${elementos} elemento${elementos === 1 ? "" : "s"}`;

                            return (
                                <tr
                                    key={`carpeta-${folder.id}`}
                                    onClick={() => !editando("carpeta", folder.id) && onFolderClick(folder.id)}
                                    className="cursor-pointer transition hover:bg-slate-50"
                                >
                                    <td className="px-3 py-3"></td>
                                    <td className="px-3 py-3">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                                                <i className="mgc_folder_fill text-xl text-amber-500"></i>
                                            </div>
                                            <div className="min-w-0 max-w-[14rem] sm:max-w-xs lg:max-w-md">
                                                {editando("carpeta", folder.id) ? (
                                                    <InputNombre
                                                        valor={renombrando.nombre}
                                                        onCambiar={(nombre) => setRenombrando((r) => ({ ...r, nombre }))}
                                                        onGuardar={guardarNombre}
                                                        onCancelar={() => setRenombrando(null)}
                                                    />
                                                ) : (
                                                    <p className="truncate font-semibold text-slate-800" title={folder.nombre}>
                                                        {folder.nombre}
                                                    </p>
                                                )}
                                                {mostrarUbicacion && folder.path && (
                                                    <p className="truncate text-xs text-slate-400" title={folder.path}>
                                                        {folder.path}
                                                    </p>
                                                )}
                                                <p className="text-xs text-slate-500 sm:hidden">{textoElementos}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="hidden px-3 py-3 text-slate-600 lg:table-cell">Carpeta</td>
                                    <td className="hidden whitespace-nowrap px-3 py-3 text-right text-slate-600 sm:table-cell">
                                        {textoElementos}
                                    </td>
                                    <td className="hidden whitespace-nowrap px-3 py-3 text-slate-600 md:table-cell">
                                        {formatoFechaHora(folder.created_at)}
                                    </td>
                                    <td className="hidden px-3 py-3 text-slate-400 xl:table-cell">—</td>
                                    <td className="px-3 py-3 text-slate-400">—</td>
                                    <td className="px-3 py-3" onClick={(e) => e.stopPropagation()}>
                                        <div className="flex items-center justify-end gap-0.5">
                                            <BotonAccion icono="mgc_folder_open_line" titulo="Abrir" onClick={() => onFolderClick(folder.id)} />
                                            <BotonAccion icono="mgc_edit_line" titulo="Renombrar" onClick={() => empezarRenombrar("carpeta", folder)} />
                                            <BotonAccion icono="mgc_download_line" titulo="Descargar como ZIP" onClick={() => descargarCarpeta(folder)} />
                                            <BotonAccion icono="mgc_scissors_line" titulo="Cortar para mover" onClick={() => cortarCarpeta(folder)} />
                                            <BotonAccion icono="mgc_delete_line" titulo="Eliminar" peligro onClick={() => onDeleteFolder(folder.id)} />
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}

                        {archivosOrdenados.map((file) => {
                            const seleccionado = selectedFiles.includes(file.id);
                            const icono = iconoArchivo(file.nombre);
                            const caducidad = estadoCaducidad(file);
                            const previsualizable = esPrevisualizable(file.nombre);

                            return (
                                <tr
                                    key={`archivo-${file.id}`}
                                    className={`transition hover:bg-slate-50 ${seleccionado ? "bg-indigo-50/60" : ""}`}
                                >
                                    <td className="px-3 py-3">
                                        <input
                                            type="checkbox"
                                            checked={seleccionado}
                                            onChange={() => onSelectFile(file.id)}
                                            aria-label={`Seleccionar ${file.nombre}`}
                                            className="h-4 w-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td className="px-3 py-3">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${icono.color}`}>
                                                <i className={`${icono.icon} text-lg`}></i>
                                            </div>
                                            <div className="min-w-0 max-w-[14rem] sm:max-w-xs lg:max-w-md">
                                                {editando("archivo", file.id) ? (
                                                    <InputNombre
                                                        valor={renombrando.nombre}
                                                        onCambiar={(nombre) => setRenombrando((r) => ({ ...r, nombre }))}
                                                        onGuardar={guardarNombre}
                                                        onCancelar={() => setRenombrando(null)}
                                                    />
                                                ) : previsualizable ? (
                                                    <button
                                                        type="button"
                                                        onClick={() => onPreviewFile(file)}
                                                        className="block max-w-full truncate text-left font-semibold text-slate-800 hover:text-indigo-600"
                                                        title={`Ver ${file.nombre}`}
                                                    >
                                                        {file.nombre}
                                                    </button>
                                                ) : (
                                                    <p className="truncate font-semibold text-slate-800" title={file.nombre}>
                                                        {file.nombre}
                                                    </p>
                                                )}
                                                {mostrarUbicacion && file.folder_path && (
                                                    <p className="truncate text-xs text-slate-400" title={file.folder_path}>
                                                        {file.folder_path}
                                                    </p>
                                                )}
                                                <p className="text-xs text-slate-500 md:hidden">
                                                    {formatoTamano(file.tamaño)} · {formatoFecha(file.created_at)}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="hidden whitespace-nowrap px-3 py-3 text-slate-600 lg:table-cell">
                                        {tipoArchivo(file.nombre)}
                                    </td>
                                    <td className="hidden whitespace-nowrap px-3 py-3 text-right text-slate-600 sm:table-cell">
                                        {formatoTamano(file.tamaño)}
                                    </td>
                                    <td className="hidden whitespace-nowrap px-3 py-3 text-slate-600 md:table-cell">
                                        {formatoFechaHora(file.created_at)}
                                    </td>
                                    <td className="hidden max-w-[10rem] truncate px-3 py-3 text-slate-600 xl:table-cell">
                                        {file.usuario?.name ?? "—"}
                                    </td>
                                    <td className="whitespace-nowrap px-3 py-3">
                                        {caducidad ? (
                                            <div>
                                                <span className={`inline-flex rounded-lg border px-2 py-0.5 text-xs font-semibold ${caducidad.clases}`}>
                                                    {caducidad.texto}
                                                </span>
                                                {caducidad.estado !== "vigente" && (
                                                    <span className="mt-0.5 block text-xs text-slate-400">
                                                        {formatoFecha(file.fecha_caducidad)}
                                                    </span>
                                                )}
                                            </div>
                                        ) : (
                                            <span className="text-xs text-slate-400">Sin caducidad</span>
                                        )}
                                    </td>
                                    <td className="px-3 py-3">
                                        <div className="flex items-center justify-end gap-0.5">
                                            {previsualizable && (
                                                <BotonAccion icono="mgc_eye_2_line" titulo="Ver" onClick={() => onPreviewFile(file)} />
                                            )}
                                            <BotonAccion icono="mgc_download_line" titulo="Descargar" onClick={() => onDownloadFile(file.id)} />
                                            {esZip(file.nombre) && (
                                                <BotonAccion icono="mgc_file_zip_line" titulo="Extraer aquí" onClick={() => onExtractFile(file.id)} />
                                            )}
                                            <BotonAccion icono="mgc_edit_line" titulo="Renombrar" onClick={() => empezarRenombrar("archivo", file)} />
                                            <BotonAccion icono="mgc_delete_line" titulo="Eliminar" peligro onClick={() => onDeleteFile(file.id)} />
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            <DownloadModal isOpen={!!carpetaDescargando} folderName={carpetaDescargando?.nombre} />
        </>
    );
}
