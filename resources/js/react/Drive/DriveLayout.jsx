// resources/js/react/Drive/DriveLayout.jsx
import React, { useState } from "react";
import FolderGrid from "./components/FolderGrid";
import FileGrid from "./components/FileGrid";
import Breadcrumbs from "./components/Breadcrumbs";
import CreateFolderModal from "./components/CreateFolderModal";
import UploadButton from "./components/UploadButton";
import PasteButton from "./components/PasteButton";
import SelectionBar from "./components/SelectionBar";

export default function DriveLayout({ 
    onBack, 
    folders, 
    files, 
    breadcrumbs,
    loading,
    onFolderClick,
    onCreateFolder,
    onFileUpload,
    onDeleteFolder,
    onDeleteFile,
    onDownloadFile,
    onRenameFolder,
    onRenameFile,
    onPasteItems,
    currentFolderId,
    selectedFiles,
    onSelectFile,
    onClearSelection,
    onCutSelectedFiles
}) {
    const [showCreateModal, setShowCreateModal] = useState(false);

    const handleCreateFolderSubmit = async (nombre) => {
        const success = await onCreateFolder(nombre);
        if (success) {
            setShowCreateModal(false);
        }
    };

    return (
        <div className="grid grid-cols-12">
            <div className="col-span-12">
                <div className="card p-10">
                    {/* Header */}
                    <div>
                        <button
                            onClick={onBack}
                            className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium shadow-sm border border-gray-200 hover:bg-gray-200 hover:text-gray-900 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/30 mb-7"
                        >
                            <i className="mgc_arrow_left_line text-lg"></i>
                            Regresar
                        </button>
                        <hr className="mb-3" />
                        <div className="mb-5 flex items-center justify-between">
                            <div>
                                <h2 className="text-2xl font-semibold text-gray-800 dark:text-white mb-1">
                                    Drive App
                                </h2>
                                <p className="text-gray-600 dark:text-gray-400">
                                    Gestiona las carpetas y archivos de tu empresa aquí.
                                </p>
                            </div>
                            <div className="flex gap-3">
                                <button
                                    onClick={() => setShowCreateModal(true)}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <i className="mgc_folder_add_line text-lg"></i>
                                    Nueva Carpeta
                                </button>
                                <UploadButton onUpload={onFileUpload} />
                                <PasteButton 
                                    currentFolderId={currentFolderId}
                                    onPasteItems={onPasteItems}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Breadcrumbs */}
                    <Breadcrumbs items={breadcrumbs} onNavigate={onFolderClick} />

                    {/* CONTENIDO DRIVE */}
                    <div className="mt-6">
                        {loading ? (
                            <div className="text-center py-12">
                                <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                <p className="text-gray-500 mt-4">Cargando...</p>
                            </div>
                        ) : (
                            <>
                                {/* Carpetas */}
                                {folders.length > 0 && (
                                    <div className="mb-8">
                                        <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                                            <i className="mgc_folder_line text-xl"></i>
                                            Carpetas
                                        </h3>
                                        <FolderGrid 
                                            folders={folders} 
                                            onFolderClick={onFolderClick}
                                            onDelete={onDeleteFolder}
                                            onRename={onRenameFolder}
                                        />
                                    </div>
                                )}

                                {/* Archivos */}
                                {files.length > 0 && (
                                    <div>
                                        <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                                            <i className="mgc_file_line text-xl"></i>
                                            Archivos
                                        </h3>
                                        <FileGrid 
                                            files={files}
                                            onDelete={onDeleteFile}
                                            onDownload={onDownloadFile}
                                            onRename={onRenameFile}
                                            selectedFiles={selectedFiles}
                                            onSelectFile={onSelectFile}
                                        />
                                    </div>
                                )}

                                {/* Empty State */}
                                {folders.length === 0 && files.length === 0 && (
                                    <div className="text-center py-16">
                                        <i className="mgc_folder_open_line text-7xl text-gray-300 mb-4 block"></i>
                                        <p className="text-gray-500 text-lg mb-2">
                                            Esta carpeta está vacía
                                        </p>
                                        <p className="text-gray-400 text-sm">
                                            Crea una carpeta o sube archivos para comenzar
                                        </p>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                </div>
            </div>

            {/* Selection Bar */}
            <SelectionBar
                selectedFiles={selectedFiles}
                onClearSelection={onClearSelection}
                onCut={onCutSelectedFiles}
            />

            {/* Modals */}
            {showCreateModal && (
                <CreateFolderModal
                    onClose={() => setShowCreateModal(false)}
                    onSubmit={handleCreateFolderSubmit}
                />
            )}
        </div>
    );
}