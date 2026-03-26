import React, { useState, useEffect } from "react";
import api from "../../shared/api";

export default function ModalPdf({ obraId, onCancelar }) {
    const [clientes, setClientes] = useState([]);
    const [clienteId, setClienteId] = useState("");
    const [loading, setLoading] = useState(true);
    const [descargando, setDescargando] = useState(false);
    
    useEffect(() => {
        api.get("/clientes")
            .then(({ data }) => {
                const lista = data.data ?? data.clientes ?? [];
                setClientes(lista);
                setLoading(false);
            })
            .catch((err) => {
                console.error("Error:", err);
                setLoading(false);
            });
    }, []);


    const handleDescargar = async () => {
        if (!clienteId) return;
        setDescargando(true);
        try {
            const response = await api.get(
                `/obras/${obraId}/presupuesto-venta/pdf`,
                {
                    params: { cliente_id: clienteId },
                    responseType: "blob",
                },
            );

            // Crear enlace de descarga
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", `Presupuesto_${obraId}.pdf`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.URL.revokeObjectURL(url);

            onCancelar();
        } catch (err) {
            console.error("Error al generar PDF", err);
        } finally {
            setDescargando(false);
        }
    };

    return (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">
                <h3 className="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                    <span className="w-1.5 h-5 bg-primary rounded"></span>
                    Generar presupuesto PDF
                </h3>

                <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                        Seleccionar cliente{" "}
                        <span className="text-red-500">*</span>
                    </label>

                    {loading ? (
                        <p className="text-sm text-gray-400">
                            Cargando clientes...
                        </p>
                    ) : (
                        <select
                            value={clienteId}
                            onChange={(e) => setClienteId(e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">— Selecciona un cliente —</option>
                            {clientes.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.nombre}
                                </option>
                            ))}
                        </select>
                    )}
                </div>

                <div className="flex justify-end gap-2">
                    <button
                        onClick={onCancelar}
                        disabled={descargando}
                        className="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition text-sm disabled:opacity-50"
                    >
                        Cancelar
                    </button>
                    <button
                        onClick={handleDescargar}
                        disabled={!clienteId || descargando}
                        className="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 shadow text-sm disabled:opacity-50 flex items-center gap-2"
                    >
                        <i className="mgc_download_line"></i>
                        {descargando ? "Generando..." : "Descargar PDF"}
                    </button>
                </div>
            </div>
        </div>
    );
}
