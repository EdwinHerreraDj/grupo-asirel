import React from "react";
export default function FilePreviewModal({
    show,
    onClose,
    fileUrl,
    fileName,
    fileType,
}) {
    if (!show) return null;

    const isImage = ["jpg", "jpeg", "png", "gif", "webp"].includes(fileType);
    const isPdf = fileType === "pdf";

    return (
        <div className="fixed inset-0 z-50 bg-black/70 flex items-center justify-center">
            <div className="bg-white dark:bg-gray-900 w-[95vw] h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">

                {/* Header */}
                <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h2 className="text-sm font-semibold truncate">
                        {fileName}
                    </h2>

                    <button
                        onClick={onClose}
                        className="h-9 w-9 flex items-center justify-center rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        ✕
                    </button>
                </div>

                {/* Body */}
                <div className="flex-1 bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-auto">

                    {isPdf && (
                        <iframe
                            src={fileUrl}
                            className="w-full h-full"
                        />
                    )}

                    {isImage && (
                        <img
                            src={fileUrl}
                            alt={fileName}
                            className="max-w-full max-h-full object-contain"
                        />
                    )}
                </div>
            </div>
        </div>
    );
}
