import React, { useState, useRef } from "react";

export default function UploadModal({ isOpen, onClose, onUpload }) {
    const [selectedFiles, setSelectedFiles] = useState([]);
    const [hasExpiry, setHasExpiry] = useState(false);
    const [expiryDate, setExpiryDate] = useState("");
    const [uploading, setUploading] = useState(false);
    const [dragActive, setDragActive] = useState(false);
    const fileInputRef = useRef(null);
    const [errorMessage, setErrorMessage] = useState("");

    const handleFileSelect = (e) => {
        setErrorMessage("");

        const files = Array.from(e.target.files);

        if (files.length > 0) {
            setSelectedFiles((prev) => [...prev, ...files]);
        }
    };

    const handleDrag = (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (e.type === "dragenter" || e.type === "dragover") {
            setDragActive(true);
        } else if (e.type === "dragleave") {
            setDragActive(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);
        setErrorMessage("");

        const items = e.dataTransfer.items;
        if (!items) return;

        const newFiles = [];
        let folderDetected = false;

        for (let i = 0; i < items.length; i++) {
            const item = items[i];

            if (item.kind === "file") {
                const entry = item.webkitGetAsEntry?.();

                if (entry && entry.isDirectory) {
                    folderDetected = true;
                    continue;
                }

                const file = item.getAsFile();
                if (file) newFiles.push(file);
            }
        }

        if (folderDetected) {
            setErrorMessage(
                "No se pueden subir carpetas. Solo archivos individuales.",
            );
        }

        if (newFiles.length > 0) {
            setSelectedFiles((prev) => [...prev, ...newFiles]);
        }
    };

    const handleRemoveFile = (index) => {
        setSelectedFiles((prev) => prev.filter((_, i) => i !== index));
    };

    const handleSubmit = async () => {
        if (selectedFiles.length === 0) return;

        if (hasExpiry && !expiryDate) {
            alert("Por favor selecciona una fecha de caducidad");
            return;
        }

        setUploading(true);

        try {
            let successCount = 0;

            // Subir archivos uno por uno
            for (const file of selectedFiles) {
                const success = await onUpload(file, hasExpiry, expiryDate);
                if (success) {
                    successCount++;
                }
            }

            // Limpiar y cerrar
            setSelectedFiles([]);
            setHasExpiry(false);
            setExpiryDate("");
            onClose(true);
        } catch (error) {
            console.error("Error uploading files:", error);
        } finally {
            setUploading(false);
        }
    };

    const formatFileSize = (bytes) => {
        if (!bytes) return "0 B";
        const sizes = ["B", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (
            Math.round((bytes / Math.pow(1024, i)) * 100) / 100 + " " + sizes[i]
        );
    };

    const handleClose = () => {
        if (!uploading) {
            setSelectedFiles([]);
            setHasExpiry(false);
            setExpiryDate("");
            onClose(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col border border-slate-200">
                {/* Header */}
                <div className="px-6 py-5 border-b border-slate-200">
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="text-2xl font-bold text-slate-800">
                                Subir Archivos
                            </h3>
                            <p className="text-sm text-slate-500 mt-1">
                                Añade documentos al Drive empresarial
                            </p>
                        </div>

                        <button
                            onClick={handleClose}
                            disabled={uploading}
                            className="w-10 h-10 flex items-center justify-center rounded-2xl hover:bg-slate-100 transition disabled:opacity-50"
                        >
                            <i className="mgc_close_line text-xl text-slate-500"></i>
                        </button>
                    </div>
                </div>

                {/* Body */}
                <div className="flex-1 overflow-y-auto px-6 py-6 space-y-8">
                    {/* Drag & Drop */}
                    <div>
                        <input
                            ref={fileInputRef}
                            type="file"
                            multiple
                            onChange={handleFileSelect}
                            className="hidden"
                            disabled={uploading}
                        />

                        <div
                            onDragEnter={handleDrag}
                            onDragLeave={handleDrag}
                            onDragOver={handleDrag}
                            onDrop={handleDrop}
                            onClick={() =>
                                !uploading && fileInputRef.current?.click()
                            }
                            className={`
                        w-full border-2 border-dashed rounded-3xl p-10 transition-all cursor-pointer
                        ${
                            dragActive
                                ? "border-indigo-500 bg-indigo-50"
                                : "border-slate-300 hover:border-indigo-400"
                        }
                        ${uploading ? "opacity-50 cursor-not-allowed" : ""}
                    `}
                        >
                            <div className="text-center">
                                <i
                                    className={`
                                mgc_upload_line text-6xl mb-4 block transition-colors
                                ${
                                    dragActive
                                        ? "text-indigo-500"
                                        : "text-slate-400"
                                }
                            `}
                                ></i>

                                <p className="text-slate-700 font-semibold">
                                    {dragActive
                                        ? "Suelta los archivos aquí"
                                        : "Haz click para seleccionar archivos"}
                                </p>

                                <p className="text-sm text-slate-500 mt-2">
                                    o arrastra y suéltalos en esta área
                                </p>
                            </div>
                        </div>
                    </div>
                    {errorMessage && (
                        <div className="mt-4 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm">
                            <div className="flex items-start gap-2">
                                <i className="mgc_warning_line text-lg mt-0.5"></i>
                                <span>{errorMessage}</span>
                            </div>
                        </div>
                    )}

                    {/* Lista de archivos */}
                    {selectedFiles.length > 0 && (
                        <div>
                            <h4 className="text-sm font-semibold text-slate-700 mb-4">
                                Archivos seleccionados ({selectedFiles.length})
                            </h4>

                            <div className="space-y-3 max-h-56 overflow-y-auto pr-1">
                                {selectedFiles.map((file, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-2xl"
                                    >
                                        <div className="flex items-center gap-3 flex-1 min-w-0">
                                            <div className="w-10 h-10 rounded-xl bg-slate-200 flex items-center justify-center">
                                                <i className="mgc_file_line text-lg text-slate-500"></i>
                                            </div>

                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-slate-700 truncate">
                                                    {file.name}
                                                </p>
                                                <p className="text-xs text-slate-500">
                                                    {formatFileSize(file.size)}
                                                </p>
                                            </div>
                                        </div>

                                        <button
                                            onClick={() =>
                                                handleRemoveFile(index)
                                            }
                                            disabled={uploading}
                                            className="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-rose-50 transition disabled:opacity-50"
                                        >
                                            <i className="mgc_close_line text-rose-600"></i>
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Caducidad */}
                    <div className="border-t border-slate-200 pt-6">
                        <div className="flex items-center gap-3 mb-4">
                            <input
                                type="checkbox"
                                id="hasExpiry"
                                checked={hasExpiry}
                                onChange={(e) => setHasExpiry(e.target.checked)}
                                disabled={uploading}
                                className="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <label
                                htmlFor="hasExpiry"
                                className="text-sm font-medium text-slate-700 cursor-pointer"
                            >
                                Estos archivos tienen fecha de caducidad
                            </label>
                        </div>

                        {hasExpiry && (
                            <div className="ml-8 space-y-3">
                                <label className="block text-sm font-medium text-slate-700">
                                    Fecha de caducidad
                                </label>

                                <input
                                    type="date"
                                    value={expiryDate}
                                    onChange={(e) =>
                                        setExpiryDate(e.target.value)
                                    }
                                    disabled={uploading}
                                    min={new Date().toISOString().split("T")[0]}
                                    className="w-full px-4 py-3 border border-slate-300 rounded-2xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm disabled:opacity-50"
                                />

                                <p className="text-xs text-slate-500">
                                    Esta fecha se aplicará a todos los archivos
                                    seleccionados
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Footer */}
                <div className="px-6 py-5 border-t border-slate-200 flex flex-col sm:flex-row justify-end gap-3">
                    <button
                        onClick={handleClose}
                        disabled={uploading}
                        className="w-full sm:w-auto px-5 py-3 rounded-2xl bg-white border border-slate-300 text-slate-600 font-medium hover:bg-slate-100 transition-all disabled:opacity-50"
                    >
                        Cancelar
                    </button>

                    <button
                        onClick={handleSubmit}
                        disabled={selectedFiles.length === 0 || uploading}
                        className="w-full sm:w-auto px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold shadow-md hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        {uploading ? (
                            <>
                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Subiendo...
                            </>
                        ) : (
                            <>
                                <i className="mgc_upload_line"></i>
                                Subir {selectedFiles.length}{" "}
                                {selectedFiles.length === 1
                                    ? "archivo"
                                    : "archivos"}
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}
