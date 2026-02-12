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
                <div className="bg-gradient-to-br from-slate-50 to-white border border-slate-200 rounded-3xl shadow-sm p-6 md:p-10">
                    {/* Header */}
                    <div className="mb-10">
                        {/* BACK */}
                        <button
                            onClick={onBack}
                            className="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors mb-6"
                        >
                            <i className="mgc_arrow_left_line text-lg"></i>
                            Volver
                        </button>

                        {/* HEADER CARD */}
                        <div className="bg-white border border-slate-200 rounded-3xl shadow-sm px-6 py-6">
                            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                {/* LEFT */}
                                <div>
                                    <h1 className="text-3xl font-bold text-slate-900 tracking-tight">
                                        Drive Empresarial
                                    </h1>

                                    <p className="text-slate-500 mt-2 text-sm max-w-2xl">
                                        Centraliza la documentación operativa de
                                        la empresa, controla caducidades y
                                        mantén el acceso organizado.
                                    </p>

                                    <div className="mt-4 h-1 w-20 bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-full"></div>
                                </div>

                                {/* ACTIONS */}
                                <div className="flex flex-wrap items-center gap-3">
                                    {/* ALERT */}
                                    <button
                                        onClick={onOpenExpiringModal}
                                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-rose-50 text-rose-700 border border-rose-100 hover:bg-rose-100 transition font-semibold"
                                    >
                                        <i className="mgc_alert_line text-lg"></i>
                                        Caducidades
                                    </button>

                                    {/* CREATE */}
                                    <button
                                        onClick={() => setShowCreateModal(true)}
                                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white hover:shadow-md hover:scale-[1.02] active:scale-[0.98] transition-all font-semibold"
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
                    <div className="mb-6 flex justify-end">
                        <SearchBar
                            onSearch={onSearch}
                            onClear={onClearSearch}
                        />
                    </div>

                    {/* Breadcrumbs */}
                    {!isSearching && (
                        <div className="mb-6">
                            <Breadcrumbs
                                items={breadcrumbs}
                                onNavigate={onFolderClick}
                            />
                        </div>
                    )}

                    {/* CONTENIDO DRIVE */}
                    <div className="mt-6">
                        {loading ? (
                            <div className="text-center py-16">
                                <div className="inline-block animate-spin rounded-full h-12 w-12 border-4 border-slate-200 border-t-indigo-600"></div>
                                <p className="text-slate-500 mt-4 font-medium">
                                    Cargando...
                                </p>
                            </div>
                        ) : (
                            <>
                                {/* Carpetas */}
                                {folders.length > 0 && (
                                    <div className="mb-10">
                                        <h3 className="text-lg font-semibold text-slate-700 mb-5 flex items-center gap-2">
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
                                        <h3 className="text-lg font-semibold text-slate-700 mb-5 flex items-center gap-2">
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
                                    <div className="text-center py-20">
                                        <div className="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-6">
                                            <i className="mgc_folder_open_line text-5xl text-slate-400"></i>
                                        </div>
                                        <p className="text-slate-600 text-lg font-semibold mb-2">
                                            Esta carpeta está vacía
                                        </p>
                                        <p className="text-slate-400 text-sm">
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
