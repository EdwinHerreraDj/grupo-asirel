// resources/js/react/shared/components/Pagination.jsx
import React from "react";

export default function Pagination({
    currentPage,
    lastPage,
    total,
    onPageChange,
}) {
    const pages = [];
    const maxPagesToShow = 5;

    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(lastPage, startPage + maxPagesToShow - 1);

    if (endPage - startPage < maxPagesToShow - 1) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }

    return (
        <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
            {/* Info */}
            <div className="text-sm text-gray-600 dark:text-gray-400">
                Mostrando página{" "}
                <span className="font-medium">{currentPage}</span> de{" "}
                <span className="font-medium">{lastPage}</span> ({total}{" "}
                registros totales)
            </div>

            {/* Botones de paginación */}
            <div className="flex items-center gap-1">
                {/* Primera página */}
                <button
                    onClick={() => onPageChange(1)}
                    disabled={currentPage === 1}
                    className="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    title="Primera página"
                >
                    <i className="mgc_transfer_line rotate-180"></i>
                </button>

                {/* Página anterior */}
                <button
                    onClick={() => onPageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                    className="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    title="Anterior"
                >
                    <i className="mgc_left_line"></i>
                </button>

                {/* Números de página */}
                {startPage > 1 && (
                    <span className="px-2 text-gray-400">...</span>
                )}

                {pages.map((page) => (
                    <button
                        key={page}
                        onClick={() => onPageChange(page)}
                        className={`px-4 py-1.5 rounded-lg border transition ${
                            currentPage === page
                                ? "bg-primary text-white border-primary"
                                : "border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300"
                        }`}
                    >
                        {page}
                    </button>
                ))}

                {endPage < lastPage && (
                    <span className="px-2 text-gray-400">...</span>
                )}

                {/* Página siguiente */}
                <button
                    onClick={() => onPageChange(currentPage + 1)}
                    disabled={currentPage === lastPage}
                    className="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    title="Siguiente"
                >
                    <i className="mgc_right_line"></i>
                </button>

                {/* Última página */}
                <button
                    onClick={() => onPageChange(lastPage)}
                    disabled={currentPage === lastPage}
                    className="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    title="Última página"
                >
                    <i className="mgc_transfer_line"></i>
                </button>
            </div>
        </div>
    );
}
