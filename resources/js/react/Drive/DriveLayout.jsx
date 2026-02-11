import React, { useState } from "react";
import FolderGrid from "./components/FolderGrid";
import FileGrid from "./components/FileGrid";
import Breadcrumbs from "./components/Breadcrumbs";
import CreateFolderModal from "./components/CreateFolderModal";
import UploadButton from "./components/UploadButton";
import PasteButton from "./components/PasteButton";
import SelectionBar from "./components/SelectionBar";
import ExpiringFilesModal from "./components/ExpiringFilesModal";
import FilePreviewModal from "./components/FilePreviewModal";
import SearchBar from "./components/SearchBar";

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
    onExtractFile,
    onPasteItems,
    currentFolderId,
    selectedFiles,
    onSelectFile,
    onClearSelection,
    onCutSelectedFiles,
    onOpenExpiringModal,
    onCloseExpiringModal,
    showExpiringModal,
    expiringFiles,
    loadingExpiring,
    previewFile,
    onPreviewFile,
    onClosePreview,
    onSearch,
    onClearSearch,
    searchResults,
    isSearching,
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
                    <div className="mb-8">
                        {/* BACK */}
                        <button
                            onClick={onBack}
                            className="
                              flex items-center gap-2 
                              text-sm font-medium 
                              text-gray-500 hover:text-gray-900 
                              transition-colors mb-4
                          "
                        >
                            <i className="mgc_arrow_left_line text-lg"></i>
                            Volver
                        </button>

                        {/* HEADER CARD */}
                        <div
                            className="
                           bg-white 
                           border border-gray-200 
                           rounded-2xl 
                           shadow-sm 
                           px-6 py-5
                       "
                        >
                            <div className="flex items-center justify-between">
                                {/* LEFT */}
                                <div>
                                    <h1 className="text-2xl font-semibold text-gray-900 tracking-tight">
                                        Drive Empresarial
                                    </h1>

                                    <p className="text-gray-500 mt-1 text-sm">
                                        Centraliza la documentación operativa de
                                        la empresa, controla caducidades y
                                        mantén el acceso organizado.
                                    </p>
                                </div>

                                {/* ACTIONS */}
                                <div className="flex items-center gap-3">
                                    {/* ALERT */}
                                    <button
                                        onClick={onOpenExpiringModal}
                                        className="
                        inline-flex items-center gap-2
                        px-4 py-2.5
                        rounded-xl
                        bg-red-50
                        text-red-700
                        border border-red-100
                        hover:bg-red-100
                        transition
                        font-medium
                    "
                                    >
                                        <i className="mgc_alert_line text-lg"></i>
                                        Caducidades
                                    </button>

                                    {/* CREATE */}
                                    <button
                                        onClick={() => setShowCreateModal(true)}
                                        className="
                                        inline-flex items-center gap-2
                                        px-4 py-2.5
                                        rounded-xl
                                        bg-indigo-600
                                        text-white
                                        hover:bg-indigo-500
                                        transition
                                        font-medium
                                        shadow-sm
                                        "
                                    >
                                        <i className="mgc_folder_2_line text-lg"></i>
                                        Nueva carpeta
                                    </button>

                                    <UploadButton onUpload={onFileUpload} />

                                    <PasteButton
                                        currentFolderId={currentFolderId}
                                        onPasteItems={onPasteItems}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Barra de búsqueda */}
                    <div className="mb-5 flex justify-end">
                        <SearchBar
                            onSearch={onSearch}
                            onClear={onClearSearch}
                        />
                    </div>

                    {/* Breadcrumbs */}
                    {!isSearching && (
                        <Breadcrumbs
                            items={breadcrumbs}
                            onNavigate={onFolderClick}
                        />
                    )}

                    {/* CONTENIDO DRIVE */}
                    <div className="mt-6">
                        {loading ? (
                            <div className="text-center py-12">
                                <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                <p className="text-gray-500 mt-4">
                                    Cargando...
                                </p>
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
                                            onExtract={onExtractFile}
                                            selectedFiles={selectedFiles}
                                            onSelectFile={onSelectFile}
                                            onPreview={onPreviewFile}
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
                                            Crea una carpeta o sube archivos
                                            para comenzar
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

            {showExpiringModal && (
                <ExpiringFilesModal
                    show={showExpiringModal}
                    onClose={onCloseExpiringModal}
                    files={expiringFiles}
                    loading={loadingExpiring}
                />
            )}

            <FilePreviewModal
                show={!!previewFile}
                onClose={onClosePreview}
                fileUrl={previewFile?.url}
                fileName={previewFile?.name}
                fileType={previewFile?.type}
            />
        </div>
    );
}
