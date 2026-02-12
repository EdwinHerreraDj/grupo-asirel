// resources/js/react/Drive/components/FolderGrid.jsx
import React from "react";
import FolderItem from "./FolderItem";

export default function FolderGrid({
    folders,
    onFolderClick,
    onDelete,
    onRename,
}) {
    return (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
            {folders.map((folder) => (
                <FolderItem
                    key={folder.id}
                    folder={folder}
                    onClick={() => onFolderClick(folder.id)}
                    onDelete={() => onDelete(folder.id)}
                    onRename={(newName) => onRename(folder.id, newName)}
                />
            ))}
        </div>
    );
}
