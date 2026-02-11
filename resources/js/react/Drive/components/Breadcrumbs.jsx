// resources/js/react/Drive/components/Breadcrumbs.jsx
import React from "react";

export default function Breadcrumbs({ items, onNavigate }) {
    return (
        <nav className="flex items-center flex-wrap gap-1 text-sm">
            <div
                className="
        flex items-center gap-1
        px-2 py-1.5
        bg-gray-50 dark:bg-gray-900
        border border-gray-200 dark:border-gray-800
        rounded-xl
    "
            >
                {items.map((item, index) => {
                    const isLast = index === items.length - 1;

                    return (
                        <React.Fragment key={item.id}>
                            {/* Separator */}
                            {index > 0 && (
                                <i
                                    className="
                            mgc_right_line 
                            text-gray-300 
                            dark:text-gray-700
                            text-xs
                        "
                                ></i>
                            )}

                            {/* Item */}
                            <button
                                onClick={() => !isLast && onNavigate(item.id)}
                                disabled={isLast}
                                className={`
                            flex items-center gap-1.5
                            px-2.5 py-1.5
                            rounded-lg
                            transition-all
                            duration-150
                            
                            ${
                                isLast
                                    ? `
                                        bg-white dark:bg-gray-800
                                        text-gray-900 dark:text-white
                                        font-semibold
                                        shadow-sm
                                        cursor-default
                                      `
                                    : `
                                        text-gray-500
                                        hover:text-gray-900
                                        dark:hover:text-gray-100
                                        hover:bg-white
                                        dark:hover:bg-gray-800
                                      `
                            }
                        `}
                            >
                                {index === 0 ? (
                                    <i className="mgc_home_3_line text-base"></i>
                                ) : (
                                    <span className="max-w-[160px] truncate">
                                        {item.nombre}
                                    </span>
                                )}
                            </button>
                        </React.Fragment>
                    );
                })}
            </div>
        </nav>
    );
}
