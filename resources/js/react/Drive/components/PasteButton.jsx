// resources/js/react/Drive/components/PasteButton.jsx
import React from "react";
import { useClipboard } from "../context/ClipboardContext";

export default function PasteButton({ currentFolderId, onPasteItems }) {
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
            className="
        inline-flex items-center gap-2
        px-5 py-2.5
        rounded-2xl
        bg-gradient-to-r from-amber-500 to-orange-600
        text-white
        font-semibold
        shadow-md
        hover:shadow-lg
        hover:scale-[1.02]
        active:scale-[0.98]
        transition-all duration-200
    "
        >
            <i className="mgc_paste_line text-lg"></i>
            Mover aquí {count} {count === 1 ? "item" : "items"}
        </button>
    );
}
