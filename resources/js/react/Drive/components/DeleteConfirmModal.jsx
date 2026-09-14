// resources/js/react/Drive/components/DeleteConfirmModal.jsx
import React, { useEffect, useRef, useState } from "react";

/**
 * Confirmación de borrado del Drive.
 *  - Carpeta: se borra con todo su contenido y exige la contraseña del usuario.
 *  - Archivo: confirmación simple.
 */
export default function DeleteConfirmModal({
    elemento,
    borrando,
    error,
    onConfirm,
    onCancel,
}) {
    const [password, setPassword] = useState("");
    const inputRef = useRef(null);
    const botonRef = useRef(null);
    const esCarpeta = elemento?.tipo === "carpeta";

    useEffect(() => {
        setPassword("");

        if (!elemento) {
            return undefined;
        }

        const overflowPrevio = document.body.style.overflow;
        document.body.style.overflow = "hidden";

        const foco = setTimeout(() => {
            (esCarpeta ? inputRef.current : botonRef.current)?.focus();
        }, 0);

        return () => {
            clearTimeout(foco);
            document.body.style.overflow = overflowPrevio;
        };
    }, [elemento, esCarpeta]);

    if (!elemento) {
        return null;
    }

    const cancelar = () => {
        if (!borrando) {
            onCancel();
        }
    };

    const enviar = (e) => {
        e.preventDefault();
        if (borrando || (esCarpeta && !password)) {
            return;
        }
        onConfirm(password);
    };

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4 backdrop-blur-sm"
            onClick={cancelar}
        >
            <form
                onSubmit={enviar}
                onClick={(e) => e.stopPropagation()}
                onKeyDown={(e) => e.key === "Escape" && cancelar()}
                className="flex max-h-[90vh] w-full max-w-md flex-col rounded-3xl border border-slate-200 bg-white shadow-2xl"
            >
                <div className="flex items-center gap-4 border-b border-slate-200 px-6 py-5">
                    <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100">
                        <i className="mgc_delete_2_line text-xl text-rose-600"></i>
                    </div>
                    <div className="min-w-0">
                        <h3 className="text-lg font-semibold text-slate-900">
                            {esCarpeta ? "Eliminar carpeta" : "Eliminar archivo"}
                        </h3>
                        <p
                            className="truncate text-sm text-slate-500"
                            title={elemento.nombre}
                        >
                            {elemento.nombre}
                        </p>
                    </div>
                </div>

                <div className="space-y-4 overflow-y-auto overscroll-contain px-6 py-5">
                    {esCarpeta ? (
                        <>
                            <div className="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                                Se eliminará la carpeta{" "}
                                <strong>con todo su contenido</strong>{" "}
                                (subcarpetas y archivos). Esta acción no se puede
                                deshacer.
                            </div>

                            <div>
                                <label
                                    htmlFor="drive-borrar-password"
                                    className="mb-1.5 block text-sm font-semibold text-slate-700"
                                >
                                    Confirma con tu contraseña
                                </label>
                                <input
                                    id="drive-borrar-password"
                                    ref={inputRef}
                                    type="password"
                                    autoComplete="current-password"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    disabled={borrando}
                                    className="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm transition focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50"
                                />
                            </div>
                        </>
                    ) : (
                        <p className="text-sm text-slate-600">
                            ¿Seguro que quieres eliminar este archivo? Esta
                            acción no se puede deshacer.
                        </p>
                    )}

                    {error && (
                        <div className="flex items-start gap-2 rounded-2xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            <i className="mgc_warning_line mt-0.5 text-base"></i>
                            <span>{error}</span>
                        </div>
                    )}
                </div>

                <div className="flex flex-col-reverse gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        onClick={cancelar}
                        disabled={borrando}
                        className="w-full rounded-2xl border border-slate-300 bg-white px-5 py-2.5 font-medium text-slate-600 transition hover:bg-slate-100 disabled:opacity-50 sm:w-auto"
                    >
                        Cancelar
                    </button>
                    <button
                        ref={botonRef}
                        type="submit"
                        disabled={borrando || (esCarpeta && !password)}
                        className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-6 py-2.5 font-semibold text-white shadow-md transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                    >
                        {borrando && (
                            <span className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        )}
                        {borrando ? "Eliminando..." : "Eliminar"}
                    </button>
                </div>
            </form>
        </div>
    );
}
