// resources/js/react/Drive/components/DownloadModal.jsx
import React, { useEffect, useState } from 'react';

export default function DownloadModal({ isOpen, folderName }) {
    const [dots, setDots] = useState('');

    useEffect(() => {
        if (!isOpen) return;
        
        const interval = setInterval(() => {
            setDots(prev => prev.length >= 3 ? '' : prev + '.');
        }, 500);

        return () => clearInterval(interval);
    }, [isOpen]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md p-8 mx-4">
                {/* Icono animado */}
                <div className="flex justify-center mb-6">
                    <div className="relative">
                        <div className="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <i className="mgc_download_line text-4xl text-blue-600 dark:text-blue-400 animate-bounce"></i>
                        </div>
                        <div className="absolute inset-0 rounded-full border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 animate-spin"></div>
                    </div>
                </div>

                {/* Texto */}
                <div className="text-center">
                    <h3 className="text-xl font-semibold text-gray-800 dark:text-white mb-2">
                        Preparando descarga{dots}
                    </h3>
                    <p className="text-gray-600 dark:text-gray-400 mb-1">
                        Comprimiendo carpeta
                    </p>
                    <p className="text-sm font-medium text-blue-600 dark:text-blue-400 truncate px-4">
                        "{folderName}"
                    </p>
                    <p className="text-xs text-gray-500 dark:text-gray-500 mt-4">
                        Esto puede tomar varios minutos para carpetas grandes...
                    </p>
                </div>

                {/* Barra de progreso indeterminada */}
                <div className="mt-6 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                    <div className="h-full bg-gradient-to-r from-blue-500 to-blue-600 animate-progress-indeterminate"></div>
                </div>

                {/* Advertencia */}
                <div className="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <p className="text-xs text-yellow-800 dark:text-yellow-200 text-center">
                        ⚠️ No cierres esta ventana
                    </p>
                </div>
            </div>
        </div>
    );
}