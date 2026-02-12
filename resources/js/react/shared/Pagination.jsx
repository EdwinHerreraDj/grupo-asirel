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
        <div className="flex flex-col lg:flex-row items-center justify-between gap-6">
            {/* Info */}
            <div className="text-sm text-slate-500 text-center lg:text-left">
                Mostrando página{" "}
                <span className="font-semibold text-slate-700">
                    {currentPage}
                </span>{" "}
                de{" "}
                <span className="font-semibold text-slate-700">{lastPage}</span>{" "}
                <span className="hidden sm:inline">
                    ({total} registros totales)
                </span>
            </div>

            {/* Botones de paginación */}
            <div className="flex flex-wrap items-center justify-center gap-2">
                {/* Primera página */}
                <button
                    onClick={() => onPageChange(1)}
                    disabled={currentPage === 1}
                    className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                    title="Primera página"
                >
                    <i className="mgc_transfer_line rotate-180 text-base"></i>
                </button>

                {/* Página anterior */}
                <button
                    onClick={() => onPageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                    className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                    title="Anterior"
                >
                    <i className="mgc_left_line text-base"></i>
                </button>

                {startPage > 1 && (
                    <span className="px-2 text-slate-400 font-medium">...</span>
                )}

                {pages.map((page) => (
                    <button
                        key={page}
                        onClick={() => onPageChange(page)}
                        className={`min-w-[38px] h-9 px-3 flex items-center justify-center rounded-xl text-sm font-semibold transition-all shadow-sm ${
                            currentPage === page
                                ? "bg-gradient-to-r from-blue-600 to-cyan-500 text-white border border-transparent shadow-md"
                                : "bg-white border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        }`}
                    >
                        {page}
                    </button>
                ))}

                {endPage < lastPage && (
                    <span className="px-2 text-slate-400 font-medium">...</span>
                )}

                {/* Página siguiente */}
                <button
                    onClick={() => onPageChange(currentPage + 1)}
                    disabled={currentPage === lastPage}
                    className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                    title="Siguiente"
                >
                    <i className="mgc_right_line text-base"></i>
                </button>

                {/* Última página */}
                <button
                    onClick={() => onPageChange(lastPage)}
                    disabled={currentPage === lastPage}
                    className="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                    title="Última página"
                >
                    <i className="mgc_transfer_line text-base"></i>
                </button>
            </div>
        </div>
    );
}
