// resources/js/react/Drive/context/NotificationContext.jsx
import React, { createContext, useContext, useState } from "react";
import Toast from "../components/Toast";

const NotificationContext = createContext();

export function NotificationProvider({ children }) {
    const [notifications, setNotifications] = useState([]);

    const addNotification = (message, type = "success", duration = 3000) => {
        const id = Date.now();
        setNotifications((prev) => [...prev, { id, message, type, duration }]);
    };

    const removeNotification = (id) => {
        setNotifications((prev) => prev.filter((notif) => notif.id !== id));
    };

    const showSuccess = (message, duration) =>
        addNotification(message, "success", duration);
    const showError = (message, duration) =>
        addNotification(message, "error", duration);
    const showWarning = (message, duration) =>
        addNotification(message, "warning", duration);
    const showInfo = (message, duration) =>
        addNotification(message, "info", duration);

    return (
        <NotificationContext.Provider
            value={{
                showSuccess,
                showError,
                showWarning,
                showInfo,
            }}
        >
            {children}
            <div className="fixed bottom-4 right-4 z-50 space-y-2">
                {notifications.map((notif) => (
                    <Toast
                        key={notif.id}
                        message={notif.message}
                        type={notif.type}
                        duration={notif.duration}
                        onClose={() => removeNotification(notif.id)}
                    />
                ))}
            </div>
        </NotificationContext.Provider>
    );
}

export function useNotification() {
    const context = useContext(NotificationContext);
    if (!context) {
        throw new Error(
            "useNotification must be used within NotificationProvider",
        );
    }
    return context;
}
