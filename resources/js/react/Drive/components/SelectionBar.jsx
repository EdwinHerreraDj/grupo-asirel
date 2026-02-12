// resources/js/react/Drive/components/SelectionBar.jsx
import React from "react";
import { useClipboard } from "../context/ClipboardContext";

export default function SelectionBar({
    selectedFiles,
    onClearSelection,
    onCut,
}) {
    const { MAX_SELECTION } = useClipboard();

    if (selectedFiles.length === 0) return null;

    return (
        <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 w-full flex justify-center pointer-events-none">
            <div className="pointer-events-auto bg-white border border-slate-200 rounded-2xl shadow-2xl px-6 py-4 flex flex-col sm:flex-row items-center gap-4">
                {/* Contador */}
                <div className="flex items-center gap-3">
                    <div className="bg-amber-100 text-amber-700 rounded-full w-9 h-9 flex items-center justify-center font-bold text-sm">
                        {selectedFiles.length}
                    </div>

                    <span className="text-sm font-semibold text-slate-700 text-center sm:text-left">
                        {selectedFiles.length === 1
                            ? "archivo seleccionado"
                            : "archivos seleccionados"}

                        {selectedFiles.length >= MAX_SELECTION && (
                            <span className="text-amber-600 ml-1">
                                (máximo)
                            </span>
                        )}
                    </span>
                </div>

                <div className="hidden sm:block h-6 w-px bg-slate-200"></div>

                {/* Acciones */}
                <div className="flex items-center gap-3">
                    <button
                        onClick={onCut}
                        className="
                    inline-flex items-center gap-2
                    px-5 py-2.5
                    rounded-xl
                    bg-gradient-to-r from-amber-500 to-orange-600
                    text-white
                    text-sm
                    font-semibold
                    shadow-md
                    hover:shadow-lg
                    hover:scale-[1.03]
                    active:scale-[0.97]
                    transition-all
                "
                    >
                        <i className="mgc_scissors_line"></i>
                        Cortar para mover
                    </button>

                    <button
                        onClick={onClearSelection}
                        className="
                    w-10 h-10
                    flex items-center justify-center
                    rounded-xl
                    bg-slate-100
                    text-slate-600
                    hover:bg-slate-200
                    transition
                "
                    >
                        <i className="mgc_close_line"></i>
                    </button>
                </div>
            </div>
        </div>
    );
}
