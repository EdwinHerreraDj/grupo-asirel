// resources/js/react/Drive/components/DownloadModal.jsx
import React, { useEffect, useState } from "react";

export default function DownloadModal({ isOpen, folderName }) {
    const [dots, setDots] = useState("");

    useEffect(() => {
        if (!isOpen) return;

        const interval = setInterval(() => {
            setDots((prev) => (prev.length >= 3 ? "" : prev + "."));
        }, 500);

        return () => clearInterval(interval);
    }, [isOpen]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 p-8">
                {/* Icono animado */}
                <div className="flex justify-center mb-8">
                    <div className="relative">
                        <div className="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center shadow-inner">
                            <i className="mgc_download_line text-5xl text-indigo-600 animate-bounce"></i>
                        </div>

                        <div className="absolute inset-0 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin"></div>
                    </div>
                </div>

                {/* Texto */}
                <div className="text-center">
                    <h3 className="text-2xl font-bold text-slate-800 mb-2">
                        Preparando descarga{dots}
                    </h3>

                    <p className="text-slate-500 mb-1">Comprimiendo carpeta</p>

                    <p className="text-sm font-semibold text-indigo-600 truncate px-6">
                        "{folderName}"
                    </p>

                    <p className="text-xs text-slate-400 mt-4">
                        Esto puede tomar varios minutos para carpetas grandes...
                    </p>
                </div>

                {/* Barra de progreso */}
                <div className="mt-8 w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div className="h-full bg-gradient-to-r from-indigo-500 to-blue-600 animate-progress-indeterminate"></div>
                </div>

                {/* Advertencia */}
                <div className="mt-6 p-4 bg-amber-50 rounded-2xl border border-amber-200">
                    <p className="text-xs text-amber-800 text-center font-medium">
                        ⚠️ No cierres esta ventana
                    </p>
                </div>
            </div>
        </div>
    );
}
