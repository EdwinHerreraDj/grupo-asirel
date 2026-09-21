// resources/js/react/Drive/components/FileItem.jsx
import React, { useState, useRef, useEffect } from "react";
import {
    esPrevisualizable,
    esZip,
    estadoCaducidad,
    formatoTamano,
    iconoArchivo,
} from "../utils/archivos";

export default function FileItem({
    file,
    onDelete,
    onDownload,
    onRename,
    isSelected,
    onSelect,
    onExtract,
    onPreview,
    previewFile,
    onClosePreview,
}) {
    const [showMenu, setShowMenu] = useState(false);
    const [isRenaming, setIsRenaming] = useState(false);
    const [newName, setNewName] = useState(file.nombre);
    const menuRef = useRef(null);
    const inputRef = useRef(null);

    const previewable = esPrevisualizable(file.nombre);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setShowMenu(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        if (isRenaming && inputRef.current) {
            inputRef.current.focus();
            inputRef.current.select();
        }
    }, [isRenaming]);

    const handleRename = async () => {
        if (newName.trim() && newName !== file.nombre) {
            const success = await onRename(newName.trim());
            if (success) {
                setIsRenaming(false);
            }
        } else {
            setNewName(file.nombre);
            setIsRenaming(false);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === "Enter") {
            handleRename();
        } else if (e.key === "Escape") {
            setNewName(file.nombre);
            setIsRenaming(false);
        }
    };

    const handleCheckboxChange = (e) => {
        e.stopPropagation();
        onSelect();
    };

    const isZipFile = () => esZip(file.nombre);

    // Icono y caducidad: utilidades compartidas con la vista de lista.
    const iconData = iconoArchivo(file.nombre);
    const caducidad = estadoCaducidad(file);

    return (
        <div className="relative group">
            {/* Checkbox */}
            <div className="absolute top-3 left-3 z-20">
                <input
                    type="checkbox"
                    checked={isSelected}
                    onChange={handleCheckboxChange}
                    className="
                w-5 h-5
                rounded-lg
                border-slate-300
                text-indigo-600
                focus:ring-indigo-500
                cursor-pointer
                shadow-sm
            "
                />
            </div>

            {/* Badge caducidad */}
            {caducidad && (
                <div className="absolute top-3 right-3 z-20">
                    <div
                        className={`px-2.5 py-1 text-[11px] font-semibold rounded-xl border backdrop-blur-sm shadow-sm ${caducidad.clases}`}
                    >
                        {caducidad.texto}
                    </div>
                </div>
            )}

            {/* Card */}
            <div
                className={`
            relative
            border
            rounded-2xl
            p-5
            bg-white
            transition-all duration-200
            ${
                isSelected
                    ? "border-indigo-500 ring-2 ring-indigo-200"
                    : "border-slate-200 hover:border-indigo-400 hover:shadow-lg"
            }
        `}
            >
                <div className="flex items-start justify-between mb-4">
                    {/* Icon */}
                    <div className="flex-1 flex justify-center">
                        <div
                            className={`
                        w-16 h-16
                        rounded-2xl
                        flex items-center justify-center
                        shadow-inner
                        ${iconData.color}
                    `}
                        >
                            <i className={`${iconData.icon} text-3xl`}></i>
                        </div>
                    </div>

                    {/* Menu */}
                    <div className="relative" ref={menuRef}>
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                setShowMenu(!showMenu);
                            }}
                            className="
                        w-9 h-9
                        flex items-center justify-center
                        rounded-xl
                        hover:bg-slate-100
                        opacity-0
                        group-hover:opacity-100
                        transition
                    "
                        >
                            <i className="mgc_more_2_fill text-slate-500"></i>
                        </button>

                        {showMenu && (
                            <div
                                className="
                            absolute right-0 top-10
                            w-52
                            bg-white
                            rounded-2xl
                            shadow-2xl
                            border border-slate-200
                            py-2
                            z-50
                        "
                            >
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onDownload();
                                        setShowMenu(false);
                                    }}
                                    className="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center gap-2 text-sm"
                                >
                                    <i className="mgc_download_line"></i>
                                    Descargar
                                </button>

                                {previewable && (
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            if (onPreview) onPreview();
                                            setShowMenu(false);
                                        }}
                                        className="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center gap-2 text-sm"
                                    >
                                        <i className="mgc_eye_2_line"></i>
                                        Ver
                                    </button>
                                )}

                                {isZipFile() && (
                                    <>
                                        <div className="my-2 border-t border-slate-200"></div>
                                        <button
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                onExtract();
                                                setShowMenu(false);
                                            }}
                                            className="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center gap-2 text-sm"
                                        >
                                            <i className="mgc_file_zip_line"></i>
                                            Extraer aquí
                                        </button>
                                    </>
                                )}

                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        setIsRenaming(true);
                                        setShowMenu(false);
                                    }}
                                    className="w-full text-left px-4 py-2.5 hover:bg-slate-50 flex items-center gap-2 text-sm"
                                >
                                    <i className="mgc_edit_line"></i>
                                    Renombrar
                                </button>

                                <div className="my-2 border-t border-slate-200"></div>

                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onDelete();
                                        setShowMenu(false);
                                    }}
                                    className="w-full text-left px-4 py-2.5 hover:bg-rose-50 text-rose-600 flex items-center gap-2 text-sm"
                                >
                                    <i className="mgc_delete_line"></i>
                                    Eliminar
                                </button>
                            </div>
                        )}
                    </div>
                </div>

                {/* Info */}
                <div className="text-center">
                    {isRenaming ? (
                        <input
                            ref={inputRef}
                            type="text"
                            value={newName}
                            onChange={(e) => setNewName(e.target.value)}
                            onBlur={handleRename}
                            onKeyDown={handleKeyDown}
                            onClick={(e) => e.stopPropagation()}
                            className="
                        w-full px-3 py-2 text-sm
                        border border-indigo-500
                        rounded-xl
                        focus:outline-none
                        focus:ring-2 focus:ring-indigo-500
                    "
                        />
                    ) : (
                        <p
                            className="text-sm font-semibold text-slate-700 truncate"
                            title={file.nombre}
                        >
                            {file.nombre}
                        </p>
                    )}

                    <div className="flex items-center justify-center gap-2 mt-2 text-xs text-slate-500">
                        <span>{formatoTamano(file.tamaño)}</span>
                        <span className="text-slate-300">•</span>
                        <span>
                            {new Date(file.created_at).toLocaleDateString()}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
