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
        bg-white
        text-gray-800
        border border-gray-300
        rounded-xl
        hover:bg-gray-50
        transition
        font-medium
    "
            >
                <i className="mgc_upload_line text-lg"></i>
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
