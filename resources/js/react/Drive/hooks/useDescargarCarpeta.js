// resources/js/react/Drive/hooks/useDescargarCarpeta.js
import { useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../context/NotificationContext";

/**
 * Descarga una carpeta del Drive como ZIP. Lo comparten la vista de
 * cuadrícula (FolderItem) y la de lista (DriveListView).
 */
export default function useDescargarCarpeta() {
    const [carpetaDescargando, setCarpetaDescargando] = useState(null);
    const { showSuccess, showError, showWarning } = useNotification();

    const descargarCarpeta = async (folder) => {
        setCarpetaDescargando(folder);

        try {
            const response = await api.get(`/folders/${folder.id}/download`, {
                responseType: "blob",
                timeout: 120000, // 2 minutos
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", `${folder.nombre}.zip`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            showSuccess(`Archivos de "${folder.nombre}" descargados exitosamente`);
        } catch (error) {
            console.error("Error downloading folder:", error);

            if (error.response?.status === 422) {
                showWarning(
                    error.response.data?.message ||
                        "Esta carpeta no contiene archivos",
                );
            } else if (error.code === "ECONNABORTED") {
                showError("La descarga tardó demasiado tiempo");
            } else {
                showError(
                    error.response?.data?.message ||
                        "Error al descargar la carpeta",
                );
            }
        } finally {
            setCarpetaDescargando(null);
        }
    };

    return { descargarCarpeta, carpetaDescargando };
}
