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
        bg-slate-900/70 backdrop-blur-sm
        flex items-center justify-center 
        z-50 p-4
        animate-[fadeIn_.15s_ease-out]
    "
            onClick={onClose}
        >
            <div
                className="
            w-full max-w-md
            bg-white
            rounded-3xl
            border border-slate-200
            shadow-2xl
        "
                onClick={(e) => e.stopPropagation()}
            >
                {/* HEADER */}
                <div className="flex items-center justify-between px-7 py-6 border-b border-slate-200">
                    <div className="flex items-center gap-4">
                        <div
                            className="
                        h-11 w-11
                        rounded-2xl
                        bg-gradient-to-br from-indigo-100 to-blue-100
                        flex items-center justify-center
                        shadow-inner
                    "
                        >
                            <i className="mgc_folder_2_fill text-indigo-600 text-xl"></i>
                        </div>

                        <h3 className="text-lg font-semibold tracking-tight text-slate-900">
                            Nueva carpeta
                        </h3>
                    </div>

                    <button
                        onClick={onClose}
                        className="
                    w-9 h-9
                    flex items-center justify-center
                    rounded-2xl
                    text-slate-500
                    hover:bg-slate-100
                    hover:text-slate-700
                    transition
                "
                    >
                        <i className="mgc_close_line text-xl"></i>
                    </button>
                </div>

                {/* BODY */}
                <form onSubmit={handleSubmit} className="px-7 py-7">
                    <div className="space-y-3">
                        <label className="text-sm font-semibold text-slate-700">
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
                        w-full px-4 py-3
                        rounded-2xl
                        border border-slate-300
                        bg-slate-50
                        text-slate-900
                        placeholder-slate-400
                        focus:outline-none
                        focus:ring-2
                        focus:ring-indigo-500
                        focus:border-indigo-500
                        transition-all
                        shadow-sm
                    "
                        />

                        <p className="text-xs text-slate-500">
                            Usa nombres claros para facilitar la organización
                            documental.
                        </p>
                    </div>

                    {/* FOOTER */}
                    <div className="flex flex-col sm:flex-row justify-end gap-3 mt-10">
                        <button
                            type="button"
                            onClick={onClose}
                            className="
                        w-full sm:w-auto
                        px-5 py-2.5
                        rounded-2xl
                        font-medium
                        text-slate-600
                        bg-white
                        border border-slate-300
                        hover:bg-slate-100
                        transition
                    "
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            disabled={loading || !folderName.trim()}
                            className="
                        w-full sm:w-auto
                        inline-flex items-center justify-center gap-2
                        px-6 py-2.5
                        rounded-2xl
                        bg-gradient-to-r from-indigo-600 to-blue-600
                        text-white
                        font-semibold
                        shadow-md
                        hover:shadow-lg
                        hover:scale-[1.02]
                        active:scale-[0.98]
                        transition-all
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
