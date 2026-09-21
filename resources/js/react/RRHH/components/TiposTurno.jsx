import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Campo, Cargando, Modal, SeccionFormulario, Vacio, claseInput } from "./Comunes";
import { COLORES_AUSENCIA, botonPrimario, botonSecundario, colorAusencia, erroresDeValidacion, horasTurno, mensajeDeError, numeroDias } from "../utils";

const hhmm = (v) => (v ? String(v).slice(0, 5) : "");

function FormularioTurno({ turno, onCerrar, onGuardado }) {
    const { showSuccess, showError } = useNotification();
    const [d, setD] = useState({
        nombre: turno?.nombre ?? "",
        color: turno?.color ?? "cyan",
        hora_inicio: hhmm(turno?.hora_inicio) || "08:00",
        hora_fin: hhmm(turno?.hora_fin) || "16:00",
        hora_inicio_2: hhmm(turno?.hora_inicio_2),
        hora_fin_2: hhmm(turno?.hora_fin_2),
        descanso_minutos: turno?.descanso_minutos ?? 0,
        activo: turno?.activo ?? true,
    });
    const [partida, setPartida] = useState(!!turno?.hora_inicio_2);
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);
    const poner = (c, v) => {
        setD((p) => ({ ...p, [c]: v }));
        setErrores((p) => ({ ...p, [c]: null }));
    };

    const datos = { ...d, hora_inicio_2: partida ? d.hora_inicio_2 : "", hora_fin_2: partida ? d.hora_fin_2 : "" };

    const guardar = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});
        try {
            const payload = { ...datos, hora_inicio_2: datos.hora_inicio_2 || null, hora_fin_2: datos.hora_fin_2 || null, descanso_minutos: Number(d.descanso_minutos) || 0 };
            const { data } = turno ? await api.put(`/rrhh/turnos/${turno.id}`, payload) : await api.post("/rrhh/turnos", payload);
            showSuccess(data.message);
            onGuardado();
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            if (!Object.keys(campos).length) showError(mensajeDeError(error, "No se pudo guardar."));
        } finally {
            setGuardando(false);
        }
    };

    const hora = (campo) => (
        <input type="time" value={d[campo]} onChange={(e) => poner(campo, e.target.value)} className={claseInput(errores[campo])} />
    );

    return (
        <Modal
            etiqueta={turno ? "Editar turno" : "Nuevo turno"}
            titulo={turno ? turno.nombre : "Nuevo turno"}
            onCerrar={onCerrar}
            ancho="max-w-xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={guardando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-turno" className={botonPrimario} disabled={guardando}>
                        {guardando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-turno" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Turno" descripcion="Nombre y color en el cuadrante" icono="mgc_tag_line" color="cyan" conError={!!errores.nombre}>
                    <div className="space-y-4">
                        <Campo etiqueta="Nombre" obligatorio error={errores.nombre}>
                            <input value={d.nombre} onChange={(e) => poner("nombre", e.target.value)} placeholder="Ej.: Jornada intensiva" className={claseInput(errores.nombre)} autoFocus />
                        </Campo>
                        <Campo etiqueta="Color">
                            <div className="flex flex-wrap gap-2">
                                {Object.keys(COLORES_AUSENCIA).map((c) => (
                                    <button
                                        key={c}
                                        type="button"
                                        aria-label={c}
                                        onClick={() => poner("color", c)}
                                        className={`h-8 w-8 rounded-full ${colorAusencia(c).celda} ring-offset-2 transition ${d.color === c ? "ring-2 ring-slate-900" : "hover:scale-110"}`}
                                    ></button>
                                ))}
                            </div>
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Horario"
                    descripcion={`${numeroDias(horasTurno(datos))} horas de trabajo`}
                    icono="mgc_time_line"
                    color="amber"
                    conError={["hora_inicio", "hora_fin", "hora_inicio_2", "hora_fin_2", "descanso_minutos"].some((c) => errores[c])}
                >
                    <div className="grid grid-cols-2 gap-4">
                        <Campo etiqueta="Entrada" obligatorio error={errores.hora_inicio}>{hora("hora_inicio")}</Campo>
                        <Campo etiqueta="Salida" obligatorio error={errores.hora_fin}>{hora("hora_fin")}</Campo>
                        <label className="col-span-2 flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={partida} onChange={(e) => setPartida(e.target.checked)} className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                            Jornada partida (segundo tramo)
                        </label>
                        {partida && (
                            <>
                                <Campo etiqueta="Entrada (tarde)" obligatorio error={errores.hora_inicio_2}>{hora("hora_inicio_2")}</Campo>
                                <Campo etiqueta="Salida (tarde)" obligatorio error={errores.hora_fin_2}>{hora("hora_fin_2")}</Campo>
                            </>
                        )}
                        <Campo etiqueta="Descanso no retribuido (min)" error={errores.descanso_minutos} className="col-span-2 sm:col-span-1">
                            <input type="number" min={0} max={600} value={d.descanso_minutos} onChange={(e) => poner("descanso_minutos", e.target.value)} className={claseInput(errores.descanso_minutos)} />
                        </Campo>
                    </div>
                    <p className="mt-3 text-xs text-slate-500">Si la salida es anterior a la entrada, el turno pasa de medianoche.</p>
                </SeccionFormulario>

                {turno && (
                    <SeccionFormulario titulo="Estado" descripcion="Desactivar no borra el cuadrante" icono="mgc_forbid_circle_line" color="slate">
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={d.activo} onChange={(e) => poner("activo", e.target.checked)} className="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                            Activo (se puede asignar)
                        </label>
                    </SeccionFormulario>
                )}
            </form>
        </Modal>
    );
}

/** Configuración de los tipos de turno. */
export default function TiposTurno() {
    const { showError } = useNotification();
    const [turnos, setTurnos] = useState(null);
    const [editando, setEditando] = useState(null);

    const cargar = () =>
        api.get("/rrhh/turnos")
            .then(({ data }) => setTurnos(data.turnos || []))
            .catch(() => {
                showError("Error al cargar los turnos");
                setTurnos([]);
            });

    useEffect(() => {
        cargar();
    }, []);

    return (
        <div>
            <div className="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p className="text-sm text-slate-500">Horarios que se asignan en el cuadrante. Las horas cuentan para comparar con las de contrato.</p>
                <button type="button" onClick={() => setEditando("nuevo")} className={botonPrimario}>
                    <i className="mgc_add_line"></i> Nuevo turno
                </button>
            </div>

            {turnos === null ? (
                <Cargando />
            ) : turnos.length === 0 ? (
                <Vacio icono="mgc_time_line" titulo="Sin turnos" />
            ) : (
                <ul className="divide-y divide-slate-100">
                    {turnos.map((t) => (
                        <li key={t.id} className="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div className="flex min-w-0 items-center gap-3">
                                <span className={`h-3.5 w-3.5 shrink-0 rounded-full ${colorAusencia(t.color).punto}`}></span>
                                <div className="min-w-0">
                                    <p className={`font-medium ${t.activo ? "text-slate-800" : "text-slate-400 line-through"}`}>{t.nombre}</p>
                                    <p className="text-xs text-slate-500">
                                        {t.horario} · {numeroDias(t.horas)} h{t.descanso_minutos ? ` · ${t.descanso_minutos} min de descanso` : ""}
                                        {!t.activo && " · Desactivado"}
                                    </p>
                                </div>
                            </div>
                            <button type="button" onClick={() => setEditando(t)} className={`${botonSecundario} self-start sm:self-auto`}>
                                <i className="mgc_edit_line"></i> Editar
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            {editando && (
                <FormularioTurno
                    turno={editando === "nuevo" ? null : editando}
                    onCerrar={() => setEditando(null)}
                    onGuardado={() => {
                        setEditando(null);
                        cargar();
                    }}
                />
            )}
        </div>
    );
}
