import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import Pagination from "../../shared/Pagination";
import { useNotification } from "../../shared/NotificationContext";
import FormularioEmpleado from "./FormularioEmpleado";
import SelectorObras from "./SelectorObras";
import { Cargando, EstadoEmpleado, ObrasChips, ResumenDocumentacion, Vacio } from "./Comunes";
import { botonPrimario, botonSecundario, fechaCorta, inputBase } from "../utils";

export default function ListaEmpleados({ onAbrirFicha }) {
    const { showError } = useNotification();

    const [empleados, setEmpleados] = useState([]);
    const [cargando, setCargando] = useState(true);
    const [pagina, setPagina] = useState({ actual: 1, ultima: 1, total: 0 });
    const [stats, setStats] = useState(null);
    const [opciones, setOpciones] = useState(null);
    const [nuevo, setNuevo] = useState(false);

    const [search, setSearch] = useState("");
    const [estado, setEstado] = useState("activo");
    const [obra, setObra] = useState(null);

    const cargar = async (page = 1, filtros = { search, estado, obraId: obra?.id }) => {
        setCargando(true);
        try {
            const params = { page };
            if (filtros.search) params.search = filtros.search;
            if (filtros.estado) params.estado = filtros.estado;
            if (filtros.obraId) params.obra_id = filtros.obraId;

            const { data } = await api.get("/rrhh/empleados", { params });
            setEmpleados(data.data || []);
            setPagina({ actual: data.current_page || 1, ultima: data.last_page || 1, total: data.total || 0 });
            setStats(data.stats || null);
            setOpciones(data.opciones || null);
        } catch (error) {
            showError("Error al cargar los empleados");
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        cargar(1);
    }, []);

    const aplicar = (e) => {
        e?.preventDefault();
        cargar(1);
    };

    const cambiarEstado = (valor) => {
        setEstado(valor);
        cargar(1, { search, estado: valor, obraId: obra?.id });
    };

    const limpiar = () => {
        setSearch("");
        setObra(null);
        setEstado("activo");
        cargar(1, { search: "", estado: "activo", obraId: "" });
    };

    const FILTROS_ESTADO = [
        { valor: "activo", texto: "De alta", n: stats?.activos },
        { valor: "baja", texto: "De baja", n: stats?.bajas },
        { valor: "", texto: "Todos", n: stats ? stats.activos + stats.bajas : undefined },
    ];

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            {/* Filtros */}
            <div className="space-y-3 border-b border-slate-200 px-4 py-4 sm:px-6">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex gap-1 overflow-x-auto rounded-xl bg-slate-100 p-1">
                        {FILTROS_ESTADO.map((f) => (
                            <button
                                key={f.valor || "todos"}
                                type="button"
                                onClick={() => cambiarEstado(f.valor)}
                                className={`inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition ${
                                    estado === f.valor ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-800"
                                }`}
                            >
                                {f.texto}
                                {f.n !== undefined && (
                                    <span className="rounded-full bg-slate-200/70 px-1.5 text-[11px] text-slate-600">{f.n}</span>
                                )}
                            </button>
                        ))}
                    </div>

                    <button type="button" onClick={() => setNuevo(true)} className={botonPrimario} disabled={!opciones}>
                        <i className="mgc_user_add_line"></i> Nuevo empleado
                    </button>
                </div>

                <form onSubmit={aplicar} className="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_minmax(0,18rem)_auto]">
                    <div className="relative">
                        <i className="mgc_search_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por nombre, DNI o puesto…"
                            className={`${inputBase} pl-9`}
                        />
                    </div>
                    <SelectorObras
                        value={obra}
                        placeholder="Filtrar por obra…"
                        onChange={(o) => {
                            setObra(o);
                            cargar(1, { search, estado, obraId: o?.id });
                        }}
                    />
                    <div className="flex gap-2">
                        <button type="submit" className={`${botonSecundario} flex-1 sm:flex-none`}>
                            <i className="mgc_search_line"></i> Buscar
                        </button>
                        {(search || obra || estado !== "activo") && (
                            <button type="button" onClick={limpiar} className={botonSecundario} title="Limpiar filtros">
                                <i className="mgc_close_line"></i>
                            </button>
                        )}
                    </div>
                </form>
            </div>

            {/* Listado */}
            {cargando ? (
                <Cargando texto="Cargando empleados…" />
            ) : empleados.length === 0 ? (
                <Vacio
                    titulo={search || obra ? "Sin resultados" : estado === "baja" ? "No hay empleados de baja" : "Todavía no hay empleados"}
                    texto={
                        search || obra
                            ? "Prueba con otros filtros."
                            : "Al dar de alta un empleado se crea su carpeta en el Drive con un apartado por cada tipo de documento."
                    }
                >
                    {!search && !obra && estado !== "baja" && (
                        <button type="button" onClick={() => setNuevo(true)} className={botonPrimario} disabled={!opciones}>
                            <i className="mgc_user_add_line"></i> Dar de alta al primero
                        </button>
                    )}
                </Vacio>
            ) : (
                <>
                    {/* Tabla (tablet y escritorio) */}
                    <div className="hidden overflow-x-auto md:block">
                        <table className="min-w-full text-sm">
                            <thead className="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                <tr>
                                    <th className="px-6 py-3">Empleado</th>
                                    <th className="px-3 py-3">Puesto</th>
                                    <th className="hidden px-3 py-3 lg:table-cell">Obras</th>
                                    <th className="px-3 py-3">Alta</th>
                                    <th className="px-3 py-3">Documentación</th>
                                    <th className="px-3 py-3">Estado</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {empleados.map((e) => (
                                    <tr key={e.id} onClick={() => onAbrirFicha(e.id)} className="cursor-pointer transition hover:bg-cyan-50/40">
                                        <td className="px-6 py-3">
                                            <p className="font-semibold text-slate-800">
                                                {e.apellidos}, {e.nombre}
                                            </p>
                                            <p className="font-mono text-xs text-slate-500">{e.dni}</p>
                                        </td>
                                        <td className="px-3 py-3 text-slate-600">{e.puesto || "—"}</td>
                                        <td className="hidden max-w-[18rem] px-3 py-3 text-slate-600 lg:table-cell">
                                            <ObrasChips obras={e.obras} max={2} />
                                        </td>
                                        <td className="whitespace-nowrap px-3 py-3 text-slate-600">
                                            {fechaCorta(e.periodo_actual?.fecha_alta)}
                                        </td>
                                        <td className="px-3 py-3">
                                            <ResumenDocumentacion resumen={e.documentacion} />
                                        </td>
                                        <td className="px-3 py-3">
                                            <EstadoEmpleado estado={e.estado} />
                                        </td>
                                        <td className="px-6 py-3 text-right text-slate-400">
                                            <i className="mgc_right_line text-lg"></i>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Tarjetas (móvil) */}
                    <ul className="divide-y divide-slate-100 md:hidden">
                        {empleados.map((e) => (
                            <li key={e.id}>
                                <button type="button" onClick={() => onAbrirFicha(e.id)} className="w-full px-4 py-3 text-left transition active:bg-cyan-50/60">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="break-words font-semibold text-slate-800">
                                                {e.apellidos}, {e.nombre}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                <span className="font-mono">{e.dni}</span>
                                                {e.puesto && ` · ${e.puesto}`}
                                            </p>
                                        </div>
                                        <EstadoEmpleado estado={e.estado} />
                                    </div>
                                    {e.obras?.length > 0 && (
                                        <div className="mt-2">
                                            <ObrasChips obras={e.obras} max={2} />
                                        </div>
                                    )}
                                    <div className="mt-2 flex flex-wrap items-center justify-between gap-2">
                                        <ResumenDocumentacion resumen={e.documentacion} />
                                        <span className="text-xs text-slate-400">Alta: {fechaCorta(e.periodo_actual?.fecha_alta)}</span>
                                    </div>
                                </button>
                            </li>
                        ))}
                    </ul>

                    {pagina.ultima > 1 && (
                        <div className="border-t border-slate-200 px-4 py-4 sm:px-6">
                            <Pagination
                                currentPage={pagina.actual}
                                lastPage={pagina.ultima}
                                total={pagina.total}
                                onPageChange={(p) => cargar(p)}
                            />
                        </div>
                    )}
                </>
            )}

            {nuevo && (
                <FormularioEmpleado
                    opciones={opciones}
                    onCerrar={() => setNuevo(false)}
                    onGuardado={(data) => {
                        setNuevo(false);
                        onAbrirFicha(data.empleado.id);
                    }}
                />
            )}
        </div>
    );
}
