import React, { useEffect } from 'react';

export default function Toast({ message, type = 'success', onClose, duration = 4000 }) {
    useEffect(() => {
        if (duration > 0) {
            const timer = setTimeout(onClose, duration);
            return () => clearTimeout(timer);
        }
    }, [duration, onClose]);

    const icons = {
        success: 'mgc_check_circle_fill',
        error: 'mgc_close_circle_fill',
        warning: 'mgc_alert_fill',  // ✅ Asegurar que existe
        info: 'mgc_information_fill'
    };

    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',  // ✅ Asegurar que existe
        info: 'bg-blue-500'
    };

    return (
        <div className="fixed bottom-4 right-4 z-50 animate-slide-up">
            <div className={`${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg flex items-start gap-3 min-w-[320px] max-w-lg`}>
                <i className={`${icons[type]} text-2xl flex-shrink-0 mt-0.5`}></i>
                <p className="flex-1 text-sm font-medium leading-relaxed">{message}</p>
                <button
                    onClick={onClose}
                    className="flex-shrink-0 hover:bg-white/20 rounded p-1 transition-colors"
                >
                    <i className="mgc_close_line text-lg"></i>
                </button>
            </div>
        </div>
    );
}