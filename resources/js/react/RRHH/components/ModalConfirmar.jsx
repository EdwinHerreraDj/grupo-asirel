import React, { useState } from "react";
import { Modal } from "./Comunes";
import { botonPeligro, botonPrimario, botonSecundario } from "../utils";

/** Confirmación propia (nunca confirm() del navegador). */
export default function ModalConfirmar({ titulo, children, textoConfirmar = "Confirmar", peligro = false, onConfirmar, onCerrar }) {
    const [enviando, setEnviando] = useState(false);

    const confirmar = async () => {
        setEnviando(true);
        try {
            await onConfirmar();
        } finally {
            setEnviando(false);
        }
    };

    return (
        <Modal
            titulo={titulo}
            onCerrar={enviando ? () => {} : onCerrar}
            ancho="max-w-md"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="button" onClick={confirmar} className={peligro ? botonPeligro : botonPrimario} disabled={enviando}>
                        {enviando && <i className="mgc_loading_line animate-spin"></i>}
                        {textoConfirmar}
                    </button>
                </>
            }
        >
            <div className="text-sm text-slate-600">{children}</div>
        </Modal>
    );
}
