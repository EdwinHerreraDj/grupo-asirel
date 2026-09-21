import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { formatEuro } from "../../shared/formato";
import useEnvio from "../useEnvio";
import { Aviso, Campo, CampoAdjunto, InputEuro, Modal, Segmentado, SeccionFormulario, claseInput } from "./Comunes";
import { MESES, aFormData, botonPrimario, botonSecundario, fechaCorta, hoyISO, num } from "../utils";

export const TIPOS_NOMINA = { mensual: "Mensual", extra: "Paga extra", finiquito: "Finiquito", atrasos: "Atrasos" };

/**
 * Registrar o editar una nómina. Carga los anticipos pendientes del empleado
 * para elegir cuáles se descuentan.
 */
export default function ModalNomina({ empleado, nomina = null, anio, mes, onCerrar, onGuardado }) {
    const { errores, enviando, enviar, limpiarError } = useEnvio();
    const hoy = new Date();

    const [d, setD] = useState(() => ({
        anio: nomina?.anio ?? anio ?? hoy.getFullYear(),
        mes: nomina?.mes ?? mes ?? hoy.getMonth() + 1,
        tipo: nomina?.tipo ?? "mensual",
        bruto: nomina?.bruto ?? "",
        irpf: nomina?.irpf ?? "",
        seguridad_social: nomina?.seguridad_social ?? "",
        otras_deducciones: nomina?.otras_deducciones ?? "",
        neto: nomina?.neto ?? "",
        coste_empresa: nomina?.coste_empresa ?? "",
        estado: nomina?.estado ?? "pendiente",
        fecha_pago: nomina?.fecha_pago ?? "",
        observaciones: nomina?.observaciones ?? "",
    }));
    const [elegidos, setElegidos] = useState(() => new Set((nomina?.anticipos_descontados ?? []).map((a) => a.id)));
    const [disponibles, setDisponibles] = useState(nomina?.anticipos_descontados ?? []);
    const [archivo, setArchivo] = useState(null);
    const [errorArchivo, setErrorArchivo] = useState(null);
    const [netoTocado, setNetoTocado] = useState(!!nomina);

    const poner = (campo, valor) => {
        setD((p) => ({ ...p, [campo]: valor }));
        limpiarError(campo);
    };

    // Anticipos pendientes del empleado (+ los que ya tiene esta nómina).
    useEffect(() => {
        api.get(`/rrhh/empleados/${empleado.id}/anticipos`).then(({ data }) => {
            const pendientes = (data.anticipos ?? []).filter((a) => !a.nomina_id || a.nomina_id === nomina?.id);
            setDisponibles(pendientes.sort((a, b) => a.fecha.localeCompare(b.fecha)));
        });
    }, [empleado.id]);

    const anticipos = disponibles.filter((a) => elegidos.has(a.id)).reduce((s, a) => s + num(a.importe), 0);
    const netoCalculado = Math.round((num(d.bruto) - num(d.irpf) - num(d.seguridad_social) - anticipos - num(d.otras_deducciones)) * 100) / 100;
    const netoCuadra = d.neto === "" || Math.abs(num(d.neto) - netoCalculado) < 0.01;
    const pctIrpf = num(d.bruto) > 0 && d.irpf !== "" ? (num(d.irpf) / num(d.bruto)) * 100 : null;

    // Mientras no se toque el neto a mano, se propone el calculado.
    useEffect(() => {
        if (!netoTocado && d.bruto !== "") setD((p) => ({ ...p, neto: netoCalculado.toFixed(2) }));
    }, [netoCalculado, netoTocado]);

    const guardar = async (e) => {
        e.preventDefault();
        const datos = { ...d, anticipo_ids: [...elegidos] };
        const url = nomina ? `/rrhh/nominas/${nomina.id}` : `/rrhh/empleados/${empleado.id}/nominas`;
        const r = await enviar(url, aFormData(datos, archivo, nomina ? "PUT" : null));
        if (r) onGuardado(r);
    };

    const anios = [];
    for (let a = hoy.getFullYear() + 1; a >= hoy.getFullYear() - 5; a--) anios.push(a);
    if (!anios.includes(Number(d.anio))) anios.push(Number(d.anio));

    return (
        <Modal
            etiqueta={nomina ? "Editar nómina" : "Registrar nómina"}
            titulo={empleado.nombre_completo}
            subtitulo="Los importes son los de la nómina que hace la gestoría."
            onCerrar={onCerrar}
            ancho="max-w-3xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-nomina" className={botonPrimario} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar nómina
                    </button>
                </>
            }
        >
            <form id="form-nomina" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Periodo" descripcion="Mes y tipo de nómina" icono="mgc_calendar_line" color="cyan" conError={!!(errores.mes || errores.anio || errores.tipo)}>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Mes" obligatorio error={errores.mes}>
                            <div className="grid grid-cols-[1fr_6.5rem] gap-2">
                                <select value={d.mes} onChange={(e) => poner("mes", Number(e.target.value))} className={claseInput(errores.mes)}>
                                    {MESES.map((m, i) => (
                                        <option key={m} value={i + 1}>
                                            {m}
                                        </option>
                                    ))}
                                </select>
                                <select value={d.anio} onChange={(e) => poner("anio", Number(e.target.value))} className={claseInput(errores.anio)}>
                                    {anios.map((a) => (
                                        <option key={a} value={a}>
                                            {a}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </Campo>
                        <Campo etiqueta="Tipo" obligatorio error={errores.tipo}>
                            <Segmentado opciones={TIPOS_NOMINA} value={d.tipo} onChange={(v) => poner("tipo", v)} columnas={2} />
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Importes"
                    descripcion="Bruto, deducciones y líquido a percibir"
                    icono="mgc_currency_euro_line"
                    color="emerald"
                    conError={["bruto", "irpf", "seguridad_social", "otras_deducciones", "neto", "coste_empresa"].some((c) => errores[c])}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Total devengado (bruto)" obligatorio error={errores.bruto}>
                            <InputEuro value={d.bruto} onChange={(v) => poner("bruto", v)} error={errores.bruto} />
                        </Campo>
                        <Campo etiqueta="Retención IRPF" error={errores.irpf} ayuda={pctIrpf !== null ? `${pctIrpf.toFixed(2).replace(".", ",")} % del bruto` : null}>
                            <InputEuro value={d.irpf} onChange={(v) => poner("irpf", v)} error={errores.irpf} />
                        </Campo>
                        <Campo etiqueta="Seguridad Social (trabajador)" error={errores.seguridad_social}>
                            <InputEuro value={d.seguridad_social} onChange={(v) => poner("seguridad_social", v)} error={errores.seguridad_social} />
                        </Campo>
                        <Campo etiqueta="Otras deducciones" error={errores.otras_deducciones}>
                            <InputEuro value={d.otras_deducciones} onChange={(v) => poner("otras_deducciones", v)} error={errores.otras_deducciones} />
                        </Campo>
                        <Campo etiqueta="Anticipos y vales descontados" ayuda="Se eligen abajo.">
                            <InputEuro value={anticipos.toFixed(2)} onChange={() => {}} disabled />
                        </Campo>
                        <Campo etiqueta="Líquido a percibir (neto)" obligatorio error={errores.neto}>
                            <InputEuro
                                value={d.neto}
                                onChange={(v) => {
                                    setNetoTocado(true);
                                    poner("neto", v);
                                }}
                                error={errores.neto}
                            />
                            {!netoCuadra ? (
                                <p className="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-amber-700">
                                    <span>
                                        <i className="mgc_warning_line"></i> Bruto menos deducciones da {formatEuro(netoCalculado)}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => poner("neto", netoCalculado.toFixed(2))}
                                        className="font-semibold text-cyan-700 hover:underline"
                                    >
                                        Usar ese importe
                                    </button>
                                </p>
                            ) : (
                                d.neto !== "" && <p className="mt-1 text-xs text-emerald-600"><i className="mgc_check_circle_line"></i> Cuadra con el bruto y las deducciones</p>
                            )}
                        </Campo>
                        <Campo etiqueta="Coste para la empresa" error={errores.coste_empresa} ayuda="Opcional: bruto + Seguridad Social de la empresa." className="sm:col-span-2 sm:max-w-sm">
                            <InputEuro value={d.coste_empresa} onChange={(v) => poner("coste_empresa", v)} error={errores.coste_empresa} />
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Anticipos y vales a descontar"
                    descripcion={disponibles.length ? "Marca los que se descuentan en esta nómina" : "No tiene anticipos pendientes"}
                    icono="mgc_bank_card_line"
                    color="amber"
                    conError={!!errores.anticipo_ids}
                >
                    {disponibles.length === 0 ? (
                        <p className="text-sm text-slate-500">Cuando le entregues un anticipo o un vale, aparecerá aquí para descontarlo.</p>
                    ) : (
                        <ul className="space-y-2">
                            {disponibles.map((a) => (
                                <li key={a.id}>
                                    <label className="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50">
                                        <input
                                            type="checkbox"
                                            checked={elegidos.has(a.id)}
                                            onChange={(e) =>
                                                setElegidos((p) => {
                                                    const n = new Set(p);
                                                    e.target.checked ? n.add(a.id) : n.delete(a.id);
                                                    return n;
                                                })
                                            }
                                            className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                        />
                                        <span className="min-w-0 flex-1 text-sm">
                                            <span className="font-medium text-slate-800">{a.tipo === "vale" ? "Vale" : "Anticipo"}</span>
                                            <span className="text-slate-500"> · {fechaCorta(a.fecha)}{a.concepto ? ` · ${a.concepto}` : ""}</span>
                                        </span>
                                        <span className="font-semibold text-slate-800">{formatEuro(a.importe)}</span>
                                    </label>
                                </li>
                            ))}
                        </ul>
                    )}
                    {errores.anticipo_ids && <p className="mt-2 text-xs text-red-600">{errores.anticipo_ids}</p>}
                </SeccionFormulario>

                <SeccionFormulario titulo="Pago" descripcion="¿Está pagada?" icono="mgc_check_circle_line" color="indigo" conError={!!errores.fecha_pago}>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Estado">
                            <Segmentado
                                opciones={{ pendiente: "Pendiente", pagada: "Pagada" }}
                                value={d.estado}
                                onChange={(v) => {
                                    poner("estado", v);
                                    if (v === "pagada" && !d.fecha_pago) poner("fecha_pago", hoyISO());
                                }}
                            />
                        </Campo>
                        {d.estado === "pagada" && (
                            <Campo etiqueta="Fecha de pago" obligatorio error={errores.fecha_pago}>
                                <input type="date" value={d.fecha_pago} onChange={(e) => poner("fecha_pago", e.target.value)} className={claseInput(errores.fecha_pago)} />
                            </Campo>
                        )}
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="PDF de la nómina" descripcion="Opcional" icono="mgc_attachment_line" color="violet" conError={!!(errores.archivo || errorArchivo)}>
                    <CampoAdjunto
                        archivo={archivo}
                        actual={nomina?.archivo}
                        texto="Adjuntar el PDF de la nómina"
                        carpeta="Recibos de nómina"
                        error={errores.archivo || errorArchivo}
                        onChange={(f, err) => {
                            setArchivo(f);
                            setErrorArchivo(err ?? null);
                        }}
                    />
                </SeccionFormulario>

                <SeccionFormulario titulo="Observaciones" descripcion="Opcional" icono="mgc_edit_line" color="slate">
                    <textarea rows={2} value={d.observaciones} onChange={(e) => poner("observaciones", e.target.value)} className={claseInput(errores.observaciones)} />
                </SeccionFormulario>

                {nomina && nomina.anticipos_descontados?.length > 0 && (
                    <Aviso>Si quitas un anticipo de esta nómina, vuelve a quedar pendiente de descontar.</Aviso>
                )}
            </form>
        </Modal>
    );
}
