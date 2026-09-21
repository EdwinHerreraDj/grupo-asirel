import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { formatEuro } from "../../shared/formato";
import ModalNomina, { TIPOS_NOMINA } from "./ModalNomina";
import SelectorObras from "./SelectorObras";
import { EstadoNomina } from "./NominasEmpleado";
import { Campo, Cargando, Cifra, Modal, Vacio, claseInput } from "./Comunes";
import { MESES, botonPrimario, botonSecundario, hoyISO, inputBase, mensajeDeError } from "../utils";

function ModalPagar({ n, onConfirmar, onCerrar }) {
    const [fecha, setFecha] = useState(hoyISO());
    const [enviando, setEnviando] = useState(false);
    return (
        <Modal
            etiqueta="Pago"
            titulo={`Marcar ${n} ${n === 1 ? "nómina" : "nóminas"} como pagadas`}
            onCerrar={enviando ? () => {} : onCerrar}
            ancho="max-w-md"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button
                        type="button"
                        className={botonPrimario}
                        disabled={enviando || !fecha}
                        onClick={async () => {
                            setEnviando(true);
                            await onConfirmar(fecha);
                            setEnviando(false);
                        }}
                    >
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Marcar como pagadas
                    </button>
                </>
            }
        >
            <Campo etiqueta="Fecha de pago" obligatorio>
                <input type="date" value={fecha} onChange={(e) => setFecha(e.target.value)} className={claseInput(false)} />
            </Campo>
        </Modal>
    );
}

/** Nóminas de un mes de toda la plantilla. */
export default function NominasMes({ onAbrirFicha }) {
    const { showSuccess, showError } = useNotification();
    const ahora = new Date();
    const [anio, setAnio] = useState(ahora.getFullYear());
    const [mes, setMes] = useState(ahora.getMonth() + 1);
    const [datos, setDatos] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [search, setSearch] = useState("");
    const [buscar, setBuscar] = useState("");
    const [obra, setObra] = useState(null);
    const [marcadas, setMarcadas] = useState(new Set());
    const [pagar, setPagar] = useState(false);
    const [registrar, setRegistrar] = useState(null); // {empleado, nomina?}

    const cargar = async () => {
        setCargando(true);
        try {
            const { data } = await api.get("/rrhh/nominas", { params: { anio, mes, search: buscar || undefined, obra_id: obra?.id } });
            setDatos(data);
            setMarcadas(new Set());
        } catch {
            showError("Error al cargar las nóminas");
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        cargar();
    }, [anio, mes, buscar, obra?.id]);

    useEffect(() => {
        const t = setTimeout(() => setBuscar(search.trim()), 350);
        return () => clearTimeout(t);
    }, [search]);

    const mover = (delta) => {
        const d = new Date(anio, mes - 1 + delta, 1);
        setAnio(d.getFullYear());
        setMes(d.getMonth() + 1);
    };

    const pendientes = (datos?.empleados ?? []).flatMap((e) => e.nominas.filter((n) => n.estado === "pendiente"));
    const alternar = (id) =>
        setMarcadas((p) => {
            const n = new Set(p);
            n.has(id) ? n.delete(id) : n.add(id);
            return n;
        });

    const marcarPagadas = async (fecha) => {
        try {
            const { data } = await api.post("/rrhh/nominas/marcar-pagadas", { ids: [...marcadas], fecha_pago: fecha });
            showSuccess(data.message);
            setPagar(false);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudieron marcar."));
        }
    };

    const t = datos?.totales;

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="space-y-4 border-b border-slate-200 px-4 py-4 sm:px-6">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-2">
                        <button type="button" onClick={() => mover(-1)} className={`${botonSecundario} px-3`} aria-label="Mes anterior">
                            <i className="mgc_left_line"></i>
                        </button>
                        <h3 className="min-w-[10rem] text-center text-lg font-semibold text-slate-900">
                            {MESES[mes - 1]} {anio}
                        </h3>
                        <button type="button" onClick={() => mover(1)} className={`${botonSecundario} px-3`} aria-label="Mes siguiente">
                            <i className="mgc_right_line"></i>
                        </button>
                    </div>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-[34rem]">
                        <div className="relative">
                            <i className="mgc_search_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Buscar empleado…" className={`${inputBase} pl-9`} />
                        </div>
                        <SelectorObras value={obra} onChange={setObra} placeholder="Filtrar por obra…" />
                    </div>
                </div>

                {t && (
                    <div className="grid grid-cols-2 gap-3 lg:grid-cols-5">
                        <Cifra etiqueta="Empleados" valor={t.empleados} />
                        <Cifra etiqueta="Sin nómina" valor={t.sin_nomina} tono={t.sin_nomina ? "amber" : "emerald"} detalle="mensual" />
                        <Cifra etiqueta="Pendientes de pago" valor={t.pendientes_pago} tono={t.pendientes_pago ? "amber" : "slate"} />
                        <Cifra etiqueta="Total bruto" valor={formatEuro(t.bruto)} />
                        <Cifra etiqueta="Total neto" valor={formatEuro(t.neto)} tono="cyan" />
                    </div>
                )}

                {pendientes.length > 0 && (
                    <div className="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-slate-50/70 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                checked={marcadas.size > 0 && marcadas.size === pendientes.length}
                                onChange={(e) => setMarcadas(e.target.checked ? new Set(pendientes.map((n) => n.id)) : new Set())}
                                className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                            />
                            {marcadas.size ? `${marcadas.size} seleccionadas` : "Seleccionar las pendientes de pago"}
                        </label>
                        <button type="button" onClick={() => setPagar(true)} disabled={!marcadas.size} className={botonPrimario}>
                            <i className="mgc_check_circle_line"></i> Marcar como pagadas
                        </button>
                    </div>
                )}
            </div>

            {cargando && !datos ? (
                <Cargando />
            ) : datos?.empleados?.length === 0 ? (
                <Vacio icono="mgc_currency_euro_line" titulo="Nadie de alta en este mes" />
            ) : (
                <ul className={`divide-y divide-slate-100 ${cargando ? "opacity-60" : ""}`}>
                    {datos?.empleados.map((e) => (
                        <li key={e.id} className="flex flex-col gap-3 px-4 py-3 lg:flex-row lg:items-center sm:px-6">
                            <div className="min-w-0 lg:w-72">
                                <button type="button" onClick={() => onAbrirFicha(e.id)} className="break-words text-left font-semibold text-slate-800 hover:text-cyan-700">
                                    {e.nombre}
                                </button>
                                <p className="text-xs text-slate-500">
                                    <span className="font-mono">{e.dni}</span>
                                    {e.puesto && ` · ${e.puesto}`}
                                </p>
                                {e.anticipos_pendientes > 0 && (
                                    <p className="mt-1 text-xs font-semibold text-amber-700">
                                        <i className="mgc_bank_card_line"></i> {formatEuro(e.anticipos_pendientes)} en anticipos por descontar
                                    </p>
                                )}
                            </div>

                            <div className="flex min-w-0 flex-1 flex-col gap-2">
                                {e.nominas.length === 0 ? (
                                    <span className="self-start rounded-full border border-dashed border-amber-300 bg-amber-50/60 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        Sin nómina registrada
                                    </span>
                                ) : (
                                    e.nominas.map((n) => (
                                        <div key={n.id} className="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                                            {n.estado === "pendiente" && (
                                                <input
                                                    type="checkbox"
                                                    checked={marcadas.has(n.id)}
                                                    onChange={() => alternar(n.id)}
                                                    aria-label="Seleccionar para marcar como pagada"
                                                    className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                                />
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => setRegistrar({ empleado: { id: e.id, nombre_completo: e.nombre_completo }, nomina: n })}
                                                className="font-medium text-slate-700 hover:text-cyan-700"
                                            >
                                                {TIPOS_NOMINA[n.tipo]}
                                            </button>
                                            <span className="text-slate-500">Bruto {formatEuro(n.bruto)}</span>
                                            <span className="font-semibold text-slate-900">Neto {formatEuro(n.neto)}</span>
                                            <EstadoNomina n={n} />
                                            {n.archivo && (
                                                <a href={`/drive/ver/${n.archivo.id}`} target="_blank" rel="noopener" className="text-cyan-700 hover:underline" title="Ver PDF">
                                                    <i className="mgc_file_line"></i>
                                                </a>
                                            )}
                                        </div>
                                    ))
                                )}
                            </div>

                            <button
                                type="button"
                                onClick={() => setRegistrar({ empleado: { id: e.id, nombre_completo: e.nombre_completo } })}
                                className={`${botonSecundario} self-start lg:self-center`}
                            >
                                <i className="mgc_add_line"></i> {e.nominas.length ? "Otra" : "Registrar"}
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            {registrar && (
                <ModalNomina
                    empleado={registrar.empleado}
                    nomina={registrar.nomina}
                    anio={anio}
                    mes={mes}
                    onCerrar={() => setRegistrar(null)}
                    onGuardado={() => {
                        setRegistrar(null);
                        cargar();
                    }}
                />
            )}
            {pagar && <ModalPagar n={marcadas.size} onConfirmar={marcarPagadas} onCerrar={() => setPagar(false)} />}
        </div>
    );
}
