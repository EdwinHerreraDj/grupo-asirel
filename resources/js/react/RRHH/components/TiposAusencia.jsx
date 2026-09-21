import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Campo, Cargando, Modal, SeccionFormulario, Vacio, claseInput } from "./Comunes";
import { COLORES_AUSENCIA, botonPrimario, botonSecundario, colorAusencia, erroresDeValidacion, mensajeDeError, numeroDias } from "../utils";

function Interruptor({ checked, onChange, etiqueta, ayuda, disabled = false }) {
    return (
        <label className={`flex items-start gap-3 ${disabled ? "cursor-not-allowed opacity-50" : "cursor-pointer"}`}>
            <input
                type="checkbox"
                checked={checked}
                disabled={disabled}
                onChange={(e) => onChange(e.target.checked)}
                className="mt-0.5 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
            />
            <span>
                <span className="block text-sm font-medium text-slate-700">{etiqueta}</span>
                {ayuda && <span className="block text-xs text-slate-500">{ayuda}</span>}
            </span>
        </label>
    );
}

function FormularioTipoAusencia({ tipo, onCerrar, onGuardado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState({
        nombre: tipo?.nombre ?? "",
        color: tipo?.color ?? "cyan",
        es_vacaciones: tipo?.es_vacaciones ?? false,
        es_baja_medica: tipo?.es_baja_medica ?? false,
        retribuida: tipo?.retribuida ?? true,
        requiere_justificante: tipo?.requiere_justificante ?? false,
        dias_anuales: tipo?.dias_anuales ?? "",
        computo: tipo?.computo ?? "naturales",
        activo: tipo?.activo ?? true,
    });
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);
    const poner = (campo, valor) => {
        setDatos((p) => ({ ...p, [campo]: valor }));
        setErrores((p) => ({ ...p, [campo]: null }));
    };

    const guardar = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});
        try {
            const payload = { ...datos, dias_anuales: datos.dias_anuales === "" ? null : Number(datos.dias_anuales) };
            const { data } = tipo
                ? await api.put(`/rrhh/tipos-ausencia/${tipo.id}`, payload)
                : await api.post("/rrhh/tipos-ausencia", payload);
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

    return (
        <Modal
            etiqueta={tipo ? "Editar" : "Nuevo"}
            titulo={tipo ? tipo.nombre : "Nuevo tipo de ausencia"}
            onCerrar={onCerrar}
            ancho="max-w-2xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={guardando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-tipo-ausencia" className={botonPrimario} disabled={guardando}>
                        {guardando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-tipo-ausencia" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Tipo" descripcion="Nombre y color en el calendario" icono="mgc_tag_line" color="cyan" conError={!!(errores.nombre || errores.color)}>
                    <div className="space-y-4">
                        <Campo etiqueta="Nombre" obligatorio error={errores.nombre}>
                            <input
                                value={datos.nombre}
                                onChange={(e) => poner("nombre", e.target.value)}
                                placeholder="Ej: Formación"
                                className={claseInput(errores.nombre)}
                                autoFocus
                            />
                        </Campo>
                        <Campo etiqueta="Color" error={errores.color}>
                            <div className="flex flex-wrap gap-2">
                                {Object.keys(COLORES_AUSENCIA).map((c) => (
                                    <button
                                        key={c}
                                        type="button"
                                        onClick={() => poner("color", c)}
                                        aria-label={c}
                                        className={`h-8 w-8 rounded-full ${colorAusencia(c).celda} ring-offset-2 transition ${
                                            datos.color === c ? "ring-2 ring-slate-900" : "hover:scale-110"
                                        }`}
                                    ></button>
                                ))}
                            </div>
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Comportamiento"
                    descripcion="Cómo se trata esta ausencia"
                    icono="mgc_settings_3_line"
                    color="violet"
                    conError={!!(errores.es_vacaciones || errores.es_baja_medica)}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Interruptor
                            checked={datos.es_vacaciones}
                            onChange={(v) => {
                                poner("es_vacaciones", v);
                                if (v) poner("es_baja_medica", false);
                            }}
                            etiqueta="Son las vacaciones"
                            ayuda="Descuenta del saldo de vacaciones (solo un tipo)."
                        />
                        <Interruptor
                            checked={datos.es_baja_medica}
                            onChange={(v) => {
                                poner("es_baja_medica", v);
                                if (v) poner("es_vacaciones", false);
                            }}
                            etiqueta="Baja médica"
                            ayuda="Puede quedar abierta hasta el alta médica."
                        />
                        <Interruptor checked={datos.retribuida} onChange={(v) => poner("retribuida", v)} etiqueta="Retribuida" ayuda="Se cobra durante la ausencia." />
                        <Interruptor
                            checked={datos.requiere_justificante}
                            onChange={(v) => poner("requiere_justificante", v)}
                            etiqueta="Pide justificante"
                            ayuda="Se recomienda adjuntar un documento."
                        />
                    </div>
                    {(errores.es_vacaciones || errores.es_baja_medica) && (
                        <p className="mt-2 text-xs text-red-600">{errores.es_vacaciones || errores.es_baja_medica}</p>
                    )}
                </SeccionFormulario>

                <SeccionFormulario
                    titulo="Saldo anual"
                    descripcion={datos.es_vacaciones ? "Obligatorio para las vacaciones" : "Opcional: déjalo vacío si no hay límite"}
                    icono="mgc_calendar_line"
                    color="amber"
                    conError={!!(errores.dias_anuales || errores.computo)}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo
                            etiqueta="Días al año"
                            error={errores.dias_anuales}
                            ayuda={datos.es_vacaciones ? "Se prorratea si el empleado no está todo el año de alta." : null}
                        >
                            <input
                                type="number"
                                min={0}
                                max={365}
                                step="0.5"
                                value={datos.dias_anuales}
                                onChange={(e) => poner("dias_anuales", e.target.value)}
                                placeholder="Sin límite"
                                className={claseInput(errores.dias_anuales)}
                            />
                        </Campo>
                        <Campo etiqueta="Se cuentan" error={errores.computo}>
                            <div className="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
                                {[
                                    ["naturales", "Naturales"],
                                    ["laborables", "Laborables"],
                                ].map(([valor, texto]) => (
                                    <button
                                        key={valor}
                                        type="button"
                                        onClick={() => poner("computo", valor)}
                                        className={`rounded-lg px-3 py-1.5 text-sm font-semibold transition ${
                                            datos.computo === valor ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-800"
                                        }`}
                                    >
                                        {texto}
                                    </button>
                                ))}
                            </div>
                            <p className="mt-1 text-xs text-slate-500">Laborables: de lunes a viernes sin festivos.</p>
                        </Campo>
                    </div>
                </SeccionFormulario>

                {tipo && (
                    <SeccionFormulario titulo="Estado" descripcion="Desactivar no borra las ausencias registradas" icono="mgc_forbid_circle_line" color="slate">
                        <Interruptor checked={datos.activo} onChange={(v) => poner("activo", v)} etiqueta="Activo" ayuda="Si lo desactivas no se podrá usar en ausencias nuevas." />
                    </SeccionFormulario>
                )}
            </form>
        </Modal>
    );
}

export default function TiposAusencia() {
    const { showError } = useNotification();
    const [tipos, setTipos] = useState(null);
    const [editando, setEditando] = useState(null);

    const cargar = () =>
        api.get("/rrhh/tipos-ausencia")
            .then(({ data }) => setTipos(data.tipos || []))
            .catch(() => {
                showError("Error al cargar los tipos de ausencia");
                setTipos([]);
            });

    useEffect(() => {
        cargar();
    }, []);

    return (
        <div>
            <div className="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p className="text-sm text-slate-500">Vacaciones, bajas médicas, permisos… con su color en el calendario y su saldo anual.</p>
                <button type="button" onClick={() => setEditando("nuevo")} className={botonPrimario}>
                    <i className="mgc_add_line"></i> Nuevo tipo
                </button>
            </div>

            {tipos === null ? (
                <Cargando />
            ) : tipos.length === 0 ? (
                <Vacio icono="mgc_calendar_line" titulo="Sin tipos de ausencia" />
            ) : (
                <ul className="divide-y divide-slate-100">
                    {tipos.map((t) => (
                        <li key={t.id} className="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div className="flex min-w-0 items-start gap-3">
                                <span className={`mt-1 h-3.5 w-3.5 shrink-0 rounded-full ${colorAusencia(t.color).punto}`}></span>
                                <div className="min-w-0">
                                    <p className={`break-words font-medium ${t.activo ? "text-slate-800" : "text-slate-400 line-through"}`}>{t.nombre}</p>
                                    <div className="mt-1 flex flex-wrap gap-1 text-[11px] font-semibold">
                                        {!t.activo && <span className="rounded-full bg-slate-100 px-2 py-0.5 text-slate-500">Desactivado</span>}
                                        {t.es_vacaciones && <span className="rounded-full bg-cyan-50 px-2 py-0.5 text-cyan-700">Vacaciones</span>}
                                        {t.es_baja_medica && <span className="rounded-full bg-rose-50 px-2 py-0.5 text-rose-700">Baja médica</span>}
                                        {t.dias_anuales !== null && (
                                            <span className="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700">
                                                {numeroDias(t.dias_anuales)} días {t.computo}/año
                                            </span>
                                        )}
                                        {!t.retribuida && <span className="rounded-full bg-slate-100 px-2 py-0.5 text-slate-500">No retribuida</span>}
                                        {t.requiere_justificante && <span className="rounded-full bg-violet-50 px-2 py-0.5 text-violet-700">Justificante</span>}
                                    </div>
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
                <FormularioTipoAusencia
                    tipo={editando === "nuevo" ? null : editando}
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
