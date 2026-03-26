import { useState, useEffect, useCallback } from "react";
import api from "../../shared/api";
import { totalPartidas } from "../utils/calculos";

export default function usePresupuesto(obraId) {
    // -------------------------
    // ESTADO
    // -------------------------
    const [modo, setModo] = useState("venta");
    const [capitulos, setCapitulos] = useState([]);
    const [capituloAbierto, setCapituloAbierto] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pendientesSincronizar, setPendientesSincronizar] = useState(false);

    // -------------------------
    // CARGA INICIAL
    // -------------------------
    const cargarDatos = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const endpoint =
                modo === "venta"
                    ? `/obras/${obraId}/presupuesto-venta`
                    : `/obras/${obraId}/gastos-iniciales`;

            const { data } = await api.get(endpoint);
            setCapitulos(data.capitulos ?? []);

            // Solo disponible en modo venta
            if (modo === "venta") {
                setPendientesSincronizar(data.pendientes_sincronizar ?? false);
            }
        } catch (err) {
            setError("Error al cargar los datos.");
        } finally {
            setLoading(false);
        }
    }, [obraId, modo]);

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
    // CREAR PARTIDA
    // -------------------------
    const crearPartida = async (oficioId, datos) => {
        const endpoint =
            modo === "venta"
                ? `/obras/${obraId}/presupuesto-venta/partidas`
                : `/obras/${obraId}/gastos-iniciales/partidas`;

        const { data } = await api.post(endpoint, {
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

    // -------------------------
    // EDITAR PARTIDA
    // -------------------------
    const editarPartida = async (partidaId, datos) => {
        const endpoint =
            modo === "venta"
                ? `/obras/${obraId}/presupuesto-venta/partidas/${partidaId}`
                : `/obras/${obraId}/gastos-iniciales/partidas/${partidaId}`;

        const { data } = await api.put(endpoint, datos);

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
    // ELIMINAR PARTIDA
    // -------------------------
    const eliminarPartida = async (partidaId, oficioId) => {
        const endpoint =
            modo === "venta"
                ? `/obras/${obraId}/presupuesto-venta/partidas/${partidaId}`
                : `/obras/${obraId}/gastos-iniciales/partidas/${partidaId}`;

        const { data } = await api.delete(endpoint);

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
    // CREAR CAPÍTULO
    // -------------------------
    const crearCapitulo = async (datos) => {
        const { data } = await api.post(`/obras/${obraId}/capitulos`, datos);
        setCapitulos((prev) => [...prev, data.capitulo]);
        return data;
    };

    // -------------------------
    // EDITAR CAPÍTULO
    // -------------------------
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

    // -------------------------
    // ELIMINAR CAPÍTULO
    // -------------------------
    const eliminarCapitulo = async (oficioId) => {
        await api.delete(`/obras/${obraId}/capitulos/${oficioId}`);

        setCapitulos((prev) =>
            prev.filter((cap) => cap.oficio_id !== oficioId),
        );

        if (capituloAbierto === oficioId) {
            setCapituloAbierto(null);
        }
    };

    // -------------------------
    // SINCRONIZAR desde coste
    // -------------------------
    const sincronizar = async () => {
        const { data } = await api.post(
            `/obras/${obraId}/presupuesto-venta/sincronizar`,
        );
        setCapitulos([...(data.capitulos ?? [])]);
        setPendientesSincronizar(data.pendientes_sincronizar ?? false);
        return data;
    };

    // -------------------------
    // INCREMENTAR por capítulo o global
    // -------------------------
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
    // TOTALES GLOBALES
    // -------------------------
    const totalGlobal = capitulos.reduce(
        (acc, cap) => acc + (parseFloat(cap.importe_total) || 0),
        0,
    );

    // -------------------------
    // RETURN
    // -------------------------
    return {
        modo,
        setModo,
        capitulos,
        capituloAbierto,
        toggleCapitulo,
        loading,
        error,
        totalGlobal,
        pendientesSincronizar,
        crearPartida,
        editarPartida,
        eliminarPartida,
        crearCapitulo,
        editarCapitulo,
        eliminarCapitulo,
        sincronizar,
        incrementar,
        recargar: cargarDatos,
        restablecer,
    };
}
