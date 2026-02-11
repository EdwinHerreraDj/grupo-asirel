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
            {/* Checkbox de selección */}
            <div className="absolute top-2 left-2 z-10">
                <input
                    type="checkbox"
                    checked={isSelected}
                    onChange={handleCheckboxChange}
                    className="
                w-5 h-5
                rounded-md
                border-gray-300
                text-indigo-600
                focus:ring-indigo-500
                cursor-pointer
            "
                />
            </div>

            {/* BADGE CADUCIDAD */}
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
                            "bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400";
                        badgeText = "Vencido";
                    } else if (diffDays <= 15) {
                        badgeStyles =
                            "bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400";
                        badgeText = `Caduca en ${diffDays}d`;
                    } else {
                        badgeStyles =
                            "bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300";
                        badgeText = expiry.toLocaleDateString();
                    }

                    return (
                        <div className="absolute top-2 right-2 z-10">
                            <div
                                className={`
                        px-2 py-1
                        text-[11px]
                        font-semibold
                        rounded-lg
                        border
                        backdrop-blur-sm
                        ${badgeStyles}
                    `}
                            >
                                {badgeText}
                            </div>
                        </div>
                    );
                })()}

            <div
                className={`
            border
            rounded-xl
            p-4
            transition
            bg-white dark:bg-gray-900

            ${
                isSelected
                    ? "border-indigo-500 ring-2 ring-indigo-200 dark:ring-indigo-800"
                    : "border-gray-200 dark:border-gray-800 hover:border-gray-300 hover:shadow-sm"
            }
        `}
            >
                <div className="flex items-start justify-between mb-3">
                    {/* Icono */}
                    <div className="flex-1 flex justify-center">
                        <div
                            className={`
                        w-16 h-16
                        rounded-2xl
                        flex items-center justify-center
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
                        p-2
                        rounded-xl
                        hover:bg-gray-100
                        dark:hover:bg-gray-800
                        opacity-0
                        group-hover:opacity-100
                        transition
                    "
                        >
                            <i className="mgc_more_2_fill text-gray-600 dark:text-gray-400"></i>
                        </button>

                        {showMenu && (
                            <div
                                className="
                        absolute right-0 mt-2 w-48
                        bg-white dark:bg-gray-900
                        rounded-xl
                        shadow-lg
                        border border-gray-200 dark:border-gray-800
                        py-1
                        z-10
                    "
                            >
                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onDownload();
                                        setShowMenu(false);
                                    }}
                                    className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-2"
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
                                        className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-2"
                                        title="Ver"
                                    >
                                        <i className="mgc_eye_2_line"></i>
                                        Ver
                                    </button>
                                )}

                                {isZipFile() && (
                                    <>
                                        <hr className="my-1 border-gray-200 dark:border-gray-800" />
                                        <button
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                onExtract();
                                                setShowMenu(false);
                                            }}
                                            className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-2"
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
                                    className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-2"
                                >
                                    <i className="mgc_edit_line"></i>
                                    Renombrar
                                </button>

                                <hr className="my-1 border-gray-200 dark:border-gray-800" />

                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onDelete();
                                        setShowMenu(false);
                                    }}
                                    className="
                                w-full text-left px-4 py-2
                                hover:bg-red-50 dark:hover:bg-red-900/20
                                text-red-600
                                flex items-center gap-2
                            "
                                >
                                    <i className="mgc_delete_line"></i>
                                    Eliminar
                                </button>
                            </div>
                        )}
                    </div>
                </div>

                {/* Info */}
                <div>
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
                        w-full px-2 py-1 text-sm
                        border border-indigo-500
                        rounded-lg
                        focus:outline-none
                        focus:ring-2
                        focus:ring-indigo-500
                        dark:bg-gray-800
                        dark:text-white
                    "
                        />
                    ) : (
                        <p
                            className="text-sm font-medium text-gray-700 dark:text-gray-300 truncate text-center"
                            title={file.nombre}
                        >
                            {file.nombre}
                        </p>
                    )}

                    <div className="flex items-center justify-center gap-2 mt-1">
                        <p className="text-xs text-gray-500">
                            {formatFileSize(file.tamaño)}
                        </p>
                        <span className="text-gray-400">•</span>
                        <p className="text-xs text-gray-500">
                            {new Date(file.created_at).toLocaleDateString()}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
