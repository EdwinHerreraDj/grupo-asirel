// resources/js/react/Drive/components/FileItem.jsx
import React, { useState, useRef, useEffect } from "react";

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

    const extension = file.nombre.split(".").pop().toLowerCase();
    const previewable = ["pdf", "jpg", "jpeg", "png", "gif", "webp"].includes(
        extension,
    );

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

    const isZipFile = () => {
        const ext = file.nombre.split(".").pop().toLowerCase();
        return ext === "zip";
    };

    // Sistema de iconos mejorado
    const getFileExtension = (fileName) => {
        const parts = fileName.split(".");
        return parts.length > 1 ? parts.pop().toLowerCase() : "default";
    };

    const getFileIconData = (fileName) => {
        const extension = getFileExtension(fileName);

        const iconMap = {
            pdf: {
                icon: "mgc_pdf_fill",
                color: "text-red-600 bg-red-100 dark:bg-red-900/40 dark:text-red-300",
            },
            doc: {
                icon: "mgc_file_text_fill",
                color: "text-blue-600 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300",
            },
            docx: {
                icon: "mgc_file_text_fill",
                color: "text-blue-600 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300",
            },
            xls: {
                icon: "mgc_file_excel_fill",
                color: "text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-300",
            },
            xlsx: {
                icon: "mgc_file_excel_fill",
                color: "text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-300",
            },
            ppt: {
                icon: "mgc_file_ppt_fill",
                color: "text-orange-600 bg-orange-100 dark:bg-orange-900/40 dark:text-orange-300",
            },
            pptx: {
                icon: "mgc_file_ppt_fill",
                color: "text-orange-600 bg-orange-100 dark:bg-orange-900/40 dark:text-orange-300",
            },
            jpg: {
                icon: "mgc_pic_2_fill",
                color: "text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300",
            },
            jpeg: {
                icon: "mgc_pic_2_fill",
                color: "text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300",
            },
            png: {
                icon: "mgc_pic_fill",
                color: "text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300",
            },
            gif: {
                icon: "mgc_photo_album_fill",
                color: "text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300",
            },
            svg: {
                icon: "mgc_pic_fill",
                color: "text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300",
            },
            mp4: {
                icon: "mgc_video_fill",
                color: "text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300",
            },
            mov: {
                icon: "mgc_video_fill",
                color: "text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300",
            },
            avi: {
                icon: "mgc_video_fill",
                color: "text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300",
            },
            mp3: {
                icon: "mgc_music_fill",
                color: "text-indigo-600 bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300",
            },
            wav: {
                icon: "mgc_music_fill",
                color: "text-indigo-600 bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300",
            },
            zip: {
                icon: "mgc_file_zip_fill",
                color: "text-yellow-600 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300",
            },
            rar: {
                icon: "mgc_file_zip_fill",
                color: "text-yellow-600 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300",
            },
            "7z": {
                icon: "mgc_file_zip_fill",
                color: "text-yellow-600 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300",
            },
            txt: {
                icon: "mgc_file_text_line",
                color: "text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300",
            },
            csv: {
                icon: "mgc_file_text_line",
                color: "text-teal-600 bg-teal-100 dark:bg-teal-900/40 dark:text-teal-300",
            },
            default: {
                icon: "mgc_file_line",
                color: "text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300",
            },
        };

        return iconMap[extension] || iconMap["default"];
    };

    const formatFileSize = (bytes) => {
        if (!bytes) return "0 B";

        const sizes = ["B", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (
            Math.round((bytes / Math.pow(1024, i)) * 100) / 100 + " " + sizes[i]
        );
    };

    const iconData = getFileIconData(file.nombre);

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
            {file.fecha_caducidad &&
                (() => {
                    const today = new Date();
                    const expiry = new Date(file.fecha_caducidad);
                    const diffDays = Math.ceil(
                        (expiry - today) / (1000 * 60 * 60 * 24),
                    );

                    let badgeStyles = "";
                    let badgeText = "";

                    if (diffDays < 0) {
                        badgeStyles =
                            "bg-rose-100 text-rose-700 border-rose-200";
                        badgeText = "Vencido";
                    } else if (diffDays <= 15) {
                        badgeStyles =
                            "bg-amber-100 text-amber-700 border-amber-200";
                        badgeText = `Caduca en ${diffDays}d`;
                    } else {
                        badgeStyles =
                            "bg-slate-100 text-slate-600 border-slate-200";
                        badgeText = expiry.toLocaleDateString();
                    }

                    return (
                        <div className="absolute top-3 right-3 z-20">
                            <div
                                className={`
                            px-2.5 py-1
                            text-[11px]
                            font-semibold
                            rounded-xl
                            border
                            backdrop-blur-sm
                            shadow-sm
                            ${badgeStyles}
                        `}
                            >
                                {badgeText}
                            </div>
                        </div>
                    );
                })()}

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
                                            <i className="mgc_folder_zip_line"></i>
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
                        <span>{formatFileSize(file.tamaño)}</span>
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
