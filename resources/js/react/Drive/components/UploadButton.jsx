import React, { useState } from "react";
import UploadModal from "./UploadModal";

export default function UploadButton({ onUpload }) {
    const [showModal, setShowModal] = useState(false);

    const handleModalClose = (shouldRefresh) => {
        setShowModal(false);
        // Si shouldRefresh es true, significa que se subieron archivos
        if (shouldRefresh && onUpload.refresh) {
            onUpload.refresh();
        }
    };

    return (
        <>
            <button
                onClick={() => setShowModal(true)}
                className="
            inline-flex items-center gap-2
            px-4 py-2.5
            rounded-2xl
            bg-white
            border border-slate-300
            text-slate-700
            font-semibold
            shadow-sm
            hover:bg-slate-50
            hover:border-slate-400
            hover:shadow
            active:scale-[0.98]
            transition-all duration-200
        "
            >
                <i className="mgc_upload_line text-lg text-indigo-600"></i>
                Subir archivos
            </button>

            <UploadModal
                isOpen={showModal}
                onClose={handleModalClose}
                onUpload={onUpload.upload}
            />
        </>
    );
}
