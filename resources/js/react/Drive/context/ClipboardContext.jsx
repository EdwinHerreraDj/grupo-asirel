// resources/js/react/Drive/context/ClipboardContext.jsx
import React, { createContext, useContext, useState } from 'react';

const ClipboardContext = createContext();

const MAX_SELECTION = 10;

export function ClipboardProvider({ children }) {
    const [clipboard, setClipboard] = useState({
        items: [],         // Array de items: [{ type: 'file'|'folder', id: number, name: string }]
        operation: 'cut'   // Siempre será 'cut' ahora
    });

    const cutItems = (items) => {
        // Permitir cortar archivos múltiples
        const files = items.filter(item => item.type === 'file');
        
        if (files.length === 0) {
            return { success: false, message: 'Debes seleccionar al menos un archivo' };
        }
        
        if (files.length > MAX_SELECTION) {
            return { success: false, message: `Solo puedes mover hasta ${MAX_SELECTION} archivos a la vez` };
        }

        setClipboard({
            items: files,
            operation: 'cut'
        });
        
        return { success: true, message: `${files.length} archivo(s) seleccionado(s) para mover` };
    };

    // Para carpetas individuales desde el menú
    const cutSingleFolder = (folder) => {
        setClipboard({
            items: [{ type: 'folder', id: folder.id, name: folder.nombre }],
            operation: 'cut'
        });
        
        return { success: true, message: `Carpeta "${folder.nombre}" lista para mover` };
    };

    const clearClipboard = () => {
        setClipboard({
            items: [],
            operation: 'cut'
        });
    };

    const hasClipboard = () => {
        return clipboard.items.length > 0;
    };

    const getClipboardCount = () => {
        return clipboard.items.length;
    };

    return (
        <ClipboardContext.Provider value={{
            clipboard,
            cutItems,
            cutSingleFolder,
            clearClipboard,
            hasClipboard,
            getClipboardCount,
            MAX_SELECTION
        }}>
            {children}
        </ClipboardContext.Provider>
    );
}

export function useClipboard() {
    const context = useContext(ClipboardContext);
    if (!context) {
        throw new Error('useClipboard must be used within ClipboardProvider');
    }
    return context;
}