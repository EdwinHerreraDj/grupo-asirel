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
            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            onClick={onClose}
        >
            <div 
                className="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-xl font-semibold text-gray-800 dark:text-white">
                        Nueva Carpeta
                    </h3>
                    <button
                        onClick={onClose}
                        className="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                    >
                        <i className="mgc_close_line text-2xl"></i>
                    </button>
                </div>

                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre de la carpeta
                        </label>
                        <input
                            ref={inputRef}
                            type="text"
                            value={folderName}
                            onChange={(e) => setFolderName(e.target.value)}
                            onKeyDown={handleKeyDown}
                            placeholder="Ej: Documentos, Imágenes, etc."
                            className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 
                                     rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500
                                     dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div className="flex justify-end gap-3">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 text-gray-700 dark:text-gray-300 
                                     hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={loading || !folderName.trim()}
                            className="px-4 py-2 bg-blue-600 text-white rounded-lg 
                                     hover:bg-blue-700 transition-colors disabled:opacity-50 
                                     disabled:cursor-not-allowed"
                        >
                            {loading ? "Creando..." : "Crear Carpeta"}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}