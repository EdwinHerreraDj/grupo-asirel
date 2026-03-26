// resources/js/react/shared/api.js
import axios from "axios";

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const api = axios.create({
    baseURL: "/api",
    withCredentials: true,
    headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
    },
});

// Interceptor para agregar CSRF token automáticamente
api.interceptors.request.use(
    (config) => {
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            config.headers["X-CSRF-TOKEN"] = token.content;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    },
);

// Interceptor para manejar errores globalmente (opcional)
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            console.error("No autenticado en API", error.response);
        }

        if (error.response?.status === 419) {
            console.error("Error CSRF / sesión expirada", error.response);
        }

        return Promise.reject(error);
    },
);

export default api;
