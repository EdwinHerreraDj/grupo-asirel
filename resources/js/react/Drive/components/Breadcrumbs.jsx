// resources/js/react/Drive/components/Breadcrumbs.jsx
import React from "react";

export default function Breadcrumbs({ items, onNavigate }) {
    return (
        <nav className="flex items-center gap-2 text-sm">
            {items.map((item, index) => (
                <React.Fragment key={item.id}>
                    {index > 0 && (
                        <i className="mgc_right_line text-gray-400"></i>
                    )}
                    <button
                        onClick={() => onNavigate(item.id)}
                        className={`
                            px-3 py-1 rounded-md transition-colors
                            ${index === items.length - 1
                                ? 'bg-blue-100 text-blue-700 font-medium cursor-default'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                            }
                        `}
                        disabled={index === items.length - 1}
                    >
                        {index === 0 ? (
                            <i className="mgc_home_3_line text-lg"></i>
                        ) : (
                            item.nombre
                        )}
                    </button>
                </React.Fragment>
            ))}
        </nav>
    );
}