import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Campo, Cargando, Modal, SeccionFormulario, Vacio, claseInput } from "./Comunes";
import { botonPrimario, botonSecundario, erroresDeValidacion, mensajeDeError } from "../utils";

function Interruptor({ checked, onChange, etiqueta, ayuda }) {
    return (
        <label className="flex cursor-pointer items-start gap-3">
            <input
                type="checkbox"
                checked={checked}
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

function FormularioTipo({ tipo, onCerrar, onGuardado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState({
        nombre: tipo?.nombre ?? "",
        obligatorio: tipo?.obligatorio ?? true,
        requiere_caducidad: tipo?.requiere_caducidad ?? false,
        dias_aviso: tipo?.dias_aviso ?? 30,
        activo: tipo?.activo ?? true,
    });
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);
    const poner = (campo, valor) => setDatos((p) => ({ ...p, [campo]: valor }));

    const guardar = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});
        try {
            const payload = { ...datos, dias_aviso: Number(datos.dias_aviso) || 0 };
            const { data } = tipo
                ? await api.put(`/rrhh/tipos-documento/${tipo.id}`, payload)
                : await api.post("/rrhh/tipos-documento", payload);
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
            titulo={tipo ? tipo.nombre : "Nuevo tipo de documento"}
            subtitulo={
                tipo
                    ? "Si cambias el nombre, se renombra el apartado en la carpeta de cada empleado."
                    : "Se añadirá como apartado en la carpeta de todos los empleados."
            }
            onCerrar={onCerrar}
            ancho="max-w-lg"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={guardando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-tipo" className={botonPrimario} disabled={guardando}>
                        {guardando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_check_line"></i>}
                        Guardar
                    </button>
                </>
            }
        >
            <form id="form-tipo" onSubmit={guardar} className="space-y-4">
                <SeccionFormulario titulo="Documento" descripcion="Cómo se llama y si es obligatorio" icono="mgc_file_line" color="cyan" conError={!!errores.nombre}>
                    <div className="space-y-4">
                        <Campo etiqueta="Nombre" obligatorio error={errores.nombre} ayuda="Es también el nombre de la subcarpeta en el Drive.">
                            <input
                                value={datos.nombre}
                                onChange={(e) => poner("nombre", e.target.value)}
                                placeholder="Ej: Carnet de carretillero"
                                className={claseInput(errores.nombre)}
                                autoFocus
                            />
                        </Campo>
                        <Interruptor
                            checked={datos.obligatorio}
                            onChange={(v) => poner("obligatorio", v)}
                            etiqueta="Obligatorio"
                            ayuda="Si falta, aparece en «Documentación pendiente»."
                        />
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Caducidad" descripcion="Avisos cuando esté a punto de vencer" icono="mgc_time_line" color="amber" conError={!!errores.dias_aviso}>
                    <div className="space-y-4">
                        <Interruptor
                            checked={datos.requiere_caducidad}
                            onChange={(v) => poner("requiere_caducidad", v)}
                            etiqueta="Este documento caduca"
                            ayuda="Se avisa cuando esté caducado o a punto de caducar."
                        />
                        {datos.requiere_caducidad && (
                            <Campo etiqueta="Avisar con antelación" error={errores.dias_aviso}>
                                <div className="relative sm:w-44">
                                    <input
                                        type="number"
                                        min={0}
                                        max={365}
                                        value={datos.dias_aviso}
                                        onChange={(e) => poner("dias_aviso", e.target.value)}
                                        className={claseInput(errores.dias_aviso, "pr-14")}
                                    />
                                    <span className="pointer-events-none absolute right-8 top-1/2 -translate-y-1/2 text-xs text-slate-400">días</span>
                                </div>
                            </Campo>
                        )}
                    </div>
                </SeccionFormulario>

                {tipo && (
                    <SeccionFormulario titulo="Estado" descripcion="Desactivar no borra nada" icono="mgc_settings_3_line" color="slate">
                        <Interruptor
                            checked={datos.activo}
                            onChange={(v) => poner("activo", v)}
                            etiqueta="Activo"
                            ayuda="Si lo desactivas deja de pedirse, pero sus carpetas y archivos se conservan."
                        />
                    </SeccionFormulario>
                )}
            </form>
        </Modal>
    );
}

export default function TiposDocumento() {
    const { showError } = useNotification();
    const [tipos, setTipos] = useState(null);
    const [editando, setEditando] = useState(null); // null | "nuevo" | tipo

    const cargar = () =>
        api.get("/rrhh/tipos-documento")
            .then(({ data }) => setTipos(data.tipos || []))
            .catch(() => {
                showError("Error al cargar los tipos de documento");
                setTipos([]);
            });

    useEffect(() => {
        cargar();
    }, []);

    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div className="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p className="text-sm text-slate-500">
                    Cada tipo activo es un apartado dentro de la carpeta de cada empleado en el Drive.
                </p>
                <button type="button" onClick={() => setEditando("nuevo")} className={botonPrimario}>
                    <i className="mgc_add_line"></i> Nuevo tipo
                </button>
            </div>

            {tipos === null ? (
                <Cargando />
            ) : tipos.length === 0 ? (
                <Vacio icono="mgc_file_line" titulo="Sin tipos de documento" texto="Crea el primero: DNI, contrato, reconocimiento médico…" />
            ) : (
                <ul className="divide-y divide-slate-100">
                    {tipos.map((t) => (
                        <li key={t.id} className="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div className="min-w-0">
                                <p className={`break-words font-medium ${t.activo ? "text-slate-800" : "text-slate-400 line-through"}`}>{t.nombre}</p>
                                <div className="mt-1 flex flex-wrap gap-1">
                                    {!t.activo && <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">Desactivado</span>}
                                    <span className={`rounded-full px-2 py-0.5 text-[11px] font-semibold ${t.obligatorio ? "bg-cyan-50 text-cyan-700" : "bg-slate-100 text-slate-500"}`}>
                                        {t.obligatorio ? "Obligatorio" : "Opcional"}
                                    </span>
                                    {t.requiere_caducidad && (
                                        <span className="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                                            Caduca · aviso {t.dias_aviso} días antes
                                        </span>
                                    )}
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
                <FormularioTipo
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
