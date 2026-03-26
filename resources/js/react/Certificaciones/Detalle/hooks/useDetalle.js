import { useState, useEffect, useCallback } from "react";
import api from "../../../shared/api";

export default function useDetalle(certificacionId) {
    const [certificacion, setCertificacion] = useState(null);
    const [lineas, setLineas] = useState([]);
    const [presupuesto, setPresupuesto] = useState(null);
    const [partidasVenta, setPartidasVenta] = useState([]);
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
    };

    // -------------------------
    // CREAR LÍNEA
    // -------------------------
    const crearLinea = async (datos) => {
        const { data } = await api.post(
            `/certificaciones/${certificacionId}/lineas`,
            datos,
        );
        actualizarEstado(data);
        return data;
    };

    // -------------------------
    // ELIMINAR LÍNEA
    // -------------------------
    const eliminarLinea = async (lineaId) => {
        const { data } = await api.delete(
            `/certificaciones/${certificacionId}/lineas/${lineaId}`,
        );
        actualizarEstado(data);
        return data;
    };

    // -------------------------
    // ACTUALIZAR IMPUESTOS
    // -------------------------
    const actualizarImpuestos = async (datos) => {
        const { data } = await api.put(
            `/certificaciones/${certificacionId}/impuestos`,
            datos,
        );
        actualizarEstado(data);
        return data;
    };

    // -------------------------
    // ACEPTAR
    // -------------------------
    const aceptar = async () => {
        const { data } = await api.post(
            `/certificaciones/${certificacionId}/aceptar`,
        );
        actualizarEstado(data);
        return data;
    };

    return {
        certificacion,
        lineas,
        presupuesto,
        partidasVenta,
        loading,
        error,
        crearLinea,
        eliminarLinea,
        actualizarImpuestos,
        aceptar,
        recargar: cargar,
    };
}
