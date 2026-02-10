// resources/js/react/Drive/components/PasteButton.jsx
import React from "react";
import { useClipboard } from "../context/ClipboardContext";

export default function PasteButton({ 
    currentFolderId, 
    onPasteItems
}) {
    const { clipboard, hasClipboard, getClipboardCount } = useClipboard();

    const handlePaste = async () => {
        if (!hasClipboard()) return;
        await onPasteItems(clipboard.items, currentFolderId);
    };

    if (!hasClipboard()) {
        return null;
    }

    const count = getClipboardCount();

    return (
        <button
            onClick={handlePaste}
            className="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors font-medium"
        >
            <i className="mgc_paste_line text-lg"></i>
            Mover aquí {count} {count === 1 ? 'item' : 'items'}
        </button>
    );
}