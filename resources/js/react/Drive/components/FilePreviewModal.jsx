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
        <div className="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div className="bg-white w-[95vw] h-[90vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-200">
                {/* Header */}
                <div className="flex items-center justify-between px-8 py-5 border-b border-slate-200 bg-white/80 backdrop-blur">
                    <div className="flex items-center gap-3 min-w-0">
                        <div className="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <i className="mgc_file_line text-indigo-600 text-lg"></i>
                        </div>

                        <h2 className="text-sm md:text-base font-semibold text-slate-800 truncate">
                            {fileName}
                        </h2>
                    </div>

                    <button
                        onClick={onClose}
                        className="h-10 w-10 flex items-center justify-center rounded-2xl hover:bg-slate-100 transition"
                    >
                        <i className="mgc_close_line text-xl text-slate-600"></i>
                    </button>
                </div>

                {/* Body */}
                <div className="flex-1 bg-slate-100 flex items-center justify-center overflow-auto relative">
                    {isPdf && (
                        <iframe src={fileUrl} className="w-full h-full" />
                    )}

                    {isImage && (
                        <div className="w-full h-full flex items-center justify-center p-6">
                            <img
                                src={fileUrl}
                                alt={fileName}
                                className="max-w-full max-h-full object-contain rounded-xl shadow-lg"
                            />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
