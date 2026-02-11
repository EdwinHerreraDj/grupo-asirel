// resources/js/react/Drive/components/CreateFolderModal.jsx
import React, { useState, useEffect, useRef } from "react";

export default function CreateFolderModal({ onClose, onSubmit }) {
    const [folderName, setFolderName] = useState("");
    const [loading, setLoading] = useState(false);
    const inputRef = useRef(null);

    useEffect(() => {
        inputRef.current?.focus();
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!folderName.trim()) {
            alert("El nombre de la carpeta no puede estar vacío");
            return;
        }

        setLoading(true);
        await onSubmit(folderName.trim());
        setLoading(false);
    };

    const handleKeyDown = (e) => {
        if (e.key === "Escape") {
            onClose();
        }
    };

    return (
        <div
            className="
        fixed inset-0 
        bg-black/70 backdrop-blur-sm
        flex items-center justify-center 
        z-50 p-4
        animate-[fadeIn_.12s_ease-out]
    "
            onClick={onClose}
        >
            <div
                className="
            w-full max-w-md
            bg-white dark:bg-gray-900
            rounded-2xl
            border border-gray-200 dark:border-gray-800
            shadow-[0_25px_70px_-20px_rgba(0,0,0,0.45)]
            onClick={(e) => e.stopPropagation()}
        "
                onClick={(e) => e.stopPropagation()}
            >
                {/* HEADER */}
                <div className="flex items-center justify-between px-6 py-5 border-b border-gray-200 dark:border-gray-800">
                    <div className="flex items-center gap-3">
                        <div
                            className="
                    h-10 w-10
                    rounded-xl
                    bg-indigo-50
                    dark:bg-indigo-900/20
                    flex items-center justify-center
                "
                        >
                            <i className="mgc_folder_2_fill text-indigo-600 text-xl"></i>
                        </div>

                        <h3 className="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                            Nueva carpeta
                        </h3>
                    </div>

                    <button
                        onClick={onClose}
                        className="
                    p-2 rounded-xl
                    text-gray-500
                    hover:bg-gray-100
                    dark:hover:bg-gray-800
                    hover:text-gray-700
                    dark:hover:text-gray-300
                    transition
                "
                    >
                        <i className="mgc_close_line text-xl"></i>
                    </button>
                </div>

                {/* BODY */}
                <form onSubmit={handleSubmit} className="px-6 py-6">
                    <div className="space-y-2">
                        <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nombre de la carpeta
                        </label>

                        <input
                            ref={inputRef}
                            type="text"
                            value={folderName}
                            onChange={(e) => setFolderName(e.target.value)}
                            onKeyDown={handleKeyDown}
                            placeholder="Ej: Contratos, Facturación, Personal…"
                            className="
                        w-full px-4 py-2.5
                        rounded-xl
                        border border-gray-300 dark:border-gray-700
                        bg-white dark:bg-gray-800
                        text-gray-900 dark:text-white
                        placeholder-gray-400
                        focus:outline-none
                        focus:ring-2
                        focus:ring-indigo-500/30
                        focus:border-indigo-500
                        transition
                    "
                        />

                        <p className="text-xs text-gray-500 dark:text-gray-400">
                            Usa nombres claros para facilitar la organización
                            documental.
                        </p>
                    </div>

                    {/* FOOTER */}
                    <div className="flex justify-end gap-3 mt-8">
                        <button
                            type="button"
                            onClick={onClose}
                            className="
                        px-4 py-2.5
                        rounded-xl
                        font-medium
                        text-gray-700 dark:text-gray-300
                        hover:bg-gray-100
                        dark:hover:bg-gray-800
                        transition
                    "
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            disabled={loading || !folderName.trim()}
                            className="
                        inline-flex items-center gap-2
                        px-5 py-2.5
                        rounded-xl
                        bg-indigo-600
                        text-white
                        font-medium
                        hover:bg-indigo-500
                        transition
                        shadow-sm
                        disabled:opacity-50
                        disabled:cursor-not-allowed
                    "
                        >
                            {loading && (
                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            )}

                            {loading ? "Creando..." : "Crear carpeta"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
