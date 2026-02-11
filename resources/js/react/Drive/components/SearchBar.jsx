import React, { useState, useEffect, useRef } from 'react';
import api from '../../shared/api';

export default function SearchBar({ onSearch, onClear }) {
    const [query, setQuery] = useState('');
    const [searching, setSearching] = useState(false);
    const [showDropdown, setShowDropdown] = useState(false);
    const [results, setResults] = useState({ folders: [], files: [], total: 0 });
    const searchRef = useRef(null);
    const timeoutRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (searchRef.current && !searchRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
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
                const response = await api.get('/drive/search', {
                    params: { q: query }
                });

                setResults(response.data);
                setShowDropdown(true);
                
                // Notificar al componente padre
                onSearch(response.data);
            } catch (error) {
                console.error('Error searching:', error);
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
        setQuery('');
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
                selectedItem: { ...item, type } 
            });
        }
    };

    return (
        <div ref={searchRef} className="relative flex-1 max-w-2xl">
            <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    {searching ? (
                        <div className="w-5 h-5 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
                    ) : (
                        <i className="mgc_search_line text-xl text-gray-400"></i>
                    )}
                </div>
                
                <input
                    type="text"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Buscar archivos y carpetas..."
                    className="w-full pl-10 pr-10 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                />

                {query && (
                    <button
                        onClick={handleClear}
                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                    >
                        <i className="mgc_close_line text-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"></i>
                    </button>
                )}
            </div>

            {/* Dropdown con resultados */}
            {showDropdown && results.total > 0 && (
                <div className="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto">
                    {/* Carpetas */}
                    {results.folders.length > 0 && (
                        <div className="p-2">
                            <div className="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Carpetas ({results.folders.length})
                            </div>
                            {results.folders.map((folder) => (
                                <button
                                    key={`folder-${folder.id}`}
                                    onClick={() => handleResultClick(folder, 'folder')}
                                    className="w-full px-3 py-2 flex items-start gap-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-left"
                                >
                                    <i className="mgc_folder_fill text-2xl text-yellow-500 flex-shrink-0 mt-0.5"></i>
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-gray-800 dark:text-white truncate">
                                            {folder.nombre}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {folder.path}
                                        </p>
                                    </div>
                                </button>
                            ))}
                        </div>
                    )}

                    {/* Separador */}
                    {results.folders.length > 0 && results.files.length > 0 && (
                        <hr className="border-gray-200 dark:border-gray-700" />
                    )}

                    {/* Archivos */}
                    {results.files.length > 0 && (
                        <div className="p-2">
                            <div className="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Archivos ({results.files.length})
                            </div>
                            {results.files.map((file) => (
                                <button
                                    key={`file-${file.id}`}
                                    onClick={() => handleResultClick(file, 'file')}
                                    className="w-full px-3 py-2 flex items-start gap-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-left"
                                >
                                    <i className="mgc_file_line text-2xl text-blue-500 flex-shrink-0 mt-0.5"></i>
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-medium text-gray-800 dark:text-white truncate">
                                            {file.nombre}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {file.folder_path}
                                        </p>
                                    </div>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* No hay resultados */}
            {showDropdown && results.total === 0 && query.length >= 2 && !searching && (
                <div className="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4">
                    <div className="text-center">
                        <i className="mgc_search_line text-4xl text-gray-300 dark:text-gray-600 mb-2 block"></i>
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron resultados para "{query}"
                        </p>
                    </div>
                </div>
            )}
        </div>
    );
}