import { useState, useEffect, useCallback } from "react";
import api from "../../shared/api";

export default function useCertificaciones(obraId) {
    const [certificaciones, setCertificaciones] = useState([]);
    const [oficios, setOficios] = useState([]);
    const [clientes, setClientes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Paginación
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);

    // Filtros pendientes (el usuario escribe)
    const [pending, setPending] = useState({
        search: "",
        oficio_id: "",
        cliente_id: "",
        estado_certificacion: "",
        fecha_desde: "",
        fecha_hasta: "",
    });

    // Filtros aplicados (se usan en la query)
    const [filtros, setFiltros] = useState({ ...pending });

    const cargarDatos = useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const params = { page, ...filtros };
            // Eliminar vacíos
            Object.keys(params).forEach((k) => !params[k] && delete params[k]);

            const { data } = await api.get(`/obras/${obraId}/certificaciones`, {
                params,
            });

            setCertificaciones(data.data ?? []);
            setOficios(data.oficios ?? []);
            setClientes(data.clientes ?? []);
            setLastPage(data.last_page ?? 1);
            setTotal(data.total ?? 0);
        } catch (err) {
            setError("Error al cargar las certificaciones.");
        } finally {
            setLoading(false);
        }
    }, [obraId, page, filtros]);

    useEffect(() => {
        cargarDatos();
    }, [cargarDatos]);

    const aplicarFiltros = () => {
        setFiltros({ ...pending });
        setPage(1);
    };

    const limpiarFiltros = () => {
        const vacio = {
            search: "",
            oficio_id: "",
            cliente_id: "",
            estado_certificacion: "",
            fecha_desde: "",
            fecha_hasta: "",
        };
        setPending(vacio);
        setFiltros(vacio);
        setPage(1);
    };

    // CRUD
    const crearCertificacion = async (datos) => {
        const { data } = await api.post(
            `/obras/${obraId}/certificaciones`,
            datos,
        );
        await cargarDatos();
        return data;
    };

    const crearCapitulo = async (datos) => {
        const { data } = await api.post(
            `/obras/${obraId}/certificaciones/capitulo`,
            datos,
        );
        await cargarDatos();
        return data;
    };

    const eliminarCertificacion = async (id) => {
        await api.delete(`/certificaciones/${id}`);
        await cargarDatos();
    };

    return {
        certificaciones,
        oficios,
        clientes,
        loading,
        error,
        page,
        setPage,
        lastPage,
        total,
        pending,
        setPending,
        filtros,
        aplicarFiltros,
        limpiarFiltros,
        crearCertificacion,
        crearCapitulo,
        eliminarCertificacion,
        recargar: cargarDatos,
    };
}
