// resources/js/react/Drive/components/UploadButton.jsx
import React, { useRef, useState } from "react";

export default function UploadButton({ onUpload }) {
    const fileInputRef = useRef(null);
    const [uploading, setUploading] = useState(false);

    const handleFileSelect = async (e) => {
        const files = Array.from(e.target.files);

        if (files.length === 0) return;

        setUploading(true);

        for (const file of files) {
            try {
                await onUpload(file);
            } catch (error) {
                console.error(`Error uploading ${file.name}:`, error);
            }
        }

        setUploading(false);

        // Reset input
        if (fileInputRef.current) {
            fileInputRef.current.value = "";
        }
    };

    return (
        <>
            <input
                ref={fileInputRef}
                type="file"
                multiple
                onChange={handleFileSelect}
                className="hidden"
            />
            <button
                onClick={() => fileInputRef.current?.click()}
                disabled={uploading}
                className="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white 
                         rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 
                         disabled:cursor-not-allowed"
            >
                {uploading ? (
                    <>
                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                        Subiendo...
                    </>
                ) : (
                    <>
                        <i className="mgc_upload_line text-lg"></i>
                        Subir Archivos
                    </>
                )}
            </button>
        </>
    );
}
