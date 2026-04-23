import { useState, useEffect, useCallback } from "react";
import api from "../../../shared/api";

export default function useDetalle(certificacionId) {
    const [certificacion, setCertificacion] = useState(null);
    const [lineas, setLineas] = useState([]);
    const [presupuesto, setPresupuesto] = useState(null);
    const [partidasVenta, setPartidasVenta] = useState([]);
    const [eventos, setEventos] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const cargar = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const { data } = await api.get(
                `/certificaciones/${certificacionId}`,
            );
            setCertificacion(data.certificacion);
            setLineas(data.lineas ?? []);
            setPresupuesto(data.presupuesto ?? null);
            setPartidasVenta(data.partidas_venta ?? []);
            setEventos(data.eventos ?? []);
        } catch {
            setError("Error al cargar la certificación.");
        } finally {
            setLoading(false);
        }
    }, [certificacionId]);

    useEffect(() => {
        cargar();
    }, [cargar]);

    const actualizarEstado = (data) => {
        setCertificacion(data.certificacion);
        setLineas(data.lineas ?? []);
        setPresupuesto(data.presupuesto ?? null);
        if (data.partidas_venta) setPartidasVenta(data.partidas_venta);
        if (data.eventos) setEventos(data.eventos);
    };

    /**
     * Crea l\u00ednea.
     * Si el backend devuelve 409 con `exceso: true`, se lanza un error
     * especial que el componente puede atrapar para ofrecer "forzar".
     */
    const crearLinea = async (datos, { forzar = false } = {}) => {
        try {
            const { data } = await api.post(
                `/certificaciones/${certificacionId}/lineas`,
                { ...datos, forzar },
            );
            actualizarEstado(data);
            return data;
        } catch (err) {
            if (err.response?.status === 409 && err.response?.data?.exceso) {
                const e = new Error(err.response.data.message);
                e.exceso = true;
                e.pendiente = err.response.data.pendiente;
                e.cantidadIntentada = err.response.data.cantidad_intentada;
                throw e;
            }
            throw err;
        }
    };

    /**
     * Edita l\u00ednea existente. Mismo manejo de soft block que `crearLinea`:
     * si el backend devuelve 409, se lanza error con `exceso=true`.
     */
    const editarLinea = async (lineaId, datos, { forzar = false } = {}) => {
        try {
            const { data } = await api.put(
                `/certificaciones/${certificacionId}/lineas/${lineaId}`,
                { ...datos, forzar },
            );
            actualizarEstado(data);
            return data;
        } catch (err) {
            if (err.response?.status === 409 && err.response?.data?.exceso) {
                const e = new Error(err.response.data.message);
                e.exceso = true;
                e.pendiente = err.response.data.pendiente;
                e.cantidadIntentada = err.response.data.cantidad_intentada;
                throw e;
            }
            throw err;
        }
    };

    const eliminarLinea = async (lineaId) => {
        const { data } = await api.delete(
            `/certificaciones/${certificacionId}/lineas/${lineaId}`,
        );
        actualizarEstado(data);
        return data;
    };

    const actualizarImpuestos = async (datos) => {
        const { data } = await api.put(
            `/certificaciones/${certificacionId}/impuestos`,
            datos,
        );
        actualizarEstado(data);
        return data;
    };

    const aceptar = async () => {
        const { data } = await api.post(
            `/certificaciones/${certificacionId}/aceptar`,
        );
        actualizarEstado(data);
        return data;
    };

    const anular = async (motivo = null) => {
        const { data } = await api.post(
            `/certificaciones/${certificacionId}/anular`,
            { motivo },
        );
        actualizarEstado(data);
        return data;
    };

    /**
     * Descarga el PDF del informe de esta certificaci\u00f3n. Reusa el endpoint
     * gen\u00e9rico `informe-pdf` pasando solo el ID actual.
     */
    const descargarPdf = async () => {
        const response = await api.post(
            `/certificaciones/informe-pdf`,
            { certificacion_ids: [certificacionId] },
            { responseType: "blob" },
        );

        const blob = new Blob([response.data], { type: "application/pdf" });
        const numero = certificacion?.numero_certificacion ?? certificacionId;
        const filename = `informe_certificacion_${String(numero).replace(/[^a-z0-9-]/gi, "_")}.pdf`;

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    };

    return {
        certificacion,
        lineas,
        presupuesto,
        partidasVenta,
        eventos,
        loading,
        error,
        crearLinea,
        editarLinea,
        eliminarLinea,
        actualizarImpuestos,
        aceptar,
        anular,
        descargarPdf,
        recargar: cargar,
    };
}
