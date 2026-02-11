// resources/js/react/Drive/DriveApp.jsx
import React, { useState, useEffect } from "react";

import DriveLayout from "./DriveLayout";
import { ClipboardProvider, useClipboard } from "./context/ClipboardContext";
import {
    NotificationProvider,
    useNotification,
} from "./context/NotificationContext";
import api from "../shared/api";

function DriveAppContent() {
    const [currentFolderId, setCurrentFolderId] = useState(0);
    const [folders, setFolders] = useState([]);
    const [files, setFiles] = useState([]);
    const [breadcrumbs, setBreadcrumbs] = useState([
        { id: 0, nombre: "Inicio" },
    ]);
    const [loading, setLoading] = useState(false);
    const [selectedFiles, setSelectedFiles] = useState([]);
    const [extracting, setExtracting] = useState(false);

    const { showSuccess, showError, showWarning, showInfo } = useNotification();
    const { copyItems, cutItems, clipboard, clearClipboard, MAX_SELECTION } =
        useClipboard();

    const handleBack = () => {
        window.location.href = "/empresa";
    };

    const loadFolder = async (folderId = 0) => {
        setLoading(true);
        setSelectedFiles([]); // Limpiar selección al cambiar de carpeta
        try {
            const response = await api.get(`/folders/${folderId}/content`);
            setFolders(response.data.folders || []);
            setFiles(response.data.files || []);
            setBreadcrumbs(
                response.data.breadcrumbs || [{ id: 0, nombre: "Inicio" }],
            );
            setCurrentFolderId(folderId);
        } catch (error) {
            console.error("Error loading folder:", error);
            showError("Error al cargar la carpeta");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadFolder(0);
    }, []);

    const handleFolderClick = (folderId) => {
        loadFolder(folderId);
    };

    // Selección de archivos
    const handleSelectFile = (fileId) => {
        setSelectedFiles((prev) => {
            if (prev.includes(fileId)) {
                return prev.filter((id) => id !== fileId);
            } else {
                if (prev.length >= MAX_SELECTION) {
                    showWarning(
                        `Solo puedes seleccionar hasta ${MAX_SELECTION} archivos`,
                    );
                    return prev;
                }
                return [...prev, fileId];
            }
        });
    };

    const handleClearSelection = () => {
        setSelectedFiles([]);
    };

    const handleCutSelectedFiles = () => {
        const selectedFileObjects = files
            .filter((file) => selectedFiles.includes(file.id))
            .map((file) => ({ type: "file", id: file.id, name: file.nombre }));

        const result = cutItems(selectedFileObjects);

        if (result.success) {
            showSuccess(result.message);
        } else {
            showError(result.message);
        }
    };

    // Pegar items (archivos múltiples o carpeta individual)
    const handlePasteItems = async (items, targetFolderId) => {
        if (targetFolderId === 0) {
            showError("No se pueden mover archivos o carpetas a la raíz.");
            return;
        }

        let successCount = 0;
        let errorCount = 0;
        let renamedCount = 0;

        for (const item of items) {
            try {
                let response;

                if (item.type === "file") {
                    response = await api.post(`/files/${item.id}/move`, {
                        target_folder_id: targetFolderId,
                    });
                } else if (item.type === "folder") {
                    response = await api.post(`/folders/${item.id}/move`, {
                        target_folder_id: targetFolderId,
                    });
                }

                successCount++;

                // Detectar si fue renombrado
                if (response?.data?.was_renamed) {
                    renamedCount++;
                }
            } catch (error) {
                console.error(`Error moving ${item.type}:`, error);
                showError(
                    error.response?.data?.message ||
                        `Error al mover ${item.type}`,
                );
                errorCount++;
            }
        }

        // Mostrar notificaciones apropiadas
        if (successCount > 0) {
            if (renamedCount > 0) {
                showInfo(
                    `${successCount} item(s) movido(s). ${renamedCount} fue(ron) renombrado(s) automáticamente para evitar duplicados.`,
                );
            } else {
                showSuccess(`${successCount} item(s) movido(s) exitosamente`);
            }

            clearClipboard();
            setSelectedFiles([]);
        }

        if (errorCount > 0 && successCount === 0) {
            showError(`No se pudieron mover los items`);
        }

        loadFolder(currentFolderId);
    };

    const handleCreateFolder = async (nombre) => {
        try {
            await api.post("/folders", {
                nombre,
                parent_id: currentFolderId,
                tipo: "general",
            });
            showSuccess("Carpeta creada exitosamente");
            loadFolder(currentFolderId);
            return true;
        } catch (error) {
            console.error("Error creating folder:", error);
            showError(
                error.response?.data?.message || "Error al crear la carpeta",
            );
            return false;
        }
    };

    const handleFileUpload = async (file, hasExpiry, expiryDate) => {
        if (currentFolderId === 0) {
            showError("No se pueden subir archivos en la carpeta raíz.");
            return false;
        }
        const formData = new FormData();
        formData.append("file", file);
        formData.append("folder_id", currentFolderId);

        if (hasExpiry && expiryDate) {
            formData.append("tiene_caducidad", "1");
            formData.append("fecha_caducidad", expiryDate);
        } else {
            formData.append("tiene_caducidad", "0");
        }

        try {
            await api.post("/files", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            });

            return true;
        } catch (error) {
            console.error("Error uploading file:", error);
            showError(
                error.response?.data?.message || "Error al subir el archivo",
            );
            return false;
        }
    };

    const refreshCurrentFolder = () => {
        loadFolder(currentFolderId);
    };

    const handleDeleteFolder = async (folderId) => {
        try {
            await api.delete(`/folders/${folderId}`);
            showSuccess("Carpeta eliminada exitosamente");
            loadFolder(currentFolderId);
        } catch (error) {
            console.error("Error deleting folder:", error);
            showError(
                error.response?.data?.message || "Error al eliminar la carpeta",
            );
        }
    };

    const handleDeleteFile = async (fileId) => {
        try {
            await api.delete(`/files/${fileId}`);
            showSuccess("Archivo eliminado exitosamente");
            loadFolder(currentFolderId);
        } catch (error) {
            console.error("Error deleting file:", error);
            showError(
                error.response?.data?.message || "Error al eliminar el archivo",
            );
        }
    };

    const handleDownloadFile = async (fileId) => {
        try {
            const response = await api.get(`/files/${fileId}/download`, {
                responseType: "blob",
            });

            const contentDisposition = response.headers["content-disposition"];
            let fileName = "download";

            if (contentDisposition) {
                const fileNameMatch = contentDisposition.match(
                    /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/,
                );
                if (fileNameMatch && fileNameMatch[1]) {
                    fileName = fileNameMatch[1].replace(/['"]/g, "");
                }
            }

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            showSuccess("Archivo descargado exitosamente");
        } catch (error) {
            console.error("Error downloading file:", error);
            showError("Error al descargar el archivo");
        }
    };

    const handleRenameFolder = async (folderId, newName) => {
        try {
            await api.put(`/folders/${folderId}`, {
                nombre: newName,
            });
            showSuccess("Carpeta renombrada exitosamente");
            loadFolder(currentFolderId);
            return true;
        } catch (error) {
            console.error("Error renaming folder:", error);
            showError(
                error.response?.data?.message ||
                    "Error al renombrar la carpeta",
            );
            return false;
        }
    };

    const handleRenameFile = async (fileId, newName) => {
        try {
            await api.put(`/files/${fileId}`, {
                nombre: newName,
            });
            showSuccess("Archivo renombrado exitosamente");
            loadFolder(currentFolderId);
            return true;
        } catch (error) {
            console.error("Error renaming file:", error);
            showError(
                error.response?.data?.message ||
                    "Error al renombrar el archivo",
            );
            return false;
        }
    };

    const handleExtractFile = async (fileId) => {
        setExtracting(true);

        try {
            showInfo("Extrayendo archivo ZIP...");

            const response = await api.post(`/files/${fileId}/extract`);

            showSuccess(
                `ZIP extraído: ${response.data.stats.folders} carpetas y ${response.data.stats.files} archivos creados`,
            );

            // Recargar la carpeta actual para ver el contenido extraído
            loadFolder(currentFolderId);
        } catch (error) {
            console.error("Error extracting ZIP:", error);
            showError(
                error.response?.data?.message ||
                    "Error al extraer el archivo ZIP",
            );
        } finally {
            setExtracting(false);
        }
    };

    return (
        <DriveLayout
            onBack={handleBack}
            folders={folders}
            files={files}
            breadcrumbs={breadcrumbs}
            loading={loading}
            onFolderClick={handleFolderClick}
            onCreateFolder={handleCreateFolder}
            onFileUpload={{
                upload: handleFileUpload,
                refresh: refreshCurrentFolder,
            }}
            onDeleteFolder={handleDeleteFolder}
            onDeleteFile={handleDeleteFile}
            onDownloadFile={handleDownloadFile}
            onRenameFolder={handleRenameFolder}
            onRenameFile={handleRenameFile}
            onExtractFile={handleExtractFile}
            onPasteItems={handlePasteItems}
            currentFolderId={currentFolderId}
            selectedFiles={selectedFiles}
            onSelectFile={handleSelectFile}
            onClearSelection={handleClearSelection}
            onCutSelectedFiles={handleCutSelectedFiles}
        />
    );
}

export default function DriveApp() {
    return (
        <NotificationProvider>
            <ClipboardProvider>
                <DriveAppContent />
            </ClipboardProvider>
        </NotificationProvider>
    );
}
