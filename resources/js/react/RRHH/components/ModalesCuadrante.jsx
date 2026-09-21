import React, { useMemo, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import SelectorObras from "./SelectorObras";
import { Aviso, Campo, Modal, SeccionFormulario, claseInput } from "./Comunes";
import { DIAS_SEMANA, botonPeligro, botonPrimario, botonSecundario, colorAusencia, erroresDeValidacion, fechaCorta, inputBase, mensajeDeError, numeroDias } from "../utils";

/** Tarjetas para elegir un turno. */
function ElegirTurno({ turnos, value, onChange }) {
    const activos = turnos.filter((t) => t.activo || t.id === value);
    return (
        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
            {activos.map((t) => {
                const marcado = t.id === value;
                return (
                    <button
                        key={t.id}
                        type="button"
                        onClick={() => onChange(t.id)}
                        className={`flex items-center gap-3 rounded-xl border px-3 py-2.5 text-left transition ${
                            marcado ? "border-cyan-500 bg-cyan-50/60 ring-1 ring-cyan-500" : "border-slate-200 bg-white hover:bg-slate-50"
                        }`}
                    >
                        <span className={`h-3 w-3 shrink-0 rounded-full ${colorAusencia(t.color).punto}`}></span>
                        <span className="min-w-0 flex-1">
                            <span className="block text-sm font-medium text-slate-800">{t.nombre}</span>
                            <span className="block text-[11px] text-slate-500">
                                {t.horario} · {numeroDias(t.horas)} h
                            </span>
                        </span>
                        {marcado && <i className="mgc_check_circle_line text-lg text-cyan-600"></i>}
                    </button>
                );
            })}
            {activos.length === 0 && <Aviso tipo="aviso">No hay turnos activos. Créalos en Configuración → Turnos.</Aviso>}
        </div>
    );
}

/** Obra del día: atajos con las obras del empleado + buscador. */
function ElegirObra({ obrasEmpleado = [], value, onChange }) {
    return (
        <div className="space-y-2">
            <div className="flex flex-wrap gap-1.5">
                <button
                    type="button"
                    onClick={() => onChange(null)}
                    className={`rounded-lg border px-2.5 py-1 text-xs font-semibold transition ${
                        !value ? "border-cyan-500 bg-cyan-50 text-cyan-800" : "border-slate-200 text-slate-600 hover:bg-slate-50"
                    }`}
                >
                    Sin obra
                </button>
                {obrasEmpleado.map((o) => (
                    <button
                        key={o.id}
                        type="button"
                        onClick={() => onChange(o)}
                        className={`max-w-full truncate rounded-lg border px-2.5 py-1 text-xs font-semibold transition ${
                            value?.id === o.id ? "border-cyan-500 bg-cyan-50 text-cyan-800" : "border-slate-200 text-slate-600 hover:bg-slate-50"
                        }`}
                    >
                        {o.nombre}
                    </button>
                ))}
            </div>
            <SelectorObras value={value} onChange={onChange} placeholder="Buscar otra obra…" enLinea />
        </div>
    );
}

/** Informa del resultado de una asignación. */
function useResultado() {
    const { showSuccess, showWarning } = useNotification();
    return (data) => {
        const saltados = Object.values(data.omitidas ?? {}).reduce((s, n) => s + n, 0);
        if (data.creadas + data.actualizadas === 0) showWarning(data.message, 8000);
        else if (saltados) showWarning(data.message, 8000);
        else showSuccess(data.message);
    };
}

/** Editar el turno de un empleado en un día. */
export function ModalDia({ empleado, fecha, asignacion, turnos, onCerrar, onGuardado }) {
    const { showSuccess, showError } = useNotification();
    const informar = useResultado();
    const [turnoId, setTurnoId] = useState(asignacion?.turno_id ?? turnos.find((t) => t.activo)?.id ?? null);
    const [obra, setObra] = useState(asignacion?.obra ?? (empleado.obras?.length === 1 ? empleado.obras[0] : null));
    const [observaciones, setObservaciones] = useState(asignacion?.observaciones ?? "");
    const [enviando, setEnviando] = useState(false);

    const guardar = async (e) => {
        e.preventDefault();
        setEnviando(true);
        try {
            const { data } = await api.post("/rrhh/cuadrante/asignar", {
                empleado_ids: [empleado.id],
                desde: fecha,
                hasta: fecha,
                rrhh_turno_id: turnoId,
                obra_id: obra?.id ?? null,
                observaciones,
                sobrescribir: true,
                incluir_festivos: true,
            });
            informar(data);
            onGuardado();
        } catch (error) {
            const campos = erroresDeValidacion(error);
            showError(Object.values(campos)[0] ?? mensajeDeError(error, "No se pudo guardar."));
        } finally {
            setEnviando(false);
        }
    };

    const quitar = async () => {
        setEnviando(true);
        try {
            const { data } = await api.post("/rrhh/cuadrante/eliminar", { empleado_ids: [empleado.id], desde: fecha, hasta: fecha });
            showSuccess(data.message);
            onGuardado();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudo quitar."));
        } finally {
            setEnviando(false);
        }
    };

    return (
        <Modal
            etiqueta={fechaCorta(fecha)}
            titulo={empleado.nombre_completo}
            onCerrar={onCerrar}
            ancho="max-w-xl"
            pie={
                <>
                    {asignacion && (
                        <button type="button" onClick={quitar} className={`${botonPeligro} sm:mr-auto`} disabled={enviando}>
                            <i className="mgc_delete_line"></i> Quitar turno
                        </button>
                    )}
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-dia" className={botonPrimario} disabled={enviando || !turnoId}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-dia" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Turno" descripcion="Horario de ese día" icono="mgc_time_line" color="cyan">
                    <ElegirTurno turnos={turnos} value={turnoId} onChange={setTurnoId} />
                </SeccionFormulario>
                <SeccionFormulario titulo="Obra" descripcion="Dónde trabaja ese día" icono="mgc_building_2_line" color="emerald">
                    <ElegirObra obrasEmpleado={empleado.obras} value={obra} onChange={setObra} />
                </SeccionFormulario>
                <SeccionFormulario titulo="Observaciones" descripcion="Opcional" icono="mgc_edit_line" color="slate">
                    <input value={observaciones} onChange={(e) => setObservaciones(e.target.value)} maxLength={255} className={claseInput(false)} placeholder="Ej.: recoger material en el almacén" />
                </SeccionFormulario>
            </form>
        </Modal>
    );
}

/** Asignar un turno a varios empleados en un rango de fechas. */
export function ModalAsignar({ empleados, turnos, desde, hasta, onCerrar, onGuardado }) {
    const { showError } = useNotification();
    const informar = useResultado();
    const [elegidos, setElegidos] = useState(new Set());
    const [filtro, setFiltro] = useState("");
    const [d, setD] = useState({ desde, hasta, dias: new Set([1, 2, 3, 4, 5]), sobrescribir: false, incluir_festivos: false });
    const [turnoId, setTurnoId] = useState(turnos.find((t) => t.activo)?.id ?? null);
    const [obra, setObra] = useState(null);
    const [errores, setErrores] = useState({});
    const [enviando, setEnviando] = useState(false);

    const visibles = useMemo(
        () => empleados.filter((e) => !filtro || `${e.nombre} ${e.puesto ?? ""}`.toLowerCase().includes(filtro.toLowerCase())),
        [empleados, filtro],
    );
    const todosVisibles = visibles.length > 0 && visibles.every((e) => elegidos.has(e.id));

    const alternar = (conjunto, valor) => {
        const n = new Set(conjunto);
        n.has(valor) ? n.delete(valor) : n.add(valor);
        return n;
    };

    const guardar = async (e) => {
        e.preventDefault();
        setEnviando(true);
        setErrores({});
        try {
            const { data } = await api.post("/rrhh/cuadrante/asignar", {
                empleado_ids: [...elegidos],
                desde: d.desde,
                hasta: d.hasta,
                dias_semana: [...d.dias],
                rrhh_turno_id: turnoId,
                obra_id: obra?.id ?? null,
                sobrescribir: d.sobrescribir,
                incluir_festivos: d.incluir_festivos,
            });
            informar(data);
            onGuardado();
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            showError(Object.values(campos)[0] ?? mensajeDeError(error, "No se pudo asignar."));
        } finally {
            setEnviando(false);
        }
    };

    return (
        <Modal
            etiqueta="Asignar turnos"
            titulo="Asignación en bloque"
            subtitulo="Se saltan los días con ausencia y en los que el empleado no está de alta."
            onCerrar={onCerrar}
            ancho="max-w-3xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-asignar" className={botonPrimario} disabled={enviando || !elegidos.size || !turnoId || !d.dias.size}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Asignar{elegidos.size ? ` a ${elegidos.size}` : ""}
                    </button>
                </>
            }
        >
            <form id="form-asignar" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario
                    titulo="Empleados"
                    descripcion={elegidos.size ? `${elegidos.size} seleccionados` : "Elige a quién asignar"}
                    icono="mgc_group_line"
                    color="cyan"
                    conError={!!errores.empleado_ids}
                >
                    <div className="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input value={filtro} onChange={(e) => setFiltro(e.target.value)} placeholder="Filtrar…" className={`${inputBase} sm:flex-1`} />
                        <button
                            type="button"
                            onClick={() =>
                                setElegidos((p) => {
                                    const n = new Set(p);
                                    visibles.forEach((x) => (todosVisibles ? n.delete(x.id) : n.add(x.id)));
                                    return n;
                                })
                            }
                            className={botonSecundario}
                        >
                            {todosVisibles ? "Quitar todos" : "Marcar todos"}
                        </button>
                    </div>
                    <ul className="max-h-56 space-y-1 overflow-y-auto overscroll-contain rounded-xl border border-slate-200 p-1">
                        {visibles.map((e) => (
                            <li key={e.id}>
                                <label className="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-slate-50">
                                    <input
                                        type="checkbox"
                                        checked={elegidos.has(e.id)}
                                        onChange={() => setElegidos((p) => alternar(p, e.id))}
                                        className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                    />
                                    <span className="min-w-0 flex-1 truncate text-slate-800">{e.nombre}</span>
                                    {e.puesto && <span className="hidden truncate text-xs text-slate-400 sm:inline">{e.puesto}</span>}
                                </label>
                            </li>
                        ))}
                        {visibles.length === 0 && <li className="px-2 py-3 text-sm text-slate-500">Nadie coincide.</li>}
                    </ul>
                </SeccionFormulario>

                <SeccionFormulario titulo="Fechas" descripcion="Rango y días de la semana" icono="mgc_calendar_line" color="amber" conError={!!(errores.desde || errores.hasta)}>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Desde" error={errores.desde}>
                            <input type="date" value={d.desde} onChange={(e) => setD((p) => ({ ...p, desde: e.target.value }))} className={claseInput(errores.desde)} />
                        </Campo>
                        <Campo etiqueta="Hasta" error={errores.hasta}>
                            <input type="date" min={d.desde} value={d.hasta} onChange={(e) => setD((p) => ({ ...p, hasta: e.target.value }))} className={claseInput(errores.hasta)} />
                        </Campo>
                        <Campo etiqueta="Días de la semana" className="sm:col-span-2">
                            <div className="flex flex-wrap gap-1.5">
                                {[1, 2, 3, 4, 5, 6, 7].map((n) => (
                                    <button
                                        key={n}
                                        type="button"
                                        onClick={() => setD((p) => ({ ...p, dias: alternar(p.dias, n) }))}
                                        className={`h-9 w-9 rounded-lg border text-sm font-semibold transition ${
                                            d.dias.has(n) ? "border-cyan-500 bg-cyan-600 text-white" : "border-slate-200 bg-white text-slate-500 hover:bg-slate-50"
                                        }`}
                                    >
                                        {DIAS_SEMANA[n]}
                                    </button>
                                ))}
                            </div>
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Turno" descripcion="Horario" icono="mgc_time_line" color="indigo" conError={!!errores.rrhh_turno_id}>
                    <ElegirTurno turnos={turnos} value={turnoId} onChange={setTurnoId} />
                </SeccionFormulario>

                <SeccionFormulario titulo="Obra" descripcion="Opcional: la misma para todos" icono="mgc_building_2_line" color="emerald">
                    <SelectorObras value={obra} onChange={setObra} placeholder="Buscar obra…" enLinea />
                </SeccionFormulario>

                <SeccionFormulario titulo="Opciones" icono="mgc_settings_3_line" color="slate">
                    <div className="space-y-3">
                        <label className="flex items-start gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={d.sobrescribir} onChange={(e) => setD((p) => ({ ...p, sobrescribir: e.target.checked }))} className="mt-0.5 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                            <span>
                                Sustituir los turnos que ya estén asignados
                                <span className="block text-xs text-slate-500">Si no, esos días se dejan como están.</span>
                            </span>
                        </label>
                        <label className="flex items-start gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={d.incluir_festivos} onChange={(e) => setD((p) => ({ ...p, incluir_festivos: e.target.checked }))} className="mt-0.5 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                            <span>
                                Asignar también en festivos
                                <span className="block text-xs text-slate-500">Por defecto los festivos se saltan.</span>
                            </span>
                        </label>
                    </div>
                </SeccionFormulario>
            </form>
        </Modal>
    );
}

/** Copiar la semana anterior a la semana visible. */
export function ModalCopiar({ desde, dias, onCerrar, onGuardado }) {
    const { showError } = useNotification();
    const informar = useResultado();
    const [sobrescribir, setSobrescribir] = useState(false);
    const [enviando, setEnviando] = useState(false);

    const origenDesde = (() => {
        const [a, m, d] = desde.split("-").map(Number);
        const r = new Date(a, m - 1, d - dias);
        return `${r.getFullYear()}-${String(r.getMonth() + 1).padStart(2, "0")}-${String(r.getDate()).padStart(2, "0")}`;
    })();
    const origenHasta = (() => {
        const [a, m, d] = desde.split("-").map(Number);
        const r = new Date(a, m - 1, d - 1);
        return `${r.getFullYear()}-${String(r.getMonth() + 1).padStart(2, "0")}-${String(r.getDate()).padStart(2, "0")}`;
    })();

    const copiar = async () => {
        setEnviando(true);
        try {
            const { data } = await api.post("/rrhh/cuadrante/copiar", { desde: origenDesde, hasta: origenHasta, destino: desde, sobrescribir });
            informar(data);
            onGuardado();
        } catch (error) {
            const campos = erroresDeValidacion(error);
            showError(Object.values(campos)[0] ?? mensajeDeError(error, "No se pudo copiar."));
        } finally {
            setEnviando(false);
        }
    };

    return (
        <Modal
            etiqueta="Copiar"
            titulo={dias === 7 ? "Copiar la semana anterior" : `Copiar los ${dias} días anteriores`}
            onCerrar={enviando ? () => {} : onCerrar}
            ancho="max-w-md"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="button" onClick={copiar} className={botonPrimario} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_copy_2_line"></i>}
                        Copiar
                    </button>
                </>
            }
        >
            <div className="space-y-3 text-sm text-slate-600">
                <p>
                    Se copiarán los turnos del <strong>{fechaCorta(origenDesde)}</strong> al <strong>{fechaCorta(origenHasta)}</strong> a los mismos días de las fechas que estás viendo.
                    Se saltan ausencias y días sin alta.
                </p>
                <label className="flex items-center gap-2 text-slate-700">
                    <input type="checkbox" checked={sobrescribir} onChange={(e) => setSobrescribir(e.target.checked)} className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                    Sustituir los turnos que ya haya
                </label>
            </div>
        </Modal>
    );
}
