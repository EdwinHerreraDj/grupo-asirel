// resources/js/react/Drive/components/SelectionBar.jsx
import React from 'react';
import { useClipboard } from '../context/ClipboardContext';

export default function SelectionBar({ selectedFiles, onClearSelection, onCut }) {
    const { MAX_SELECTION } = useClipboard();
    
    if (selectedFiles.length === 0) return null;

    return (
        <div className="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center gap-4">
                {/* Contador */}
                <div className="flex items-center gap-2">
                    <div className="bg-orange-100 dark:bg-orange-900 text-orange-700 dark:text-orange-300 rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">
                        {selectedFiles.length}
                    </div>
                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {selectedFiles.length === 1 ? 'archivo seleccionado' : 'archivos seleccionados'}
                        {selectedFiles.length >= MAX_SELECTION && (
                            <span className="text-orange-600 ml-1">(máximo)</span>
                        )}
                    </span>
                </div>

                <div className="h-6 w-px bg-gray-300 dark:bg-gray-600"></div>

                {/* Botón de cortar */}
                <div className="flex items-center gap-2">
                    <button
                        onClick={onCut}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium"
                    >
                        <i className="mgc_scissors_line"></i>
                        Cortar para Mover
                    </button>

                    <button
                        onClick={onClearSelection}
                        className="inline-flex items-center gap-2 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm"
                    >
                        <i className="mgc_close_line"></i>
                    </button>
                </div>
            </div>
        </div>
    );
}