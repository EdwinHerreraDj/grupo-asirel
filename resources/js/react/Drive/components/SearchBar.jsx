import React, { useState, useEffect, useRef } from "react";
import api from "../../shared/api";

export default function SearchBar({ onSearch, onClear }) {
    const [query, setQuery] = useState("");
    const [searching, setSearching] = useState(false);
    const [showDropdown, setShowDropdown] = useState(false);
    const [results, setResults] = useState({
        folders: [],
        files: [],
        total: 0,
    });
    const searchRef = useRef(null);
    const timeoutRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (
                searchRef.current &&
                !searchRef.current.contains(event.target)
            ) {
                setShowDropdown(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () =>
            document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        if (timeoutRef.current) {
            clearTimeout(timeoutRef.current);
        }

        if (query.length < 2) {
            setResults({ folders: [], files: [], total: 0 });
            setShowDropdown(false);
            return;
        }

        setSearching(true);

        timeoutRef.current = setTimeout(async () => {
            try {
                const response = await api.get("/drive/search", {
                    params: { q: query },
                });

                setResults(response.data);
                setShowDropdown(true);

                // Notificar al componente padre
                onSearch(response.data);
            } catch (error) {
                console.error("Error searching:", error);
                setResults({ folders: [], files: [], total: 0 });
            } finally {
                setSearching(false);
            }
        }, 300); // Debounce de 300ms

        return () => {
            if (timeoutRef.current) {
                clearTimeout(timeoutRef.current);
            }
        };
    }, [query]);

    const handleClear = () => {
        setQuery("");
        setResults({ folders: [], files: [], total: 0 });
        setShowDropdown(false);
        onClear();
    };

    const handleResultClick = (item, type) => {
        setShowDropdown(false);
        // El componente padre manejará la navegación
        if (onSearch) {
            onSearch({
                ...results,
                selectedItem: { ...item, type },
            });
        }
    };

    return (
        <div ref={searchRef} className="relative flex-1 max-w-2xl">
            <div className="relative">
                {/* Icono izquierda */}
                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    {searching ? (
                        <div className="w-5 h-5 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></div>
                    ) : (
                        <i className="mgc_search_line text-xl text-slate-400"></i>
                    )}
                </div>

                <input
                    type="text"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Buscar archivos y carpetas..."
                    className="
                w-full
                pl-12 pr-12 py-3
                rounded-2xl
                border border-slate-300
                bg-white
                text-slate-700
                placeholder-slate-400
                shadow-sm
                focus:outline-none
                focus:ring-2 focus:ring-indigo-500
                focus:border-indigo-500
                transition-all
            "
                />

                {/* Botón limpiar */}
                {query && (
                    <button
                        onClick={handleClear}
                        className="absolute inset-y-0 right-0 pr-4 flex items-center"
                    >
                        <div className="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 transition">
                            <i className="mgc_close_line text-lg text-slate-400 hover:text-slate-600"></i>
                        </div>
                    </button>
                )}
            </div>

            {/* Dropdown resultados */}
            {showDropdown && results.total > 0 && (
                <div className="absolute z-50 w-full mt-3 bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-96 overflow-y-auto">
                    {/* Carpetas */}
                    {results.folders.length > 0 && (
                        <div className="p-3">
                            <div className="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Carpetas ({results.folders.length})
                            </div>

                            {results.folders.map((folder) => (
                                <button
                                    key={`folder-${folder.id}`}
                                    onClick={() =>
                                        handleResultClick(folder, "folder")
                                    }
                                    className="w-full px-3 py-3 flex items-start gap-3 hover:bg-slate-50 rounded-xl transition text-left"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                                        <i className="mgc_folder_fill text-xl text-amber-500"></i>
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-semibold text-slate-800 truncate">
                                            {folder.nombre}
                                        </p>
                                        <p className="text-xs text-slate-500 truncate">
                                            {folder.path}
                                        </p>
                                    </div>
                                </button>
                            ))}
                        </div>
                    )}

                    {/* Separador */}
                    {results.folders.length > 0 && results.files.length > 0 && (
                        <div className="border-t border-slate-200"></div>
                    )}

                    {/* Archivos */}
                    {results.files.length > 0 && (
                        <div className="p-3">
                            <div className="px-3 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                Archivos ({results.files.length})
                            </div>

                            {results.files.map((file) => (
                                <button
                                    key={`file-${file.id}`}
                                    onClick={() =>
                                        handleResultClick(file, "file")
                                    }
                                    className="w-full px-3 py-3 flex items-start gap-3 hover:bg-slate-50 rounded-xl transition text-left"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <i className="mgc_file_line text-xl text-indigo-500"></i>
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-semibold text-slate-800 truncate">
                                            {file.nombre}
                                        </p>
                                        <p className="text-xs text-slate-500 truncate">
                                            {file.folder_path}
                                        </p>
                                    </div>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* Sin resultados */}
            {showDropdown &&
                results.total === 0 &&
                query.length >= 2 &&
                !searching && (
                    <div className="absolute z-50 w-full mt-3 bg-white rounded-2xl shadow-2xl border border-slate-200 p-6">
                        <div className="text-center">
                            <div className="w-14 h-14 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                                <i className="mgc_search_line text-2xl text-slate-400"></i>
                            </div>

                            <p className="text-sm text-slate-500">
                                No se encontraron resultados para{" "}
                                <span className="font-medium text-slate-700">
                                    "{query}"
                                </span>
                            </p>
                        </div>
                    </div>
                )}
        </div>
    );
}
