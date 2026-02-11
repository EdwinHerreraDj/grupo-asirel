import React, { useState, useRef, useEffect } from "react";
import api from "../../shared/api";
import { useClipboard } from "../context/ClipboardContext";
import { useNotification } from "../context/NotificationContext";
import DownloadModal from "./DownloadModal";

export default function FolderItem({ folder, onClick, onDelete, onRename }) {
    const [showMenu, setShowMenu] = useState(false);
    const [isRenaming, setIsRenaming] = useState(false);
    const [newName, setNewName] = useState(folder.nombre);
    const [downloading, setDownloading] = useState(false);
    const menuRef = useRef(null);
    const inputRef = useRef(null);

    const { cutSingleFolder } = useClipboard();
    const { showSuccess, showError, showWarning } = useNotification(); // ✅ Importar showWarning

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (menuRef.current && !menuRef.current.contains(event.target)) {
                setShowMenu(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        if (isRenaming && inputRef.current) {
            inputRef.current.focus();
            inputRef.current.select();
        }
    }, [isRenaming]);

    const handleRename = async () => {
        if (newName.trim() && newName !== folder.nombre) {
            const success = await onRename(newName.trim());
            if (success) {
                setIsRenaming(false);
            }
        } else {
            setNewName(folder.nombre);
            setIsRenaming(false);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === "Enter") {
            handleRename();
        } else if (e.key === "Escape") {
            setNewName(folder.nombre);
            setIsRenaming(false);
        }
    };

    const handleCut = () => {
        const result = cutSingleFolder(folder);
        setShowMenu(false);

        if (result.success) {
            showSuccess(result.message);
        }
    };

    const handleDownloadFolder = async () => {
        setShowMenu(false);
        setDownloading(true);

        try {
            const response = await api.get(`/folders/${folder.id}/download`, {
                responseType: "blob",
                timeout: 120000, // 2 minutos
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", `${folder.nombre}.zip`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            showSuccess(`Archivos de "${folder.nombre}" descargados exitosamente`);
        } catch (error) {
            console.error("Error downloading folder:", error);
            
            if (error.response?.status === 422) {
                showWarning(error.response.data.message || "Esta carpeta no contiene archivos");
            } else if (error.code === 'ECONNABORTED') {
                showError("La descarga tardó demasiado tiempo");
            } else {
                showError(error.response?.data?.message || "Error al descargar la carpeta");
            }
        } finally {
            setDownloading(false);
        }
    };

    return (
        <>
            <div className="relative group">
                <div
                    className="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-400 hover:shadow-md transition-all cursor-pointer bg-white dark:bg-gray-800"
                    onDoubleClick={onClick}
                >
                    <div className="flex items-start justify-between mb-3">
                        <div className="flex-1" onClick={onClick}>
                            <i className="mgc_folder_fill text-5xl text-yellow-500"></i>
                        </div>

                        {/* Menu Button */}
                        <div className="relative" ref={menuRef}>
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setShowMenu(!showMenu);
                                }}
                                className="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <i className="mgc_more_2_fill text-gray-600 dark:text-gray-400"></i>
                            </button>

                            {/* Dropdown Menu */}
                            {showMenu && (
                                <div className="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-10">
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            onClick();
                                            setShowMenu(false);
                                        }}
                                        className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                    >
                                        <i className="mgc_folder_open_line"></i>
                                        Abrir
                                    </button>
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setIsRenaming(true);
                                            setShowMenu(false);
                                        }}
                                        className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                    >
                                        <i className="mgc_edit_line"></i>
                                        Renombrar
                                    </button>
                                    <hr className="my-1 border-gray-200 dark:border-gray-700" />
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            handleDownloadFolder();
                                        }}
                                        className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                    >
                                        <i className="mgc_download_line"></i>
                                        Descargar archivos
                                    </button>
                                    <hr className="my-1 border-gray-200 dark:border-gray-700" />
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            handleCut();
                                        }}
                                        className="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                    >
                                        <i className="mgc_scissors_line"></i>
                                        Cortar
                                    </button>
                                    <hr className="my-1 border-gray-200 dark:border-gray-700" />
                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            onDelete();
                                            setShowMenu(false);
                                        }}
                                        className="w-full text-left px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 flex items-center gap-2"
                                    >
                                        <i className="mgc_delete_line"></i>
                                        Eliminar
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Folder Name */}
                    <div onClick={onClick}>
                        {isRenaming ? (
                            <input
                                ref={inputRef}
                                type="text"
                                value={newName}
                                onChange={(e) => setNewName(e.target.value)}
                                onBlur={handleRename}
                                onKeyDown={handleKeyDown}
                                onClick={(e) => e.stopPropagation()}
                                className="w-full px-2 py-1 text-sm border border-blue-500 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            />
                        ) : (
                            <p className="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                {folder.nombre}
                            </p>
                        )}
                        <p className="text-xs text-gray-500 mt-1">
                            {new Date(folder.created_at).toLocaleDateString()}
                        </p>
                    </div>
                </div>
            </div>

            {/* Modal de descarga */}
            <DownloadModal
                isOpen={downloading}
                folderName={folder.nombre}
            />
        </>
    );
}