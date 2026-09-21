import { useState } from "react";
import api from "../shared/api";
import { useNotification } from "../shared/NotificationContext";
import { erroresDeValidacion, mensajeDeError } from "./utils";

/**
 * Envío de formularios (multipart) con errores de validación por campo,
 * mensaje de éxito y aviso opcional del servidor (`aviso`).
 */
export default function useEnvio() {
    const { showSuccess, showError, showWarning } = useNotification();
    const [errores, setErrores] = useState({});
    const [enviando, setEnviando] = useState(false);

    const enviar = async (url, formData, porDefecto = "No se pudo guardar.") => {
        setEnviando(true);
        setErrores({});
        try {
            const { data } = await api.post(url, formData, { headers: { "Content-Type": "multipart/form-data" } });
            if (data.message) showSuccess(data.message);
            if (data.aviso) showWarning(data.aviso, 9000);
            return data;
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            showError(Object.keys(campos).length ? "Revisa los campos marcados en rojo." : mensajeDeError(error, porDefecto));
            return null;
        } finally {
            setEnviando(false);
        }
    };

    const limpiarError = (campo) => setErrores((p) => (p[campo] ? { ...p, [campo]: null } : p));

    return { errores, setErrores, enviando, enviar, limpiarError };
}
