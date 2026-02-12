import React from "react";
import FileItem from "./FileItem";

export default function FileGrid({
    files,
    onDelete,
    onDownload,
    onRename,
    selectedFiles,
    onSelectFile,
    onExtract,
    onPreview,
}) {
    return (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
            {files.map((file) => (
                <FileItem
                    key={file.id}
                    file={file}
                    onDelete={() => onDelete(file.id)}
                    onDownload={() => onDownload(file.id)}
                    onRename={(newName) => onRename(file.id, newName)}
                    onExtract={() => onExtract(file.id)}
                    isSelected={selectedFiles.includes(file.id)}
                    onSelect={() => onSelectFile(file.id)}
                    onPreview={() => onPreview(file)}
                />
            ))}
        </div>
    );
}
