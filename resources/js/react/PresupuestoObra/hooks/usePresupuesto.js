import { useState, useEffect, useCallback } from "react";
import api from "../../shared/api";
import { totalPartidas } from "../utils/calculos";

/**
 * Hook del módulo presupuesto.
 * Acepta `modo` fijo ("venta" | "coste"). No expone toggle.
 */
export default function usePresupuesto(obraId, modo = "venta") {
    const [capitulos, setCapitulos] = useState([]);
    const [capituloAbierto, setCapituloAbierto] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pendientesSincronizar, setPendientesSincronizar] = useState(false);
    const [indicador, setIndicador] = useState(null);

    const endpointListado =
        modo === "venta"
            ? `/obras/${obraId}/presupuesto-venta`
            : `/obras/${obraId}/gastos-iniciales`;

    const endpointPartidas =
        modo === "venta"
            ? `/obras/${obraId}/presupuesto-venta/partidas`
            : `/obras/${obraId}/gastos-iniciales/partidas`;

    // -------------------------
    // CARGA
    // -------------------------
    const cargarDatos = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const { data } = await api.get(endpointListado);
            setCapitulos(data.capitulos ?? []);
            setIndicador(data.indicador ?? null);
            if (modo === "venta") {
                setPendientesSincronizar(data.pendientes_sincronizar ?? false);
            }
        } catch (err) {
            setError("Error al cargar los datos.");
        } finally {
            setLoading(false);
        }
    }, [endpointListado, modo]);

    useEffect(() => {
        cargarDatos();
    }, [cargarDatos]);

    // -------------------------
    // TOGGLE CAPÍTULO
    // -------------------------
    const toggleCapitulo = (oficioId) => {
        setCapituloAbierto((prev) => (prev === oficioId ? null : oficioId));
    };

    // -------------------------
    // CRUD PARTIDAS
    // -------------------------
    const crearPartida = async (oficioId, datos) => {
        const { data } = await api.post(endpointPartidas, {
            ...datos,
            oficio_id: oficioId,
        });
        setCapitulos((prev) =>
            prev.map((cap) => {
                if (cap.oficio_id !== oficioId) return cap;
                return {
                    ...cap,
                    id: data.capitulo_id,
                    partidas: data.partidas,
                    importe_total: totalPartidas(data.partidas),
                };
            }),
        );
        return data;
    };

    const editarPartida = async (partidaId, datos) => {
        const { data } = await api.put(`${endpointPartidas}/${partidaId}`, datos);
        setCapitulos((prev) =>
            prev.map((cap) => {
                if (cap.oficio_id !== data.oficio_id) return cap;
                return {
                    ...cap,
                    partidas: data.partidas,
                    importe_total: totalPartidas(data.partidas),
                };
            }),
        );
        return data;
    };

    const eliminarPartida = async (partidaId, oficioId) => {
        const { data } = await api.delete(`${endpointPartidas}/${partidaId}`);
        setCapitulos((prev) =>
            prev.map((cap) => {
                if (cap.oficio_id !== data.oficio_id) return cap;
                return {
                    ...cap,
                    partidas: data.partidas,
                    importe_total: totalPartidas(data.partidas),
                };
            }),
        );
        return data;
    };

    // -------------------------
    // CRUD CAPÍTULOS (oficios)
    // -------------------------
    const crearCapitulo = async (datos) => {
        const { data } = await api.post(`/obras/${obraId}/capitulos`, datos);
        setCapitulos((prev) => [...prev, data.capitulo]);
        return data;
    };

    const editarCapitulo = async (oficioId, datos) => {
        const { data } = await api.put(
            `/obras/${obraId}/capitulos/${oficioId}`,
            datos,
        );
        setCapitulos((prev) =>
            prev.map((cap) => {
                if (cap.oficio_id !== oficioId) return cap;
                return {
                    ...cap,
                    oficio_nombre: data.capitulo.oficio_nombre,
                };
            }),
        );
        return data;
    };

    const eliminarCapitulo = async (oficioId) => {
        await api.delete(`/obras/${obraId}/capitulos/${oficioId}`);
        setCapitulos((prev) => prev.filter((cap) => cap.oficio_id !== oficioId));
        if (capituloAbierto === oficioId) setCapituloAbierto(null);
    };

    // -------------------------
    // ACCIONES SOLO EN VENTA
    // -------------------------
    const sincronizar = async () => {
        const { data } = await api.post(
            `/obras/${obraId}/presupuesto-venta/sincronizar`,
        );
        setCapitulos([...(data.capitulos ?? [])]);
        setPendientesSincronizar(data.pendientes_sincronizar ?? false);
        return data;
    };

    const incrementar = async (porcentaje, oficioId = null) => {
        const payload = { porcentaje };
        if (oficioId) payload.oficio_id = oficioId;
        const { data } = await api.post(
            `/obras/${obraId}/presupuesto-venta/incrementar`,
            payload,
        );
        setCapitulos([...(data.capitulos ?? [])]);
        return data;
    };

    const restablecer = async (oficioId = null) => {
        const payload = {};
        if (oficioId) payload.oficio_id = oficioId;
        const { data } = await api.post(
            `/obras/${obraId}/presupuesto-venta/restablecer`,
            payload,
        );
        setCapitulos([...(data.capitulos ?? [])]);
        return data;
    };

    // -------------------------
    // TOTALES DERIVADOS
    // -------------------------
    const totalVenta = capitulos.reduce(
        (acc, cap) => acc + (parseFloat(cap.importe_total) || 0),
        0,
    );
    const totalCoste = capitulos.reduce(
        (acc, cap) => acc + (parseFloat(cap.coste_total) || 0),
        0,
    );
    const margenImporte = totalVenta - totalCoste;
    const margenPorcentaje =
        totalVenta > 0 ? (margenImporte / totalVenta) * 100 : null;

    // En modo coste, el "total global" que se muestra es el coste.
    // En modo venta, el "total global" principal es la venta.
    const totalGlobal = modo === "venta" ? totalVenta : totalCoste;

    return {
        modo,
        capitulos,
        capituloAbierto,
        toggleCapitulo,
        loading,
        error,
        totalGlobal,
        totalVenta,
        totalCoste,
        margenImporte,
        margenPorcentaje,
        pendientesSincronizar,
        indicador,
        crearPartida,
        editarPartida,
        eliminarPartida,
        crearCapitulo,
        editarCapitulo,
        eliminarCapitulo,
        sincronizar,
        incrementar,
        restablecer,
        recargar: cargarDatos,
    };
}
