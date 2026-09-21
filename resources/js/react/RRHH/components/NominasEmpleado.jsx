import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { formatEuro } from "../../shared/formato";
import ModalNomina, { TIPOS_NOMINA } from "./ModalNomina";
import ModalConfirmar from "./ModalConfirmar";
import { BotonIcono, Cargando, Cifra, Panel, Vacio } from "./Comunes";
import { MESES, botonPrimario, fechaCorta, inputBase, mensajeDeError } from "../utils";

export function EstadoNomina({ n }) {
    return n.estado === "pagada" ? (
        <span className="inline-flex items-center gap-1 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
            <i className="mgc_check_line"></i> Pagada{n.fecha_pago ? ` ${fechaCorta(n.fecha_pago)}` : ""}
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 whitespace-nowrap rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
            <i className="mgc_time_line"></i> Pendiente de pago
        </span>
    );
}

/** Nóminas del año de un empleado (pestaña de la ficha). */
export default function NominasEmpleado({ empleado }) {
    const { showSuccess, showError } = useNotification();
    const [anio, setAnio] = useState(new Date().getFullYear());
    const [datos, setDatos] = useState(null);
    const [cargando, setCargando] = useState(true);
    const [modal, setModal] = useState(null); // {nomina?}
    const [borrar, setBorrar] = useState(null);

    const cargar = async (a = anio) => {
        setCargando(true);
        try {
            const { data } = await api.get(`/rrhh/empleados/${empleado.id}/nominas`, { params: { anio: a } });
            setDatos(data);
        } catch (error) {
            showError(mensajeDeError(error, "Error al cargar las nóminas."));
        } finally {
            setCargando(false);
        }
    };

    useEffect(() => {
        cargar(anio);
    }, [empleado.id, anio]);

    const eliminar = async () => {
        try {
            const { data } = await api.delete(`/rrhh/nominas/${borrar.id}`);
            showSuccess(data.message);
            setBorrar(null);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudo eliminar."));
        }
    };

    // Mes propuesto: el primero del año sin nómina mensual (o el actual).
    const mesPropuesto = () => {
        const con = new Set((datos?.nominas ?? []).filter((n) => n.tipo === "mensual").map((n) => n.mes));
        const tope = anio === new Date().getFullYear() ? new Date().getMonth() + 1 : 12;
        for (let m = 1; m <= tope; m++) if (!con.has(m)) return m;
        return tope;
    };

    const t = datos?.totales;

    return (
        <Panel
            titulo="Nóminas"
            icono="mgc_currency_euro_line"
            acciones={
                <>
                    <select value={anio} onChange={(e) => setAnio(Number(e.target.value))} className={`${inputBase} w-28`} aria-label="Año">
                        {(datos?.anios ?? [anio]).map((a) => (
                            <option key={a} value={a}>
                                {a}
                            </option>
                        ))}
                    </select>
                    <button type="button" onClick={() => setModal({})} className={`${botonPrimario} flex-1 sm:flex-none`} disabled={!datos}>
                        <i className="mgc_add_line"></i> Registrar nómina
                    </button>
                </>
            }
        >
            {cargando && !datos ? (
                <Cargando />
            ) : (
                <div className={`space-y-5 px-5 py-5 sm:px-6 ${cargando ? "opacity-60" : ""}`}>
                    <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <Cifra etiqueta={`Bruto ${anio}`} valor={formatEuro(t?.bruto)} />
                        <Cifra etiqueta="IRPF + Seg. Social" valor={formatEuro((t?.irpf ?? 0) + (t?.seguridad_social ?? 0))} />
                        <Cifra etiqueta={`Neto ${anio}`} valor={formatEuro(t?.neto)} tono="emerald" />
                        <Cifra
                            etiqueta="Pendientes de pago"
                            valor={t?.pendientes_pago ?? 0}
                            tono={t?.pendientes_pago ? "amber" : "slate"}
                            detalle={`${t?.registradas ?? 0} registradas`}
                        />
                    </div>

                    {datos?.anticipos_pendientes?.length > 0 && (
                        <p className="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <i className="mgc_bank_card_line mr-1"></i>
                            Tiene {datos.anticipos_pendientes.length} {datos.anticipos_pendientes.length === 1 ? "anticipo pendiente" : "anticipos pendientes"} de descontar (
                            {formatEuro(datos.anticipos_pendientes.reduce((s, a) => s + Number(a.importe), 0))}).
                        </p>
                    )}

                    {datos?.nominas?.length === 0 ? (
                        <Vacio icono="mgc_currency_euro_line" titulo={`Sin nóminas registradas en ${anio}`} texto="Registra cada mes los importes y el PDF que envía la gestoría." />
                    ) : (
                        <ul className="space-y-2">
                            {datos?.nominas?.map((n) => (
                                <li key={n.id} className="flex flex-col gap-3 rounded-2xl border border-slate-200 px-4 py-3 md:flex-row md:items-center">
                                    <div className="flex min-w-0 flex-1 items-center gap-3">
                                        <div className="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl bg-cyan-50 text-cyan-800">
                                            <span className="text-[10px] font-semibold uppercase leading-none">{MESES[n.mes - 1].slice(0, 3)}</span>
                                            <span className="text-xs font-bold leading-tight">{n.anio}</span>
                                        </div>
                                        <div className="min-w-0">
                                            <p className="font-semibold text-slate-800">
                                                {MESES[n.mes - 1]} {n.tipo !== "mensual" && <span className="text-sm font-medium text-violet-700">· {TIPOS_NOMINA[n.tipo]}</span>}
                                            </p>
                                            <p className="text-xs text-slate-500">
                                                Bruto {formatEuro(n.bruto)} · IRPF {formatEuro(n.irpf)} · S.S. {formatEuro(n.seguridad_social)}
                                                {Number(n.anticipos) > 0 && ` · Anticipos ${formatEuro(n.anticipos)}`}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex flex-wrap items-center justify-between gap-3 md:justify-end">
                                        <div className="text-right">
                                            <p className="text-[11px] uppercase tracking-[0.1em] text-slate-400">Neto</p>
                                            <p className="font-bold text-slate-900">{formatEuro(n.neto)}</p>
                                        </div>
                                        <EstadoNomina n={n} />
                                        <div className="flex items-center">
                                            {n.archivo && <BotonIcono icono="mgc_file_line" titulo="Ver PDF" href={`/drive/ver/${n.archivo.id}`} target="_blank" />}
                                            <BotonIcono icono="mgc_edit_line" titulo="Editar" onClick={() => setModal({ nomina: n })} />
                                            <BotonIcono icono="mgc_delete_line" titulo="Eliminar" peligro onClick={() => setBorrar(n)} />
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}

            {modal && (
                <ModalNomina
                    empleado={empleado}
                    nomina={modal.nomina}
                    anio={anio}
                    mes={mesPropuesto()}
                    onCerrar={() => setModal(null)}
                    onGuardado={(r) => {
                        setModal(null);
                        if (r.nomina?.anio && r.nomina.anio !== anio) setAnio(r.nomina.anio);
                        else cargar();
                    }}
                />
            )}

            {borrar && (
                <ModalConfirmar titulo="Eliminar nómina" textoConfirmar="Eliminar" peligro onConfirmar={eliminar} onCerrar={() => setBorrar(null)}>
                    <p>
                        Se eliminará la nómina de <strong>{MESES[borrar.mes - 1]} {borrar.anio}</strong> ({TIPOS_NOMINA[borrar.tipo]?.toLowerCase()}).
                    </p>
                    {Number(borrar.anticipos) > 0 && <p className="mt-2">Sus anticipos descontados volverán a quedar pendientes.</p>}
                    {borrar.archivo && <p className="mt-2 text-xs text-slate-500">El PDF se conserva en su carpeta del Drive.</p>}
                </ModalConfirmar>
            )}
        </Panel>
    );
}
