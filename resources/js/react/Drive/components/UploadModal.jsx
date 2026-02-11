import React, { useState, useRef } from 'react';

export default function UploadModal({ isOpen, onClose, onUpload }) {
    const [selectedFiles, setSelectedFiles] = useState([]);
    const [hasExpiry, setHasExpiry] = useState(false);
    const [expiryDate, setExpiryDate] = useState('');
    const [uploading, setUploading] = useState(false);
    const [dragActive, setDragActive] = useState(false);
    const fileInputRef = useRef(null);

    const handleFileSelect = (e) => {
        const files = Array.from(e.target.files);
        setSelectedFiles(prev => [...prev, ...files]);
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

        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            const files = Array.from(e.dataTransfer.files);
            setSelectedFiles(prev => [...prev, ...files]);
        }
    };

    const handleRemoveFile = (index) => {
        setSelectedFiles(prev => prev.filter((_, i) => i !== index));
    };

    const handleSubmit = async () => {
        if (selectedFiles.length === 0) return;

        if (hasExpiry && !expiryDate) {
            alert('Por favor selecciona una fecha de caducidad');
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
            setExpiryDate('');
            onClose(true); // ✅ Pasar true para indicar que se subieron archivos
        } catch (error) {
            console.error('Error uploading files:', error);
        } finally {
            setUploading(false);
        }
    };

    const formatFileSize = (bytes) => {
        if (!bytes) return '0 B';
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    };

    const handleClose = () => {
        if (!uploading) {
            setSelectedFiles([]);
            setHasExpiry(false);
            setExpiryDate('');
            onClose(false); // ✅ Pasar false para indicar cancelación
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                {/* Header */}
                <div className="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div className="flex items-center justify-between">
                        <h3 className="text-xl font-semibold text-gray-800 dark:text-white">
                            Subir Archivos
                        </h3>
                        <button
                            onClick={handleClose}
                            disabled={uploading}
                            className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50"
                        >
                            <i className="mgc_close_line text-xl text-gray-600 dark:text-gray-400"></i>
                        </button>
                    </div>
                </div>

                {/* Body */}
                <div className="flex-1 overflow-y-auto px-6 py-4">
                    {/* Selector de archivos con Drag & Drop */}
                    <div className="mb-6">
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
                            onClick={() => !uploading && fileInputRef.current?.click()}
                            className={`
                                w-full border-2 border-dashed rounded-lg p-8 transition-all cursor-pointer
                                ${dragActive 
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                    : 'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500'
                                }
                                ${uploading ? 'opacity-50 cursor-not-allowed' : ''}
                            `}
                        >
                            <div className="text-center">
                                <i className={`
                                    mgc_upload_line text-5xl mb-3 block transition-colors
                                    ${dragActive 
                                        ? 'text-blue-500' 
                                        : 'text-gray-400 dark:text-gray-500'
                                    }
                                `}></i>
                                <p className="text-gray-600 dark:text-gray-400 font-medium">
                                    {dragActive 
                                        ? 'Suelta los archivos aquí' 
                                        : 'Click para seleccionar archivos'
                                    }
                                </p>
                                <p className="text-sm text-gray-500 dark:text-gray-500 mt-1">
                                    o arrastra y suelta aquí
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Lista de archivos seleccionados */}
                    {selectedFiles.length > 0 && (
                        <div className="mb-6">
                            <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Archivos seleccionados ({selectedFiles.length})
                            </h4>
                            <div className="space-y-2 max-h-48 overflow-y-auto">
                                {selectedFiles.map((file, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                    >
                                        <div className="flex items-center gap-3 flex-1 min-w-0">
                                            <i className="mgc_file_line text-xl text-gray-500 dark:text-gray-400 flex-shrink-0"></i>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
                                                    {file.name}
                                                </p>
                                                <p className="text-xs text-gray-500 dark:text-gray-500">
                                                    {formatFileSize(file.size)}
                                                </p>
                                            </div>
                                        </div>
                                        <button
                                            onClick={() => handleRemoveFile(index)}
                                            disabled={uploading}
                                            className="p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded transition-colors disabled:opacity-50"
                                        >
                                            <i className="mgc_close_line text-red-600"></i>
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Opciones de caducidad */}
                    <div className="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div className="flex items-center gap-3 mb-4">
                            <input
                                type="checkbox"
                                id="hasExpiry"
                                checked={hasExpiry}
                                onChange={(e) => setHasExpiry(e.target.checked)}
                                disabled={uploading}
                                className="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <label
                                htmlFor="hasExpiry"
                                className="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                            >
                                Estos archivos tienen fecha de caducidad
                            </label>
                        </div>

                        {hasExpiry && (
                            <div className="ml-7 animate-fade-in">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Fecha de caducidad
                                </label>
                                <input
                                    type="date"
                                    value={expiryDate}
                                    onChange={(e) => setExpiryDate(e.target.value)}
                                    disabled={uploading}
                                    min={new Date().toISOString().split('T')[0]}
                                    className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                                />
                                <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    Esta fecha se aplicará a todos los archivos seleccionados
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Footer */}
                <div className="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button
                        onClick={handleClose}
                        disabled={uploading}
                        className="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={handleSubmit}
                        disabled={selectedFiles.length === 0 || uploading}
                        className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        {uploading ? (
                            <>
                                <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Subiendo...
                            </>
                        ) : (
                            <>
                                <i className="mgc_upload_line"></i>
                                Subir {selectedFiles.length} {selectedFiles.length === 1 ? 'archivo' : 'archivos'}
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}