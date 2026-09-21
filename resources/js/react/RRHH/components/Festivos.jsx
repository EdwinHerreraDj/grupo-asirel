import React, { useEffect, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import ModalConfirmar from "./ModalConfirmar";
import { Aviso, Cargando, Vacio, claseInput } from "./Comunes";
import { MESES, botonPrimario, botonSecundario, erroresDeValidacion, fechaCorta, inputBase, mensajeDeError } from "../utils";

const DIAS = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];

/** Calendario de festivos (cuentan para los días laborables). */
export default function Festivos() {
    const { showSuccess, showError } = useNotification();
    const [anio, setAnio] = useState(new Date().getFullYear());
    const [festivos, setFestivos] = useState(null);
    const [nuevo, setNuevo] = useState({ fecha: "", nombre: "" });
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);
    const [borrar, setBorrar] = useState(null);

    const cargar = async (a = anio) => {
        try {
            const { data } = await api.get("/rrhh/festivos", { params: { anio: a } });
            setFestivos(data.festivos || []);
        } catch {
            showError("Error al cargar los festivos");
            setFestivos([]);
        }
    };

    useEffect(() => {
        setFestivos(null);
        cargar(anio);
    }, [anio]);

    const anadir = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});
        try {
            const { data } = await api.post("/rrhh/festivos", nuevo);
            showSuccess(data.message);
            setNuevo({ fecha: "", nombre: "" });
            const a = Number(nuevo.fecha.slice(0, 4));
            if (a !== anio) setAnio(a);
            else cargar();
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            if (!Object.keys(campos).length) showError(mensajeDeError(error, "No se pudo añadir."));
        } finally {
            setGuardando(false);
        }
    };

    const nacionales = async () => {
        try {
            const { data } = await api.post("/rrhh/festivos/nacionales", { anio });
            showSuccess(data.message);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudieron añadir."));
        }
    };

    const eliminar = async () => {
        try {
            const { data } = await api.delete(`/rrhh/festivos/${borrar.id}`);
            showSuccess(data.message);
            setBorrar(null);
            cargar();
        } catch (error) {
            showError(mensajeDeError(error, "No se pudo eliminar."));
        }
    };

    const porMes = (festivos ?? []).reduce((acc, f) => {
        const m = Number(f.fecha.slice(5, 7));
        (acc[m] ??= []).push(f);
        return acc;
    }, {});

    return (
        <div>
            <div className="space-y-4 border-b border-slate-200 px-4 py-4 sm:px-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-2">
                        <button type="button" onClick={() => setAnio(anio - 1)} className={`${botonSecundario} px-3`} aria-label="Año anterior">
                            <i className="mgc_left_line"></i>
                        </button>
                        <span className="w-16 text-center text-lg font-semibold text-slate-900">{anio}</span>
                        <button type="button" onClick={() => setAnio(anio + 1)} className={`${botonSecundario} px-3`} aria-label="Año siguiente">
                            <i className="mgc_right_line"></i>
                        </button>
                    </div>
                    <button type="button" onClick={nacionales} className={botonSecundario}>
                        <i className="mgc_flag_1_line"></i> Añadir festivos nacionales de {anio}
                    </button>
                </div>

                <form onSubmit={anadir} className="grid grid-cols-1 gap-2 sm:grid-cols-[11rem_1fr_auto]">
                    <div>
                        <input
                            type="date"
                            value={nuevo.fecha}
                            onChange={(e) => setNuevo((p) => ({ ...p, fecha: e.target.value }))}
                            className={claseInput(errores.fecha)}
                            aria-label="Fecha"
                        />
                        {errores.fecha && <p className="mt-1 text-xs text-red-600">{errores.fecha}</p>}
                    </div>
                    <div>
                        <input
                            value={nuevo.nombre}
                            onChange={(e) => setNuevo((p) => ({ ...p, nombre: e.target.value }))}
                            placeholder="Nombre (p. ej. Fiesta local)"
                            className={claseInput(errores.nombre)}
                            aria-label="Nombre"
                        />
                        {errores.nombre && <p className="mt-1 text-xs text-red-600">{errores.nombre}</p>}
                    </div>
                    <button type="submit" className={botonPrimario} disabled={guardando || !nuevo.fecha || !nuevo.nombre.trim()}>
                        <i className="mgc_add_line"></i> Añadir
                    </button>
                </form>

                <Aviso>
                    Los festivos se descuentan al contar días <strong>laborables</strong>. Los nacionales se añaden con un clic; los de la
                    comunidad autónoma y los locales, a mano.
                </Aviso>
            </div>

            {festivos === null ? (
                <Cargando />
            ) : festivos.length === 0 ? (
                <Vacio icono="mgc_flag_1_line" titulo={`Sin festivos en ${anio}`} texto="Empieza añadiendo los nacionales." />
            ) : (
                <div className="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-2 sm:px-6 xl:grid-cols-3">
                    {Object.entries(porMes).map(([m, lista]) => (
                        <div key={m} className="rounded-2xl border border-slate-200">
                            <p className="border-b border-slate-100 bg-slate-50/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {MESES[m - 1]}
                            </p>
                            <ul className="divide-y divide-slate-100">
                                {lista.map((f) => {
                                    const dia = new Date(`${f.fecha.slice(0, 10)}T12:00:00`).getDay();
                                    return (
                                        <li key={f.id} className="flex items-center gap-3 px-4 py-2.5">
                                            <span className="flex h-9 w-9 shrink-0 flex-col items-center justify-center rounded-xl bg-amber-50 text-amber-800">
                                                <span className="text-sm font-bold leading-none">{Number(f.fecha.slice(8, 10))}</span>
                                            </span>
                                            <div className="min-w-0 flex-1">
                                                <p className="break-words text-sm font-medium text-slate-800">{f.nombre}</p>
                                                <p className={`text-xs ${dia === 0 || dia === 6 ? "text-amber-600" : "text-slate-500"}`}>
                                                    {DIAS[dia]}
                                                    {(dia === 0 || dia === 6) && " (fin de semana)"}
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => setBorrar(f)}
                                                title="Eliminar"
                                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50"
                                            >
                                                <i className="mgc_delete_line"></i>
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                        </div>
                    ))}
                </div>
            )}

            {borrar && (
                <ModalConfirmar titulo="Eliminar festivo" textoConfirmar="Eliminar" peligro onConfirmar={eliminar} onCerrar={() => setBorrar(null)}>
                    Se eliminará <strong>{borrar.nombre}</strong> ({fechaCorta(borrar.fecha)}).
                </ModalConfirmar>
            )}
        </div>
    );
}
